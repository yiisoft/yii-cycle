<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\Support;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;
use Yiisoft\Yii\Cycle\Schema\Provider\Support\SchemaMerger;

final class SchemaMergerTest extends TestCase
{
    private function createMerger(): SchemaMerger
    {
        return new SchemaMerger();
    }

    public function testMergeNulls(): void
    {
        $result = $this->createMerger()->merge(null, null);

        $this->assertNull($result);
    }

    public function testMergeNullAndEmptyArray(): void
    {
        $result = $this->createMerger()->merge(null, []);

        $this->assertSame([], $result);
    }

    public function testMergeDifferentRoles(): void
    {
        $result = $this->createMerger()->merge(['user' => []], ['post' => []], []);

        $this->assertSame(['user' => [], 'post' => []], $result);
    }

    public function testMergeSameRoles(): void
    {
        $result = $this->createMerger()->merge(['user' => []], ['post' => [], 'user' => [], 'tag' => []]);

        $this->assertSame(['user' => [], 'post' => [], 'tag' => []], $result);
    }

    public function testMergeConflictRoles(): void
    {
        $this->expectException(DuplicateRoleException::class);

        $this->createMerger()->merge(['user' => []], ['post' => [], 'user' => ['']]);
    }

    public function testMergeNumericKeys(): void
    {
        $result = $this->createMerger()->merge([['foo'], ['bar']], [['bar'], ['baz']]);

        $this->assertSame([['foo'], ['bar'], ['bar'], ['baz']], $result);
    }
}
