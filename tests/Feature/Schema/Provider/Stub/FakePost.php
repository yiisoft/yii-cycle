<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Feature\Schema\Provider\Stub;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity]
class FakePost
{
    #[Column(type: 'primary')]
    public int $id;

    #[Column(type: 'string')]
    public string $title;

    #[Column(type: 'datetime')]
    public \DateTimeImmutable $createdAt;
}
