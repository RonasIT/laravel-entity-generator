<?php

namespace RonasIT\EntityGenerator\Tests\Support\Command\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property int $id
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
    ];

    protected $hidden = ['pivot'];
}
