<?php

namespace Yiisoft\Yii\Cycle\Config;

class BaseConfig
{
    protected $data = [];

    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        return method_exists($this, $getter) ? $this->$getter() : ($this->data[$name] ?? null);
    }

    public function __set($name, $value): void
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        }
    }

    public function configure(array $params): void
    {
        foreach ($params as $k => $v) {
            $this->__set($k, $v);
        }
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->data as $k => $v) {
            $result[$k] = $this->__get($k);
        }
        return $result;
    }
}
