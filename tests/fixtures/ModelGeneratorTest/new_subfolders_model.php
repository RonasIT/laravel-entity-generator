<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property int|null priority
 * @property int media_id
 * @property string|null description
 * @property string title
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'priority',
        'media_id',
        'description',
        'title',
    ];

    protected $hidden = ['pivot'];
}