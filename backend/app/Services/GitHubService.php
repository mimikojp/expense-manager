<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    private $token;
    private $apiBase = 'https://api.github.com';

    public function __construct()
    {
        $this->token = env('GITHUB_TOKEN');
    }

    /**
     * Verify GitHub webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = env('GITHUB_WEBHOOK_SECRET');

        if (!$secret) {
            Log::warning('GitHub webhook secret not configured');
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get pull request details
     */
    public function getPullRequest(string $owner, string $repo, int $prNumber): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github.v3+json',
            ])->get("{$this->apiBase}/repos/{$owner}/{$repo}/pulls/{$prNumber}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch PR details', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching PR details', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get pull request diff
     */
    public function getPullRequestDiff(string $owner, string $repo, int $prNumber): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github.v3.diff',
            ])->get("{$this->apiBase}/repos/{$owner}/{$repo}/pulls/{$prNumber}");

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('Failed to fetch PR diff', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching PR diff', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get pull request files
     */
    public function getPullRequestFiles(string $owner, string $repo, int $prNumber): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github.v3+json',
            ])->get("{$this->apiBase}/repos/{$owner}/{$repo}/pulls/{$prNumber}/files");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch PR files', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching PR files', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Post review comment on PR
     */
    public function postReviewComment(
        string $owner,
        string $repo,
        int $prNumber,
        string $body,
        string $event = 'COMMENT' // APPROVE, REQUEST_CHANGES, COMMENT
    ): bool {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github.v3+json',
            ])->post("{$this->apiBase}/repos/{$owner}/{$repo}/pulls/{$prNumber}/reviews", [
                'body' => $body,
                'event' => $event,
            ]);

            if ($response->successful()) {
                Log::info('Review comment posted successfully', [
                    'pr' => $prNumber,
                    'repo' => "{$owner}/{$repo}",
                ]);
                return true;
            }

            Log::error('Failed to post review comment', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception posting review comment', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Post issue comment on PR
     */
    public function postIssueComment(string $owner, string $repo, int $prNumber, string $body): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github.v3+json',
            ])->post("{$this->apiBase}/repos/{$owner}/{$repo}/issues/{$prNumber}/comments", [
                'body' => $body,
            ]);

            if ($response->successful()) {
                Log::info('Issue comment posted successfully', [
                    'pr' => $prNumber,
                    'repo' => "{$owner}/{$repo}",
                ]);
                return true;
            }

            Log::error('Failed to post issue comment', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception posting issue comment', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
