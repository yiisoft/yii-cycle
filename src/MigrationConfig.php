<?php

namespace Yiisoft\Yii\Cycle;

use Spiral\Database\Config\DatabaseConfig;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Cycle\Config\BaseConfig;
use Yiisoft\Yii\Cycle\Config\Params;

/**
 * Class CommonConfig
 * @package Yiisoft\Yii\Cycle\Config
 *
 * @property string $directory
 * @property string $namespace
 * @property string $table
 * @property bool   $safe
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

    public function __construct(Params $params, Aliases $aliases)
    {
        $this->objAliases = $aliases;
        parent::__construct($params);
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
