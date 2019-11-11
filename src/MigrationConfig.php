<?php

namespace Yiisoft\Yii\Cycle;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Config\BaseConfig;
use Yiisoft\Yii\Cycle\Config\Params;

/**
 * @property-read string $directory
 * @property-read string $namespace
 * @property-read string $table
 * @property-read bool   $safe
 */
class MigrationConfig extends BaseConfig
{
    protected $directory = '@root/migrations';
    protected $namespace = 'App\\Migration';
    protected $table = 'migration';
    protected $safe = false;

    /** @var Aliases */
    private $objAliases;

    public function __construct(Params $params, Aliases $aliases)
    {
        $this->objAliases = $aliases;
        parent::__construct($params);
    }

    protected function getDirectory(): string
    {
        return $this->convertAlias($this->directory);
    }

    protected function convertAlias(string $alias): string
    {
        return $this->objAliases->get($alias, true);
    }
}
