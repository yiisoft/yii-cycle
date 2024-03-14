<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Command\Stub;

use Cycle\Migrations\Migration;

final class ErrorMigration extends Migration
{
    public function up(): void
    {
        $this->database()->execute('SELECT');
    }

    public function down(): void
    {
        $this->database()->execute('SELECT');
    }
}
