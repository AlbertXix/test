<?php

namespace GameSpider\Util;

class SnowflakeIdGenerator
{
    private const EPOCH = 1577836800000;

    private const WORKER_BITS = 10;
    private const SEQUENCE_BITS = 12;

    private const MAX_WORKER = 1023;
    private const MAX_SEQUENCE = 4095;

    private const WORKER_SHIFT = 12;
    private const TIMESTAMP_SHIFT = 22;

    private int $workerId;
    private int $lastTimestamp = -1;
    private int $sequence = 0;

    public function __construct(int $workerId = 0)
    {
        if ($workerId < 0 || $workerId > self::MAX_WORKER) {
            throw new \InvalidArgumentException("Worker ID must be between 0 and " . self::MAX_WORKER);
        }
        $this->workerId = $workerId;
    }

    public function generate(): int
    {
        $timestamp = $this->milliTimestamp();

        if ($timestamp < $this->lastTimestamp) {
            throw new \RuntimeException('Clock moved backwards');
        }

        if ($timestamp === $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) & self::MAX_SEQUENCE;
            if ($this->sequence === 0) {
                $timestamp = $this->waitNextMillis($timestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        return ($timestamp << self::TIMESTAMP_SHIFT)
            | ($this->workerId << self::WORKER_SHIFT)
            | $this->sequence;
    }

    private function milliTimestamp(): int
    {
        return (int) (floor(microtime(true) * 1000)) - self::EPOCH;
    }

    private function waitNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->milliTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->milliTimestamp();
        }
        return $timestamp;
    }
}
