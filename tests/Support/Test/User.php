<?php

namespace RonasIT\Support\Tests\Support\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class User extends Model
{
    use HasFactory, ModelTrait;

    protected $fillable = [
        'name',
        'email',
        'created_at',
        'updated_at'
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
