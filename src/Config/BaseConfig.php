<?php

namespace Yiisoft\Yii\Cycle\Config;

use Yiisoft\Yii\Cycle\Config\Exception\PropertyNotFoundException;

class BaseConfig
{
    public function __construct(Params $params)
    {
        $this->configure($params->get(static::class, []));
    }

    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new PropertyNotFoundException("The `$name` property not found in a " . static::class . " object");
    }

    public function __call($name, $arguments)
    {
        $prefix = substr($name, 0, 3);
        if ($prefix === 'get') {
            $prop = lcfirst(substr($name, 3));
            if (!property_exists($this, $prop)) {
                throw new PropertyNotFoundException("The `$name` property was not found when the `$name` method was called");
            }
            return $this->$prop;
        }
        throw new \BadMethodCallException();
    }

    public function configure(array $params): void
    {
        foreach ($params as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } elseif (property_exists($this, $name)) {
                $this->$name = $value;
            } else {
                throw new PropertyNotFoundException("The `$name` property not found when configure " . static::class . " object");
            }
        }
    }

    /**
     * Return all public and protected properties as array
     * Note: all private properties will be ignored!
     * @return array
     * @throws PropertyNotFoundException
     */
    public function toArray(): array
    {
        $result = [];
        $arrayed = (array)$this;
        $keys = array_keys($arrayed);
        foreach ($keys as $key) {
            if ($key[0] !== chr(0)) {
                // public property

                $result[$key] = $this->__get($key);
            } elseif ($key[1] === '*') {
                // protected property

                $key = substr($key, 3);
                $result[$key] = $this->__get($key);
            } else {
                // private property

                // ignore
            }
        }
        return $result;
    }
}
