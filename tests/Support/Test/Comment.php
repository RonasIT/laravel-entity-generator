<?php

namespace RonasIT\Support\Tests\Support\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Comment extends Model
{
    use HasFactory, ModelTrait;

    protected $fillable = [
        'text',
        'post_id',
        'created_at',
        'updated_at'
    ];
}