<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Factory;

use Cycle\Migrations\Config\MigrationConfig;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Cycle\Factory\MigrationConfigFactory;

final class MigrationConfigFactoryTest extends TestCase
{
    public function testInvokeWithoutAlias(): void
    {
        $factory = new MigrationConfigFactory(['directory' => 'test/foo/bar']);

        $this->assertEquals(
            new MigrationConfig(['directory' => 'test/foo/bar']),
            $factory(new SimpleContainer([Aliases::class => new Aliases()]))
        );
    }

    public function testInvoke(): void
    {
        $factory = new MigrationConfigFactory(['directory' => '@test/foo/bar']);

        $this->assertEquals(
            new MigrationConfig(['directory' => 'src/test/app/foo/bar']),
            $factory(new SimpleContainer([Aliases::class => new Aliases(['@test' => 'src/test/app'])]))
        );
    }
}
