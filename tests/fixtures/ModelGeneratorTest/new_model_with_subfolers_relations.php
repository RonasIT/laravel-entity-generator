<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
use App\Models\Forum\Author;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $title
 * @property Collection<Author> $authors
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }
}
