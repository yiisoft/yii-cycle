<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * Temporary LoggerInterface class
 * Slightly adapted for SQL queries
 *
 * @package Yiisoft\Yii\Cycle\Logger
 *
 * @deprecated In the future StdoutLogger will be removed (when we will have debug-tools)
 * @codeCoverageIgnore
 */
class StdoutQueryLogger implements LoggerInterface
{
    use LoggerTrait;

    private bool $display;

    private int $countWrites;
    private int $countReads;
    private array $buffer = [];
    /** @var false|resource */
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

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $message = (string) $message;
        if (!empty($context['elapsed'])) {
            $sql = strtolower($message);
            if (
                str_starts_with($sql, 'insert') ||
                str_starts_with($sql, 'update') ||
                str_starts_with($sql, 'delete')
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
        } elseif (str_starts_with($message, 'SHOW')) {
            $this->print(" > \033[34m" . $message . "\033[0m");
        } else {
            if ($this->isPostgresSystemQuery($message)) {
                $this->print(" > \033[90m" . $message . "\033[0m");

                return;
            }

            if (str_starts_with($message, 'SELECT')) {
                $this->print(" > \033[32m" . $message . "\033[0m");
            } elseif (str_starts_with($message, 'INSERT')) {
                $this->print(" > \033[36m" . $message . "\033[0m");
            } else {
                $this->print(" > \033[33m" . $message . "\033[0m");
            }
        }
    }

    private function print(string $str): void
    {
        $this->buffer[] = $str;
        if (!$this->display || $this->fp === false) {
            return;
        }
        try {
            fwrite($this->fp, "{$str}\n");
        } catch (\Throwable $e) {
            /** @psalm-suppress InvalidPropertyAssignmentValue */
            fclose($this->fp);
            $this->fp = false;
        }
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
        return
            \str_contains($query, 'tc.constraint_name') ||
            \str_contains($query, 'pg_indexes') ||
            \str_contains($query, 'pg_constraint') ||
            \str_contains($query, 'information_schema') ||
            \str_contains($query, 'pg_class');
    }
}
