<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Exception;

use Cycle\Schema\GeneratorInterface;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;

final class BadGeneratorDeclarationExceptionTest extends BaseBadDeclaration
{
    private const GENERATOR_INTERFACE = GeneratorInterface::class;

    public function prepareException($argument): BadGeneratorDeclarationException
    {
        return new BadGeneratorDeclarationException($argument);
    }

    public function testDefaultState(): void
    {
        $exception = $this->prepareException(null);
        $class = addslashes(self::GENERATOR_INTERFACE);

        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertSame(0, $exception->getCode());
        $this->assertMatchesRegularExpression(
            "/Generator should be instance of {$class} or its declaration\\./",
            $exception->getMessage()
        );
    }

    public function testFriendly(): void
    {
        $exception = $this->prepareException(null);

        $this->assertInstanceOf(FriendlyExceptionInterface::class, $exception);
        $this->assertNotEmpty($exception->getName());
        $this->assertNotEmpty($exception->getSolution());
    }
}
