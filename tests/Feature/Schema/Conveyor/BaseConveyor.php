<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Conveyor;

use Cycle\Schema\GeneratorInterface;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Exception\BadGeneratorDeclarationException;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;
use Yiisoft\Yii\Cycle\Tests\Feature\Schema\Conveyor\Stub\FakeGenerator;

abstract class BaseConveyor extends TestCase
{
    public static function badGeneratorProvider(): array
    {
        return [
            [stdClass::class, '#Instance of ' . stdClass::class . '[\s\w]+instead#'],
            [new DateTimeImmutable(), '#Instance of ' . DateTimeImmutable::class . ' [\s\w]+instead#'],
            [fn () => new DateTime(), '#Instance of ' . DateTime::class . ' [\s\w]+instead#'],
            [null, '#Null [\s\w]+instead#'],
            [42, '#Int [\s\w]+instead#'],
        ];
    }

    /**
     * @dataProvider badGeneratorProvider
     */
    public function testAddWrongGenerator($badGenerator, string $message): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, $badGenerator);

        $this->expectException(BadGeneratorDeclarationException::class);
        $this->expectExceptionMessageMatches($message);

        $conveyor->getGenerators();
    }

    protected function getGeneratorClassList(SchemaConveyorInterface $conveyor): array
    {
        return array_map(
            fn ($value) => $value instanceof FakeGenerator ? $value->originClass() : $value::class,
            $conveyor->getGenerators()
        );
    }

    protected function prepareContainer(): SimpleContainer
    {
        return new SimpleContainer([
            stdClass::class => new stdClass(),
            Aliases::class => new Aliases(['@test-dir' => __DIR__]),
        ], function (string $id) {
            if (is_a($id, GeneratorInterface::class, true)) {
                return new FakeGenerator($id);
            }
            throw new NotFoundException($id);
        });
    }
}
