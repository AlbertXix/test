<?php

class SqlInjectDetector
{
    private static $patterns = [
        '/\bOR\b\s+\d+\s*=\s*\d/i',
        '/\bOR\b\s+\'[^\']*\'/i',
        '/\bAND\b\s+\d+\s*=\s*\d/i',
        '/\bUNION\b.*\bSELECT\b/i',
        '/\bSELECT\b.*\bINTO\s+(OUTFILE|DUMPFILE)\b/i',
        '/\bLOAD_FILE\s*\(/i',
        '/\bINFORMATION_SCHEMA\b/i',
        '/\bSLEEP\s*\(/i',
        '/\bWAITFOR\b\s+DELAY/i',
        '/\bBENCHMARK\s*\(/i',
        '/;\s*\b(DROP|DELETE|UPDATE|INSERT|ALTER|CREATE|TRUNCATE|EXEC)\b/i',
        '/\bEXEC\b/i',
        '/--[\s\r\n]/',
        '/#[\s\r\n]/',
        '/\/\*[\s\S]*?\*\//',
        '/\'(?:--|#|\/\*)/',
        '/"(?:--|#|\/\*)/',
    ];

    public static function hasInjection($input): bool
    {
        if (!is_string($input)) return false;
        foreach (self::$patterns as $pattern) {
            if (preg_match($pattern, $input)) return true;
        }
        return false;
    }

    public static function hasInjectionInArray(array $inputs): bool
    {
        foreach ($inputs as $value) {
            if (self::hasInjection($value)) return true;
        }
        return false;
    }
}
