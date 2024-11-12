<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class WelcomeBonus extends Model
{
    use ModelTrait;

    protected $fillable = [
        'title',
        'name',
    ];
}