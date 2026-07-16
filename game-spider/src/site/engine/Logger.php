<?php

/**
 * 简易文件日志记录器 — 支持 DEBUG/INFO/WARN/ERROR 四级，
 * 按级别过滤，追加写入 app.log
 */
class Logger
{
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARN = 'WARN';
    const ERROR = 'ERROR';

    /** @var array 级别 → 数值映射（数值越低越详细） */
    private static $levelMap = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARN => 2,
        self::ERROR => 3,
    ];

    /** @var string 日志文件存放目录 */
    private $logDir;
    /** @var string 当前最低记录级别 */
    private $minLevel;

    /**
     * @param string $logDir 日志目录路径
     * @param string $minLevel 最低记录级别（低于此级别的不写入）
     */
    public function __construct(string $logDir, string $minLevel = self::INFO)
    {
        $this->logDir = rtrim($logDir, '/\\');
        $this->minLevel = $minLevel;
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /** 记录 DEBUG 级别日志 */
    public function debug(string $msg): void { $this->log(self::DEBUG, $msg); }
    /** 记录 INFO 级别日志 */
    public function info(string $msg): void { $this->log(self::INFO, $msg); }
    /** 记录 WARN 级别日志 */
    public function warn(string $msg): void { $this->log(self::WARN, $msg); }
    /** 记录 ERROR 级别日志 */
    public function error(string $msg): void { $this->log(self::ERROR, $msg); }

    /** 核心写入方法：检查级别，格式化后追加到日志文件 */
    private function log(string $level, string $msg): void
    {
        if ((self::$levelMap[$level] ?? 0) < (self::$levelMap[$this->minLevel] ?? 0)) return;
        $line = sprintf("[%s] [%s] %s%s", date('Y-m-d H:i:s'), $level, $msg, PHP_EOL);
        file_put_contents($this->logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
