<?php

namespace App\Security;

class Sanitizer
{
    /**
     * Sanitize a string against XSS (Cross-Site Scripting)
     * Strips dangerous HTML/JS tags, removes null bytes, and normalizes characters.
     */
    public static function cleanString(mixed $input, int $maxLength = 1000): string
    {
        if ($input === null || !is_scalar($input)) {
            return '';
        }

        $input = (string) $input;

        // 1. Remove null bytes
        $clean = str_replace(chr(0), '', $input);

        // 2. Strip HTML and PHP tags to prevent injected script tags (<script>, <img onerror>, etc.)
        $clean = strip_tags($clean);

        // 3. Convert special characters to HTML entities (defense-in-depth for web contexts)
        $clean = htmlspecialchars($clean, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 4. Trim whitespace
        $clean = trim($clean);

        // 5. Enforce max length
        if (mb_strlen($clean) > $maxLength) {
            $clean = mb_substr($clean, 0, $maxLength);
        }

        return $clean;
    }

    /**
     * Sanitize and validate an email address
     */
    public static function cleanEmail(mixed $email): string
    {
        if ($email === null || !is_scalar($email)) {
            return '';
        }

        $clean = trim(filter_var((string) $email, FILTER_SANITIZE_EMAIL) ?: '');
        return strtolower($clean);
    }

    /**
     * Sanitize integer input safely (handles overflow strings and negatives)
     */
    public static function cleanInt(mixed $val, int $default = 0): int
    {
        if (is_numeric($val)) {
            // Guard against astronomical overflow
            if (strlen((string) $val) > 10) {
                return $default;
            }
            return (int) $val;
        }
        return $default;
    }

    /**
     * Sanitize float input
     */
    public static function cleanFloat(mixed $val, float $default = 0.0): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }
        return $default;
    }

    /**
     * Recursively sanitize nested arrays (e.g. JSON request payloads)
     */
    public static function cleanArray(array $data): array
    {
        $result = [];
        foreach ($data as $key => $val) {
            $cleanKey = self::cleanString((string) $key, 100);
            if (is_array($val)) {
                $result[$cleanKey] = self::cleanArray($val);
            } elseif (is_string($val)) {
                $result[$cleanKey] = self::cleanString($val);
            } else {
                $result[$cleanKey] = $val;
            }
        }
        return $result;
    }
}
