<?php

/**
 * SQL 注入检测器 — 通过正则特征匹配识别常见的 SQL 注入载荷
 */
class SqlInjectDetector
{
    /** @var array 注入特征正则列表 */
    private static $patterns = [
        '/\bOR\b\s+\d+\s*=\s*\d/i',           /* OR 1=1 */
        '/\bOR\b\s+\'[^\']*\'/i',              /* OR '1'='1' */
        '/\bAND\b\s+\d+\s*=\s*\d/i',           /* AND 1=1 */
        '/\bUNION\b.*\bSELECT\b/i',             /* UNION SELECT */
        '/\bSELECT\b.*\bINTO\s+(OUTFILE|DUMPFILE)\b/i',  /* INTO OUTFILE */
        '/\bLOAD_FILE\s*\(/i',                  /* LOAD_FILE() */
        '/\bINFORMATION_SCHEMA\b/i',            /* INFORMATION_SCHEMA */
        '/\bSLEEP\s*\(/i',                      /* SLEEP() 延时注入 */
        '/\bWAITFOR\b\s+DELAY/i',               /* WAITFOR DELAY */
        '/\bBENCHMARK\s*\(/i',                  /* BENCHMARK() */
        '/;\s*\b(DROP|DELETE|UPDATE|INSERT|ALTER|CREATE|TRUNCATE|EXEC)\b/i',  /* 堆叠查询 */
        '/\bEXEC\b/i',                           /* EXEC */
        '/--[\s\r\n]/',                          /* SQL 注释 -- */
        '/#[\s\r\n]/',                           /* SQL 注释 # */
        '/\/\*[\s\S]*?\*\//',                    /* 块注释 */
        '/\'(?:--|#|\/\*)/',                     /* 引号后跟注释 */
        '/"(?:--|#|\/\*)/',                      /* 双引号后跟注释 */
    ];

    /** 检测单个输入是否包含 SQL 注入特征 */
    public static function hasInjection($input): bool
    {
        if (!is_string($input)) return false;
        foreach (self::$patterns as $pattern) {
            if (preg_match($pattern, $input)) return true;
        }
        return false;
    }

    /** 批量检测数组中的所有输入值 */
    public static function hasInjectionInArray(array $inputs): bool
    {
        foreach ($inputs as $value) {
            if (self::hasInjection($value)) return true;
        }
        return false;
    }
}
