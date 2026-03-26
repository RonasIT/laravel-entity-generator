<?php

namespace RonasIT\Support\Tests\Support\Command\Models\Forum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

//TODO: add @property annotation for each model's field
/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
    ];

    protected $hidden = ['pivot'];
}
