<?php

namespace App\Services;

class SensitiveDataFilter
{
    private $sensitivePatterns = [
        // API Keys and Tokens
        '/api[_-]?key["\']?\s*[:=]\s*["\']?([a-zA-Z0-9_\-]+)["\']?/i',
        '/token["\']?\s*[:=]\s*["\']?([a-zA-Z0-9_\-]+)["\']?/i',
        '/secret["\']?\s*[:=]\s*["\']?([a-zA-Z0-9_\-]+)["\']?/i',
        '/password["\']?\s*[:=]\s*["\']?([^\s"\']+)["\']?/i',

        // AWS Keys
        '/AKIA[0-9A-Z]{16}/',
        '/aws[_-]?secret[_-]?access[_-]?key["\']?\s*[:=]\s*["\']?([^\s"\']+)["\']?/i',

        // Private Keys
        '/-----BEGIN\s+(RSA\s+)?PRIVATE KEY-----/',
        '/-----BEGIN\s+OPENSSH\s+PRIVATE KEY-----/',

        // Database credentials
        '/DB_PASSWORD\s*=\s*[^\s]+/',
        '/MYSQL_PASSWORD\s*=\s*[^\s]+/',

        // Email addresses (optionally filter)
        // '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',

        // IP addresses (optionally filter internal IPs)
        '/\b(?:10|172\.(?:1[6-9]|2[0-9]|3[01])|192\.168)\.\d{1,3}\.\d{1,3}\b/',

        // JWT tokens
        '/eyJ[a-zA-Z0-9_-]*\.eyJ[a-zA-Z0-9_-]*\.[a-zA-Z0-9_-]*/i',
    ];

    private $sensitiveFiles = [
        '.env',
        '.env.local',
        '.env.production',
        'credentials.json',
        'secrets.yaml',
        'secrets.yml',
        'id_rsa',
        'id_dsa',
        '*.pem',
        '*.key',
        '*.p12',
        '*.pfx',
    ];

    /**
     * Filter sensitive data from diff content
     */
    public function filterDiff(string $diff): string
    {
        $lines = explode("\n", $diff);
        $filteredLines = [];

        foreach ($lines as $line) {
            // Check if line contains sensitive data
            if ($this->containsSensitiveData($line)) {
                // Replace sensitive content with placeholder
                $filteredLine = $this->maskSensitiveData($line);
                $filteredLines[] = $filteredLine . ' // [SENSITIVE DATA MASKED]';
            } else {
                $filteredLines[] = $line;
            }
        }

        return implode("\n", $filteredLines);
    }

    /**
     * Check if a file should be excluded from review
     */
    public function shouldExcludeFile(string $filename): bool
    {
        foreach ($this->sensitiveFiles as $pattern) {
            if (fnmatch($pattern, basename($filename))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter file list to remove sensitive files
     */
    public function filterFileList(array $files): array
    {
        return array_filter($files, function ($file) {
            return !$this->shouldExcludeFile($file['filename'] ?? $file);
        });
    }

    /**
     * Check if line contains sensitive data
     */
    private function containsSensitiveData(string $line): bool
    {
        foreach ($this->sensitivePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask sensitive data in a line
     */
    private function maskSensitiveData(string $line): string
    {
        foreach ($this->sensitivePatterns as $pattern) {
            $line = preg_replace($pattern, '***REDACTED***', $line);
        }

        return $line;
    }

    /**
     * Get summary of filtered content
     */
    public function getFilterSummary(string $originalDiff, string $filteredDiff): array
    {
        $originalLines = count(explode("\n", $originalDiff));
        $filteredLines = count(explode("\n", $filteredDiff));
        $maskedLines = substr_count($filteredDiff, '[SENSITIVE DATA MASKED]');

        return [
            'total_lines' => $originalLines,
            'reviewed_lines' => $filteredLines,
            'masked_lines' => $maskedLines,
            'exclusion_percentage' => $originalLines > 0
                ? round(($maskedLines / $originalLines) * 100, 2)
                : 0,
        ];
    }
}
