<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Provider\Support;

use Yiisoft\Yii\Cycle\Exception\DuplicateRoleException;

final class SchemaMerger
{
    public function merge(?array ...$parts): ?array
    {
        $schema = null;
        foreach ($parts as $part) {
            if (empty($part)) {
                $schema ??= $part;
                continue;
            }
            foreach ($part as $role => $body) {
                if ($schema !== null && array_key_exists($role, $schema)) {
                    if ($schema[$role] === $body) {
                        continue;
                    }
                    throw new DuplicateRoleException($role);
                }
                $schema[$role] = $body;
            }
        }

        return $schema;
    }
}
