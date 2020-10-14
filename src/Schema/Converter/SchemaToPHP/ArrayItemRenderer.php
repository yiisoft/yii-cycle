<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Schema\Converter\SchemaToPHP;

final class ArrayItemRenderer
{
    public ?string $key;
    /** @var mixed */
    public $value;
    public bool $wrapValue = true;
    public bool $wrapKey;

    /**
     * @param null|string $key
     * @param mixed $value
     * @param bool $wrapKey
     */
    public function __construct(?string $key, $value = null, bool $wrapKey = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->wrapKey = $wrapKey;
    }

    public function __toString()
    {
        $result = '';
        if ($this->key !== null) {
            $result = $this->wrapKey ? "'{$this->key}' => " : "{$this->key} => ";
        }
        return $result . $this->renderValue($this->value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function renderValue($value): string
    {
        switch (true) {
            case $value === null:
                return 'null';
            case is_bool($value):
                return $value ? 'true' : 'false';
            case is_array($value):
                return $this->renderArray($value);
            case !$this->wrapValue || is_int($value) || $value instanceof ArrayItemRenderer:
                return (string)$value;
            case is_string($value):
                return "'" . addslashes($value) . "'";
            default:
                return "unserialize('" . addslashes(serialize($value)) . "')";
        }
    }

    private function renderArray(array $value): string
    {
        if (count($value) === 0) {
            return '[]';
        }
        $result = '[';
        foreach ($value as $key => $item) {
            $result .= "\n";
            if (!$item instanceof ArrayItemRenderer) {
                $result .= is_int($key) ? "{$key} => " : "'{$key}' => ";
            }
            $result .= $this->renderValue($item) . ',';
        }
        return str_replace("\n", "\n    ", $result) . "\n]";
    }
}
