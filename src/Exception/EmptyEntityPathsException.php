<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

final class EmptyEntityPathsException extends Exception implements FriendlyExceptionInterface
{
    #[\Override]
    public function getName(): string
    {
        return 'Bad declaration of Entity paths';
    }

    #[\Override]
    public function getSolution(): ?string
    {
        return 'There must be at least one entry in the path list.';
    }
}
