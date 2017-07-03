<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.10.16
 * Time: 8:36
 */

namespace RonasIT\Support\Generators;

use Carbon\Carbon;
use Illuminate\Support\Str;
use RonasIT\Support\Events\SuccessCreateMessage;

class MigrationsGenerator extends EntityGenerator
{
    protected $migrations;

    public function generate() {
        $entities = $this->getTableName($this->model);

        $content = $this->getStub('migration', [
            'entity' => $this->model,
            'entities' => $entities,
            'relations' => $this->relations,
            'fields' => $this->fields
        ]);
        $now = Carbon::now()->timestamp;

        $this->saveClass('migrations', "{$now}_create_{$entities}_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: create_{$entities}_table"));
    }
}