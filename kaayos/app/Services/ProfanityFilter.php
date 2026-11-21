<?php

namespace App\Services;

class ProfanityFilter
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('profanity', []);
    }

    /**
     * Check if text contains profanity.
     */
    public function containsProfanity(string $text): bool
    {
        return $this->filter($text) !== $text;
    }

    /**
     * Filter profanity from text, masking or blocking based on config.
     *
     * @return string Filtered text with profanity masked.
     */
    public function filter(string $text): string
    {
        if (empty($text)) {
            return $text;
        }

        $normalized = $this->normalize($text);
        $words = $this->getAllWords();

        $filtered = $text;
        $foundAny = false;

        foreach ($words as $word) {
            if (strlen($word) < ($this->config['min_length'] ?? 3)) {
                continue;
            }

            // Check normalized text for the word
            if (stripos($normalized, $word) !== false) {
                $foundAny = true;
                $masked = str_repeat($this->config['mask_char'] ?? '*', strlen($word));
                // Replace in original text (case-insensitive)
                $filtered = preg_replace('/' . preg_quote($word, '/') . '/iu', $masked, $filtered);
            }
        }

        return $foundAny ? $filtered : $text;
    }

    /**
     * Get flattened list of all profanity words (EN + TL).
     */
    protected function getAllWords(): array
    {
        $words = [];
        foreach ($this->config['words'] ?? [] as $lang => $list) {
            foreach ($list as $word) {
                $words[] = strtolower(trim($word));
            }
        }
        return array_unique($words);
    }

    /**
     * Normalize text by applying obfuscation pattern replacements.
     * This converts l33t speak back to plain letters for matching.
     */
    protected function normalize(string $text): string
    {
        $normalized = $text;
        foreach ($this->config['obfuscation_patterns'] ?? [] as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized);
        }
        // Collapse repeated characters (e.g., "foooool" → "fool")
        $normalized = preg_replace('/(.)\1{2,}/', '$1$1', $normalized);
        // Remove non-alphanumeric characters for matching
        $normalized = preg_replace('/[^a-z0-9]/i', '', $normalized);
        return strtolower($normalized);
    }

    /**
     * Check if text contains profanity (alias for containsProfanity).
     */
    public function hasProfanity(string $text): bool
    {
        return $this->containsProfanity($text);
    }
}
