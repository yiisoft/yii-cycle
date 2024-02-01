<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Unit\Schema\Provider\Path;

use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Schema\Provider\Path\AliasesResolver;

final class AliasesResolverTest extends TestCase
{
    /**
     * @dataProvider pathsDataProvider
     */
    public function testResolve(string $path, string $expected): void
    {
        $resolver = new AliasesResolver(new Aliases(['@foo' => 'runtime/foo']));

        $this->assertSame($expected, $resolver->resolve($path));
    }

    public function testResolveException(): void
    {
        $resolver = new AliasesResolver(new Aliases(['@foo' => 'runtime/foo']));

        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve('@bar/baz');
    }

    public static function pathsDataProvider(): \Traversable
    {
        yield ['@foo/bar', 'runtime/foo/bar'];
        yield ['@foo/bar/baz', 'runtime/foo/bar/baz'];
        yield ['without/alias', 'without/alias'];
        yield ['', ''];
    }
}
