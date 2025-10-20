<?php

namespace RonasIT\Support\Tests\Support\NovaTestGeneratorTest\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Tests\Support\Test\Models\Role;
use RonasIT\Support\Traits\ModelTrait;

class User extends Model
{
    use ModelTrait;

    protected $fillable = [
        'name',
        'email',
        'role_id',
        'created_at',
        'updated_at'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
