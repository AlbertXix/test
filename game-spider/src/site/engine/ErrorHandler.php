<?php

class ErrorHandler
{
    private static $logger;

    public static function init(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) return false;
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

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
