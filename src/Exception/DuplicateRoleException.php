<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use LogicException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class DuplicateRoleException extends LogicException implements FriendlyExceptionInterface
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
        return 'Check the provided parts of the DB schema for duplicate roles.';
    }
}
