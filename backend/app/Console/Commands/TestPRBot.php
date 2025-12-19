<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GitHubService;
use App\Services\ClaudeService;
use App\Services\SensitiveDataFilter;

class TestPRBot extends Command
{
    protected $signature = 'prbot:test {--pr= : PR number to test} {--owner= : Repository owner} {--repo= : Repository name}';
    protected $description = 'Test PR Bot functionality and configuration';

    private $github;
    private $claude;
    private $filter;

    public function __construct(GitHubService $github, ClaudeService $claude, SensitiveDataFilter $filter)
    {
        parent::__construct();
        $this->github = $github;
        $this->claude = $claude;
        $this->filter = $filter;
    }

    public function handle()
    {
        $this->info('🤖 PR Bot Diagnostic Test');
        $this->line('');

        // Test 1: Check environment variables
        $this->info('1️⃣ Checking environment configuration...');
        $envCheck = $this->checkEnvironment();

        if (!$envCheck['success']) {
            $this->error('❌ Environment check failed!');
            return 1;
        }

        $this->info('✅ Environment configured correctly');
        $this->line('');

        // Test 2: Test GitHub API connection
        $this->info('2️⃣ Testing GitHub API connection...');
        $githubCheck = $this->testGitHubConnection();

        if (!$githubCheck['success']) {
            $this->error('❌ GitHub API connection failed!');
            $this->error('   Error: ' . $githubCheck['error']);
            return 1;
        }

        $this->info('✅ GitHub API connection successful');
        $this->info('   User: ' . ($githubCheck['user'] ?? 'unknown'));
        $this->line('');

        // Test 3: Test Claude AI connection
        $this->info('3️⃣ Testing Claude AI connection...');
        $claudeCheck = $this->testClaudeConnection();

        if (!$claudeCheck['success']) {
            $this->error('❌ Claude AI connection failed!');
            $this->error('   Error: ' . $claudeCheck['error']);
            return 1;
        }

        $this->info('✅ Claude AI connection successful');
        $this->line('');

        // Test 4: Test webhook signature verification
        $this->info('4️⃣ Testing webhook signature verification...');
        $webhookCheck = $this->testWebhookSignature();

        if (!$webhookCheck['success']) {
            $this->error('❌ Webhook signature verification failed!');
            return 1;
        }

        $this->info('✅ Webhook signature verification working');
        $this->line('');

        // Test 5: Test full PR review (optional)
        if ($this->option('pr') && $this->option('owner') && $this->option('repo')) {
            $this->info('5️⃣ Testing full PR review...');
            $prCheck = $this->testPRReview(
                $this->option('owner'),
                $this->option('repo'),
                (int)$this->option('pr')
            );

            if (!$prCheck['success']) {
                $this->error('❌ PR review test failed!');
                $this->error('   Error: ' . $prCheck['error']);
                return 1;
            }

            $this->info('✅ PR review test successful');
            $this->line('');
        }

        $this->info('🎉 All tests passed! PR Bot is configured correctly.');
        $this->line('');
        $this->info('Webhook URL: ' . env('APP_URL') . '/api/webhooks/github/pr');
        $this->info('Configure this webhook in your GitHub repository settings.');

        return 0;
    }

    private function checkEnvironment(): array
    {
        $required = [
            'GITHUB_TOKEN' => env('GITHUB_TOKEN'),
            'GITHUB_WEBHOOK_SECRET' => env('GITHUB_WEBHOOK_SECRET'),
            'ANTHROPIC_API_KEY' => env('ANTHROPIC_API_KEY'),
        ];

        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
                $this->error("   ❌ {$key} is not set");
            } else {
                $this->line("   ✅ {$key} is configured");
            }
        }

        return [
            'success' => empty($missing),
            'missing' => $missing,
        ];
    }

    private function testGitHubConnection(): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GITHUB_TOKEN'),
                'Accept' => 'application/vnd.github.v3+json',
            ])->get('https://api.github.com/user');

            if ($response->successful()) {
                $user = $response->json();
                return [
                    'success' => true,
                    'user' => $user['login'] ?? 'unknown',
                ];
            }

            return [
                'success' => false,
                'error' => 'Status: ' . $response->status() . ' - ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function testClaudeConnection(): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => env('ANTHROPIC_API_KEY'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 100,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Say "Hello" if you can receive this message.',
                    ],
                ],
            ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => 'Status: ' . $response->status() . ' - ' . $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function testWebhookSignature(): array
    {
        $payload = '{"test": "data"}';
        $secret = env('GITHUB_WEBHOOK_SECRET');
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        $result = $this->github->verifyWebhookSignature($payload, $signature);

        return ['success' => $result];
    }

    private function testPRReview(string $owner, string $repo, int $prNumber): array
    {
        try {
            $this->line("   Fetching PR #{$prNumber} from {$owner}/{$repo}...");

            $pr = $this->github->getPullRequest($owner, $repo, $prNumber);
            if (!$pr) {
                return ['success' => false, 'error' => 'Failed to fetch PR details'];
            }

            $this->line("   Fetching PR diff...");
            $diff = $this->github->getPullRequestDiff($owner, $repo, $prNumber);
            if (!$diff) {
                return ['success' => false, 'error' => 'Failed to fetch PR diff'];
            }

            $this->line("   Fetching PR files...");
            $files = $this->github->getPullRequestFiles($owner, $repo, $prNumber);
            if (!$files) {
                return ['success' => false, 'error' => 'Failed to fetch PR files'];
            }

            $this->line("   Filtering sensitive data...");
            $filteredFiles = $this->filter->filterFileList($files);
            $filteredDiff = $this->filter->filterDiff($diff);

            $this->line("   Sending to Claude AI for review...");
            $reviewData = [
                'title' => $pr['title'],
                'description' => $pr['body'] ?? '',
                'author' => $pr['user']['login'] ?? 'unknown',
                'base' => $pr['base']['ref'] ?? '',
                'head' => $pr['head']['ref'] ?? '',
                'changed_files' => count($filteredFiles),
            ];

            $review = $this->claude->reviewPullRequest($reviewData, $filteredDiff, $filteredFiles);
            if (!$review) {
                return ['success' => false, 'error' => 'Failed to get Claude AI review'];
            }

            $this->line('');
            $this->info('   📝 Review preview (first 500 chars):');
            $this->line('   ' . substr($review['review'], 0, 500) . '...');

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
