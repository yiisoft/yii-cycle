<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use LogicException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

final class DuplicateRoleException extends LogicException implements FriendlyExceptionInterface
{
    public function __construct(string $role)
    {
        parent::__construct('The "' . $role . '" role already exists in the DB schema.');
    }

    public function getName(): string
    {
        return 'Duplicate role in the DB schema';
    }

    public function getSolution(): ?string
    {
        return 'Unable to build schema. Duplicate role in schema providers.';
    }
}
