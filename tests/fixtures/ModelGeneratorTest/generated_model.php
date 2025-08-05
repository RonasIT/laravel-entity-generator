<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'name',
        'reviewed_at',
        'publiched_at',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'publiched_at' => 'datetime',
    ];
}