<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Exception;

use Exception;

/**
 * @final Will be marked as final in next major version
 * @psalm-suppress ClassMustBeFinal
 *
 * @todo Remove this note and make the class final
 */
class ConfigException extends Exception
{
    /**
     * @param string[] $section Config path
     */
    public function __construct(array $section, string $message, int $code = 0, ?\Throwable $previous = null)
    {
        $path = \implode(' -> ', $section);
        parent::__construct(\sprintf('(%s): %s', $path, $message), $code, $previous);
    }
}
