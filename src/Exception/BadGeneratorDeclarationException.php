<?php

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class BadGeneratorDeclarationException extends Exception implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Bad declaration of schema generator';
    }
    public function getSolution(): ?string
    {
        return "When you add a generator for the Schema Conveyor you should specify a value that can be:\n\n"
            . "- Name of the class implementing GeneratorInterface.\n"
            . "- An object implementing GeneratorInterface.\n"
            . "- A function that returns an object implementing GeneratorInterface.";
    }
}
