<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Factory;

use Psr\Container\ContainerInterface;
use Spiral\Migrations\Config\MigrationConfig;
use Yiisoft\Aliases\Aliases;

final class MigrationConfigFactory
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function __invoke(ContainerInterface $container)
    {
        // Convert alias to full path
        if (isset($this->params['directory'])) {
            $aliases = $container->get(Aliases::class);
            $this->params['directory'] = $aliases->get($this->params['directory']);
        }
        return new MigrationConfig($this->params);
    }
}
