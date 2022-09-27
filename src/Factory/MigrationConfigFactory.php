<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Cycle\Migrations\Config\MigrationConfig;
use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;

final class MigrationConfigFactory
{
    public function __construct(private array $params)
    {
    }

    public function __invoke(ContainerInterface $container): MigrationConfig
    {
        // Convert alias to full path
        if (isset($this->params['directory'])) {
            $aliases = $container->get(Aliases::class);
            $this->params['directory'] = $aliases->get($this->params['directory']);
        }
        return new MigrationConfig($this->params);
    }
}
