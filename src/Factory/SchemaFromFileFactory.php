<?php

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\ORM\Schema;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;

final class SchemaFromFileFactory
{
    private string $file;

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function __invoke(ContainerInterface $container)
    {
        $aliases = $container->get(Aliases::class);
        $file = $aliases->get($this->file);
        if (!is_file($file)) {
            throw new \Exception('Schema file not found');
        }
        $schemaArray = include $file;
        return new Schema($schemaArray);
    }
}
