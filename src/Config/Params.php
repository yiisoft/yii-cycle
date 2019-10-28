<?php

namespace Yiisoft\Yii\Cycle\Config;

class Params
{
    protected $data = [];

    public function __construct(array &$params)
    {
        $this->data = &$params;
    }

    public function get($name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }
}
