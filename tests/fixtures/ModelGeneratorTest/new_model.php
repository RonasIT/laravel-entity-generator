<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'media_id',
        'is_published',
        'reviewed_at',
        'published_at',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'is_published' => 'boolean',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function comment()
    {
        return $this->hasOne(Comment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}