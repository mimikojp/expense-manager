<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClaudeService;
use App\Services\GitHubService;
use App\Services\SensitiveDataFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private $github;
    private $claude;
    private $filter;

    public function __construct(
        GitHubService $github,
        ClaudeService $claude,
        SensitiveDataFilter $filter
    ) {
        $this->github = $github;
        $this->claude = $claude;
        $this->filter = $filter;
    }

    /**
     * Handle GitHub PR webhook
     * POST /api/webhooks/github/pr
     */
    public function handlePullRequest(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();

        if (!$this->github->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid webhook signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->json()->all();
        $action = $data['action'] ?? null;

        // Only process opened and synchronize (updated) PRs
        if (!in_array($action, ['opened', 'synchronize', 'reopened'])) {
            return response()->json(['message' => 'Event ignored'], 200);
        }

        $pullRequest = $data['pull_request'] ?? null;
        $repository = $data['repository'] ?? null;

        if (!$pullRequest || !$repository) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Extract PR information
        $prNumber = $pullRequest['number'];
        $repoFullName = $repository['full_name'];
        list($owner, $repo) = explode('/', $repoFullName);

        Log::info('Processing PR webhook', [
            'repo' => $repoFullName,
            'pr' => $prNumber,
            'action' => $action,
        ]);

        // Process PR review asynchronously (in production, use queue)
        try {
            $this->processPullRequestReview($owner, $repo, $prNumber, $pullRequest);
        } catch (\Exception $e) {
            Log::error('Error processing PR review', [
                'error' => $e->getMessage(),
                'pr' => $prNumber,
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }

        return response()->json(['message' => 'Webhook received'], 200);
    }

    /**
     * Process pull request review
     */
    private function processPullRequestReview(string $owner, string $repo, int $prNumber, array $prData)
    {
        Log::info('Starting PR review', [
            'repo' => "{$owner}/{$repo}",
            'pr' => $prNumber,
            'title' => $prData['title'],
        ]);

        // Fetch PR diff and files
        $diff = $this->github->getPullRequestDiff($owner, $repo, $prNumber);
        $files = $this->github->getPullRequestFiles($owner, $repo, $prNumber);

        if (!$diff || !$files) {
            Log::error('Failed to fetch PR data from GitHub', [
                'repo' => "{$owner}/{$repo}",
                'pr' => $prNumber,
            ]);
            return;
        }

        // Filter sensitive files
        $filteredFiles = $this->filter->filterFileList($files);

        // Filter sensitive data from diff
        $filteredDiff = $this->filter->filterDiff($diff);
        $filterSummary = $this->filter->getFilterSummary($diff, $filteredDiff);

        Log::info('Filtered PR data', [
            'repo' => "{$owner}/{$repo}",
            'pr' => $prNumber,
            'total_files' => count($files),
            'reviewed_files' => count($filteredFiles),
            'masked_lines' => $filterSummary['masked_lines'],
        ]);

        // Get Claude AI review
        $reviewData = [
            'title' => $prData['title'],
            'description' => $prData['body'] ?? '',
            'author' => $prData['user']['login'] ?? 'unknown',
            'base' => $prData['base']['ref'] ?? '',
            'head' => $prData['head']['ref'] ?? '',
            'changed_files' => count($filteredFiles),
        ];

        $claudeResult = $this->claude->reviewPullRequest($reviewData, $filteredDiff, $filteredFiles);

        if (!$claudeResult) {
            Log::error('Failed to get review from Claude AI', [
                'repo' => "{$owner}/{$repo}",
                'pr' => $prNumber,
            ]);
            return;
        }

        // Format review for GitHub
        $formattedReview = $this->claude->formatReviewForGitHub(
            $claudeResult['review'],
            $filterSummary
        );

        // Post review to GitHub
        $posted = $this->github->postIssueComment($owner, $repo, $prNumber, $formattedReview);

        if ($posted) {
            Log::info('PR review completed successfully', [
                'repo' => "{$owner}/{$repo}",
                'pr' => $prNumber,
            ]);
        } else {
            Log::error('Failed to post review to GitHub', [
                'repo' => "{$owner}/{$repo}",
                'pr' => $prNumber,
            ]);
        }
    }
}
