<?php

namespace RonasIT\Support\Tests\Support\Command\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

//TODO: add @property annotation for each model's field
/**
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
    ];

    protected $hidden = ['pivot'];
}