<?php

namespace RonasIT\Support\Tests\Support\Test\Models;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function other_users()
    {
        return $this->hasMany(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
