<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class SchemaNotReadException extends RuntimeException implements FriendlyExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Cycle Schema not read.');
    }
    public function getName(): string
    {
        return 'Current Schema for Cycle ORM has not been read';
    }
    public function getSolution(): ?string
    {
        return 'If you are using the SchemaManager to get the Schema, make sure it is configured correctly.';
    }
}
