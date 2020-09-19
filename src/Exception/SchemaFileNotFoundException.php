<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class SchemaFileNotFoundException extends RuntimeException implements FriendlyExceptionInterface
{

    public function __construct(string $file)
    {
        parent::__construct('Schema file "' . $file . '" not found.');
    }

    public function getName(): string
    {
        return 'Schema file not found';
    }

    public function getSolution(): ?string
    {
        return 'Check that the file exists.';
    }
}
