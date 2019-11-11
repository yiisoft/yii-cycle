<?php

namespace Yiisoft\Yii\Cycle\Config\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class PropertyNotFoundException extends \Exception implements FriendlyExceptionInterface
{

    public function getName(): string
    {
        return 'Unsupported property';
    }

    public function getSolution(): ?string
    {
        return "Perhaps you made a mistake in the name of the config parameter.\n"
            . "Verify that the specified configuration parameters are correct.";
    }
}
