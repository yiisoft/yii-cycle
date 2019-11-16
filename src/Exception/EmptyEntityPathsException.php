<?php

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class EmptyEntityPathsException extends Exception implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Bad declaration of Entity paths';
    }
    public function getSolution(): ?string
    {
        return 'There must be at least one entry in the path list.';
    }
}
