<?php

namespace Yiisoft\Yii\Cycle\Config;

class DIConfigGenerator
{
    /** @var array */
    private $params;

    public function __construct(&$params)
    {
        $this->params = $params;
    }

    public function generate(): array
    {
        $result = [];
        foreach ($this->params as $key => &$config) {
            if (is_a($key, BaseConfig::class, true)) {
                $result[$key] = [
                    '__class' => $key,
                    'configure()' => [&$config]
                ];
            }
        }
        return $result;
    }
}
