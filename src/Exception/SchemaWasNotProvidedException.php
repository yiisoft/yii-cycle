<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class SchemaWasNotProvidedException extends RuntimeException implements FriendlyExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Schema was not provided.');
    }
    public function getName(): string
    {
        return 'Current Schema for Cycle ORM was not provided';
    }
    public function getSolution(): ?string
    {
        return 'Make sure a SchemaProvider is configured correctly.';
    }
}
