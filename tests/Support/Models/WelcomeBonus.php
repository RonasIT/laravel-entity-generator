<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class WelcomeBonus extends Model
{
    use ModelTrait;

    public function getConnectionName(): string
    {
        return 'pgsql';
    }

    protected $fillable = [
        'title',
        'name',
    ];
}