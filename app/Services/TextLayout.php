<?php

declare(strict_types=1);

namespace App\Services;

final class TextLayout
{
    public function fit(string $text, callable $measure, int $boxW, int $boxH, int $baseSize, int $minSize, int $maxLines, float $lineHeight): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['lines' => [], 'fontSize' => $baseSize];
        }

        $fontSize = $baseSize;
        while ($fontSize >= $minSize) {
            $lines = $this->wrap($text, $measure, $fontSize, $boxW);
            if (count($lines) > $maxLines) {
                $fontSize -= 2;
                continue;
            }
            $lineHeightPx = $fontSize * $lineHeight;
            if ((count($lines) * $lineHeightPx) <= $boxH) {
                return ['lines' => $lines, 'fontSize' => $fontSize];
            }
            $fontSize -= 2;
        }
        $lines = $this->wrap($text, $measure, $minSize, $boxW);
        $lines = $this->truncate($lines, $measure, $minSize, $maxLines, $boxW);
        return ['lines' => $lines, 'fontSize' => $minSize];
    }

    private function wrap(string $text, callable $measure, int $fontSize, int $maxWidth): array
    {
        $words = preg_split('/\s+/', $text);
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $test = $current === '' ? $word : $current . ' ' . $word;
            $width = $measure($test, $fontSize);
            if ($width <= $maxWidth) {
                $current = $test;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                }
                $current = $word;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        return $lines;
    }

    private function truncate(array $lines, callable $measure, int $fontSize, int $maxLines, int $maxWidth): array
    {
        if (count($lines) <= $maxLines) {
            return $lines;
        }
        $lines = array_slice($lines, 0, $maxLines);
        $last = array_pop($lines);
        $ellipsis = '…';
        while ($last !== '') {
            $width = $measure($last . $ellipsis, $fontSize);
            if ($width <= $maxWidth) {
                $lines[] = $last . $ellipsis;
                return $lines;
            }
            $last = mb_substr($last, 0, -1);
        }
        $lines[] = $ellipsis;
        return $lines;
    }
}
