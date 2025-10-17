<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
use App\Models\Forum\Author;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $title
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'title',
    ];

    protected $hidden = ['pivot'];

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }
}
