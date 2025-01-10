<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Post extends Model
{
    use HasFactory, ModelTrait;

    protected $fillable = [
        'media_id',
        'is_published',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'is_published' => 'boolean',
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