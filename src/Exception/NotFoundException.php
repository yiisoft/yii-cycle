<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Container\NotFoundExceptionInterface;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

final class NotFoundException extends Exception implements NotFoundExceptionInterface, FriendlyExceptionInterface
{
    #[Pure]
    public function __construct(string $class, string $message = null, int $code = 0, Exception $previous = null)
    {
        if ($message === null) {
            $message = sprintf('No definition or class found or resolvable for "%s".', $class);
        }
        parent::__construct($message, $code, $previous);
    }

    public function getSolution(): ?string
    {
        return 'Check if the class exists or if the class is properly defined.';
    }
}
