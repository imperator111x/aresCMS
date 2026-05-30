<?php

namespace Plugins\CommentModerationAI\Services;

use App\Models\Setting;

class CommentModerationService
{
    /**
     * @return array{
     *   pending_threshold:int,
     *   reject_threshold:int,
     *   max_links:int,
     *   toxic_words:array<int,string>
     * }
     */
    public function config(): array
    {
        $defaults = [
            'pending_threshold' => 50,
            'reject_threshold' => 80,
            'max_links' => 1,
            'toxic_words' => [
                'idiot', 'trottel', 'arsch', 'fick', 'fuck', 'bitch', 'nazi', 'spast',
            ],
        ];

        $raw = Setting::getValue('comment_moderation_ai_config');
        if (! is_string($raw) || trim($raw) === '') {
            return $defaults;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $defaults;
        }

        $pendingThreshold = (int) ($decoded['pending_threshold'] ?? $defaults['pending_threshold']);
        $rejectThreshold = (int) ($decoded['reject_threshold'] ?? $defaults['reject_threshold']);
        $maxLinks = (int) ($decoded['max_links'] ?? $defaults['max_links']);
        $toxicWords = is_array($decoded['toxic_words'] ?? null)
            ? array_values(array_filter(array_map(
                static fn ($word) => is_string($word) ? trim(mb_strtolower($word)) : '',
                $decoded['toxic_words']
            )))
            : $defaults['toxic_words'];

        $pendingThreshold = max(0, min(100, $pendingThreshold));
        $rejectThreshold = max(0, min(100, $rejectThreshold));
        if ($rejectThreshold < $pendingThreshold) {
            $rejectThreshold = $pendingThreshold;
        }

        return [
            'pending_threshold' => $pendingThreshold,
            'reject_threshold' => $rejectThreshold,
            'max_links' => max(0, min(10, $maxLinks)),
            'toxic_words' => $toxicWords !== [] ? $toxicWords : $defaults['toxic_words'],
        ];
    }

    /**
     * @return array{status:string,score:int,flags:array<int,string>}
     */
    public function evaluate(string $content): array
    {
        $text = trim($content);
        $lower = mb_strtolower($text);
        $config = $this->config();

        $score = 0;
        $flags = [];
        $hasBlockedWord = false;

        foreach ($config['toxic_words'] as $word) {
            if (str_contains($lower, $word)) {
                $score += 35;
                $flags[] = 'toxic-word:'.$word;
                $hasBlockedWord = true;
            }
        }

        preg_match_all('/https?:\/\/|www\./i', $text, $linkMatches);
        $linkCount = is_array($linkMatches[0] ?? null) ? count($linkMatches[0]) : 0;
        if ($linkCount > $config['max_links']) {
            $score += 30;
            $flags[] = 'too-many-links';
        }

        if (preg_match('/(.)\1{7,}/u', $text) === 1) {
            $score += 20;
            $flags[] = 'repeated-characters';
        }

        $lettersOnly = preg_replace('/[^a-zA-Z]/', '', $text);
        $letterLen = is_string($lettersOnly) ? strlen($lettersOnly) : 0;
        if ($letterLen >= 16) {
            $upperCount = preg_match_all('/[A-Z]/', $lettersOnly);
            if (is_int($upperCount) && $upperCount / max($letterLen, 1) > 0.65) {
                $score += 20;
                $flags[] = 'shouting';
            }
        }

        if (mb_strlen($text) < 3) {
            $score += 20;
            $flags[] = 'too-short';
        }

        $score = max(0, min(100, $score));
        $status = 'approved';
        if ($hasBlockedWord) {
            // Blocked words are hard violations and should never be published automatically.
            $status = 'rejected';
            $score = max($score, $config['reject_threshold']);
        } elseif ($score >= $config['reject_threshold']) {
            $status = 'rejected';
        } elseif ($score >= $config['pending_threshold']) {
            $status = 'pending';
        }

        return [
            'status' => $status,
            'score' => $score,
            'flags' => array_values(array_unique($flags)),
        ];
    }
}
