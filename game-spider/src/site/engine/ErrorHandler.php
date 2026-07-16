<?php

/**
 * 错误/异常处理器 — 注册 set_error_handler / set_exception_handler，
 * 将 PHP 错误转为 ErrorException，未捕获异常统一记录日志并返回 500
 */
class ErrorHandler
{
    /** @var Logger|null 日志记录器实例 */
    private static $logger;

    /** 注册错误和异常处理函数 */
    public static function init(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    /** 设置日志记录器 */
    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * 错误处理回调：将 PHP 错误转换为 ErrorException 抛出
     * @return bool false=交由标准错误处理
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) return false;
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /** 异常处理回调：记录日志（含堆栈），输出 500 页面 */
    public static function handleException(\Throwable $e): void
    {
        if (self::$logger) {
            $msg = sprintf(
                'Uncaught %s: %s in %s:%d',
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            self::$logger->error($msg);
            self::$logger->debug($msg . "\n" . $e->getTraceAsString());
        }

        http_response_code(500);
        if (!ini_get('display_errors')) {
            echo '<h1>500 Internal Server Error</h1>';
        }
        exit;
    }
}
