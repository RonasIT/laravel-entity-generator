<?php

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
            'class' => $this->getPluralName($this->model),
            'entity' => $this->model,
            'entities' => $entities,
            'relations' => $this->relations,
            'fields' => $this->fields
        ]);
        $now = Carbon::now()->format('Y_m_d_His');

        $this->saveClass('migrations', "{$now}_create_{$entities}_table", $content);

        event(new SuccessCreateMessage("Created a new Migration: create_{$entities}_table"));
    }
}