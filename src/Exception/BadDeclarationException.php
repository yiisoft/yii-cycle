<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;

class BadDeclarationException extends Exception
{
    public function __construct(string $parameter, string $class, mixed $argument)
    {
        $type = is_object($argument) ? 'Instance of ' . $argument::class : ucfirst(gettype($argument));
        parent::__construct(sprintf(
            '%s should be instance of %s or its declaration. %s was received instead.',
            $parameter,
            $class,
            $type
        ));
    }
}
