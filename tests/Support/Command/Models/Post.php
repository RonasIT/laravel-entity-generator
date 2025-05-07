<?php

namespace RonasIT\Support\Tests\Support\Command\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'title',
        'body',
        'data',
        'drafted',
        'user_id',
        'posted_at',
        'created_at',
        'updated_at'
    ];

    public function getConnectionName(): string
    {
        return 'pgsql';
    }
}
