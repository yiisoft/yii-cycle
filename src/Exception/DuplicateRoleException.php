<?php

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
        return 'Check schema in your files on role duplactes.';
    }
}
