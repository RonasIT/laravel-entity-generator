<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
use App\Models\Forum\Author;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $id
 * @property string $title
 * @property Collection<Author> $authors
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
