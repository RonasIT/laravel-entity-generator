<?php

namespace RonasIT\Support\Tests\Support\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Post extends Model
{
    use HasFactory, ModelTrait;

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

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function other_users()
    {
        return $this->hasMany(User::class);
    }

    public function comments()
    {
        return $this->belongsTo(Comment::class);
    }
}
