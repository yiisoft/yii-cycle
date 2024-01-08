<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Stub;

use Symfony\Component\Console\Output\Output;

final class FakeOutput extends Output
{
    private string $buffer = '';

    protected function doWrite(string $message, bool $newline): void
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= \PHP_EOL;
        }
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
