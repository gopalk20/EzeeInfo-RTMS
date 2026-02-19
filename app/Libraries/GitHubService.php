<?php

namespace App\Libraries;

/**
 * GitHub API integration for RTMS.
 * Fetches issues, PRs, branches. Requires GITHUB_PAT in .env for authenticated requests.
 */
class GitHubService
{
    protected ?string $token = null;
    protected string $apiBase = 'https://api.github.com';

    public function __construct()
    {
        $this->token = env('GITHUB_PAT', '');
    }

    /**
     * Parse owner/repo from GitHub URL.
     */
    public function parseRepoUrl(string $url): ?array
    {
        if (preg_match('#github\.com[:/]([^/]+)/([^/\s#.]+)#', $url, $m)) {
            return ['owner' => $m[1], 'repo' => preg_replace('/\.git$/', '', $m[2])];
        }
        return null;
    }

    /**
     * Fetch open issues for a repository.
     */
    public function fetchIssues(string $owner, string $repo): array
    {
        $url = "{$this->apiBase}/repos/{$owner}/{$repo}/issues?state=open&per_page=100";
        $response = $this->request($url);
        if (!is_array($response)) {
            return [];
        }
        return $response;
    }

    /**
     * Fetch branches for a repository.
     */
    public function fetchBranches(string $owner, string $repo): array
    {
        $url = "{$this->apiBase}/repos/{$owner}/{$repo}/branches?per_page=100";
        $response = $this->request($url);
        if (!is_array($response)) {
            return [];
        }
        return array_map(fn ($b) => $b['name'] ?? '', $response);
    }

    /**
     * Make authenticated GET request to GitHub API.
     */
    protected function request(string $url): array|false
    {
        $ch = curl_init($url);
        if (!$ch) {
            return false;
        }
        $headers = ['Accept: application/vnd.github.v3+json'];
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || $body === false) {
            log_message('error', "GitHub API error: {$url} HTTP {$code}");
            return false;
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : false;
    }
}
