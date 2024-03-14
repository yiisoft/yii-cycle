<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Cycle\Schema\GeneratorInterface;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

final class BadGeneratorDeclarationException extends BadDeclarationException implements FriendlyExceptionInterface
{
    /**
     * @param mixed $argument
     */
    public function __construct($argument)
    {
        parent::__construct('Generator', GeneratorInterface::class, $argument);
    }

    public function getName(): string
    {
        return 'Bad declaration of schema generator';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
When you add a generator for the Schema Conveyor you should specify a value that can be:

- Name of the class implementing GeneratorInterface.
- An object implementing GeneratorInterface.
- A function that returns an object implementing GeneratorInterface.
SOLUTION;
    }
}
