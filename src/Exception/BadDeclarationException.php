<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;

class BadDeclarationException extends Exception
{
    /**
     * @param non-empty-string $parameter
     * @param class-string $class
     */
    public function __construct(string $parameter, string $class, mixed $argument)
    {
        $type = \is_object($argument)
            ? 'Instance of ' . \get_class($argument)
            : \ucfirst(\gettype($argument));
        parent::__construct(\sprintf(
            '%s should be instance of %s or its declaration. %s was received instead.',
            $parameter,
            $class,
            $type
        ));
    }
}
