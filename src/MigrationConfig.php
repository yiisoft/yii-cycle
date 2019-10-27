<?php

namespace Yiisoft\Yii\Cycle;

use Spiral\Database\Config\DatabaseConfig;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Config\BaseConfig;

/**
 * Class CommonConfig
 * @package Yiisoft\Yii\Cycle\Config
 *
 * @property-read string $directory
 * @property-read string $namespace
 * @property-read string $table
 * @property-read bool   $safe
 */
class MigrationConfig extends BaseConfig
{
    protected $data = [
        'directory' => '@root/migrations',
        'namespace' => 'App\\Migration',
        'table'     => 'migration',
        'safe'      => false,
    ];

    /** @var Aliases */
    private $objAliases;

    public function __construct(Aliases $aliases)
    {
        $this->objAliases = $aliases;
    }

    protected function getDirectory(): string
    {
        return $this->getAlias($this->data['directory']);
    }

    protected function getAlias(string $alias): string
    {
        return $this->objAliases->get($alias, true);
    }
}
