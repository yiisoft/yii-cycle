<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * Temporary LoggerInterface class
 * Slightly adapted for SQL queries
 * @package Yiisoft\Yii\Cycle\Logger
 * @deprecated should be replaced
 */
class StdoutQueryLogger implements LoggerInterface
{
    use LoggerTrait;

    private bool $display;

    private int $countWrites;
    private int $countReads;
    private array $buffer = [];
    private $fp;

    public function __construct()
    {
        $this->display = true;
        $this->countWrites = 0;
        $this->countReads = 0;
        $this->fp = fopen('php://stdout', 'w');
    }

    public function countWriteQueries(): int
    {
        return $this->countWrites;
    }

    public function countReadQueries(): int
    {
        return $this->countReads;
    }

    public function log($level, $message, array $context = []): void
    {
        if (!empty($context['elapsed'])) {
            $sql = strtolower($message);
            if (
                strpos($sql, 'insert') === 0 ||
                strpos($sql, 'update') === 0 ||
                strpos($sql, 'delete') === 0
            ) {
                $this->countWrites++;
            } elseif (!$this->isPostgresSystemQuery($sql)) {
                ++$this->countReads;
            }
        }

        if ($level === LogLevel::ERROR) {
            $this->print(" ! \033[31m" . $message . "\033[0m");
        } elseif ($level === LogLevel::ALERT) {
            $this->print(" ! \033[35m" . $message . "\033[0m");
        } elseif (strpos($message, 'SHOW') === 0) {
            $this->print(" > \033[34m" . $message . "\033[0m");
        } else {
            if ($this->isPostgresSystemQuery($message)) {
                $this->print(" > \033[90m" . $message . "\033[0m");

                return;
            }

            if (strpos($message, 'SELECT') === 0) {
                $this->print(" > \033[32m" . $message . "\033[0m");
            } elseif (strpos($message, 'INSERT') === 0) {
                $this->print(" > \033[36m" . $message . "\033[0m");
            } else {
                $this->print(" > \033[33m" . $message . "\033[0m");
            }
        }
    }

    private function print(string $str): void
    {
        $this->buffer[] = $str;
        if (!$this->display) {
            return;
        }
        fwrite($this->fp, "{$str}\n");
    }

    public function display(): void
    {
        $this->display = true;
    }

    public function hide(): void
    {
        $this->display = false;
    }

    public function getBuffer(): array
    {
        return $this->buffer;
    }

    public function cleanBuffer(): void
    {
        $this->buffer = [];
    }

    protected function isPostgresSystemQuery(string $query): bool
    {
        $query = strtolower($query);
        if (
            strpos($query, 'tc.constraint_name') ||
            strpos($query, 'pg_indexes') ||
            strpos($query, 'tc.constraint_name') ||
            strpos($query, 'pg_constraint') ||
            strpos($query, 'information_schema') ||
            strpos($query, 'pg_class')
        ) {
            return true;
        }

        return false;
    }
}
