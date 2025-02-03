<?php

namespace RonasIT\Support\Tests\Support\Test\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Role extends Model
{
    use ModelTrait;

    protected $fillable = [
        'name',
    ];
}
