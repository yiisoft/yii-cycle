<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Cycle\ORM\RepositoryInterface;
use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Container\ContainerExceptionInterface;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

final class NotInstantiableClassException extends Exception implements ContainerExceptionInterface,
                                                                       FriendlyExceptionInterface
{
    #[Pure]
    public function __construct(string $class, string $message = null, int $code = 0, Exception $previous = null)
    {
        if ($message === null) {
            $message = sprintf(
                'Can not instantiate "%s" because it is not a subclass of "%s".',
                $class,
                RepositoryInterface::class
            );
        }
        parent::__construct($message, $code, $previous);
    }

    public function getSolution(): ?string
    {
        return 'Make sure that the class is instantiable and implements ' . RepositoryInterface::class;
    }
}
