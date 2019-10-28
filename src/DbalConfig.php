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
 * @property-read string $default
 * @property-read array  $aliases
 * @property-read array  $databases
 * @property-read array  $connections
 */
class DbalConfig extends BaseConfig
{
    protected $data = [
        'default'     => '',
        'aliases'     => [],
        'databases'   => [],
        'connections' => [],
    ];

    /** @var Aliases */
    private $objAliases;

    public function __construct(Params $params, Aliases $aliases)
    {
        $this->objAliases = $aliases;
        parent::__construct($params);
    }

    public function prepareConfig(): DatabaseConfig
    {
        return new DatabaseConfig($this->data);
    }

    protected function setConnections($data): void
    {
        $this->data['connections'] = $data;
        foreach ($this->data['connections'] as &$connection) {
            // if connection option contain alias in path
            if (isset($connection['connection']) && preg_match('/^(?<proto>\w+:)?@/', $connection['connection'], $m)) {
                $proto = $m['proto'];
                $path = $this->getAlias(substr($connection['connection'], strlen($proto)));
                $connection['connection'] = $proto . $path;
            }
        }
    }

    protected function getAlias(string $alias): string
    {
        return $this->objAliases->get($alias, true);
    }
}
