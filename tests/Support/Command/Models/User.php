<?php

namespace RonasIT\Support\Tests\Support\Command\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class User extends Model
{
    use ModelTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    public function getConnectionName(): string
    {
        return 'pgsql';
    }
}
