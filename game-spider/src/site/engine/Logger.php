<?php

class Logger
{
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARN = 'WARN';
    const ERROR = 'ERROR';

    private static $levelMap = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARN => 2,
        self::ERROR => 3,
    ];

    private $logDir;
    private $minLevel;

    public function __construct(string $logDir, string $minLevel = self::INFO)
    {
        $this->logDir = rtrim($logDir, '/\\');
        $this->minLevel = $minLevel;
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function debug(string $msg): void { $this->log(self::DEBUG, $msg); }
    public function info(string $msg): void { $this->log(self::INFO, $msg); }
    public function warn(string $msg): void { $this->log(self::WARN, $msg); }
    public function error(string $msg): void { $this->log(self::ERROR, $msg); }

    private function log(string $level, string $msg): void
    {
        if ((self::$levelMap[$level] ?? 0) < (self::$levelMap[$this->minLevel] ?? 0)) return;
        $line = sprintf("[%s] [%s] %s%s", date('Y-m-d H:i:s'), $level, $msg, PHP_EOL);
        file_put_contents($this->logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
