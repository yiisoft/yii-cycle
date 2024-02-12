<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Schema\Provider;

use Cycle\Database\DatabaseProviderInterface;
use Cycle\Schema\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Schema\Provider\FromConveyorSchemaProvider;
use Yiisoft\Yii\Cycle\Schema\SchemaConveyorInterface;

final class FromConveyorSchemaProviderTest extends TestCase
{
    public function testAddingGenerators(): void
    {
        $generator = $this->createMock(GeneratorInterface::class);

        $conveyor = $this->createMock(SchemaConveyorInterface::class);
        $conveyor
            ->expects($this->once())
            ->method('addGenerator')
            ->with(SchemaConveyorInterface::STAGE_USERLAND, $generator);

        $provider = new FromConveyorSchemaProvider(
            $conveyor,
            $this->createMock(DatabaseProviderInterface::class)
        );
        $provider = $provider->withConfig(['generators' => [$generator]]);

        $provider->read();
    }

    public function testClear(): void
    {
        $provider = new FromConveyorSchemaProvider(
            $this->createMock(SchemaConveyorInterface::class),
            $this->createMock(DatabaseProviderInterface::class)
        );

        $this->assertFalse($provider->clear());
    }
}
