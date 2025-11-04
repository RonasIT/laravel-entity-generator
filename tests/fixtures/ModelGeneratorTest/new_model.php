<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int|null $priority
 * @property int $media_id
 * @property float|null $seo_score
 * @property float $rating
 * @property string|null $description
 * @property string $title
 * @property bool|null $is_reviewed
 * @property bool $is_published
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon $published_at
 * @property array $meta
 * @property Comment|null $comment
 * @property Collection<User> $users
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'priority',
        'media_id',
        'seo_score',
        'rating',
        'description',
        'title',
        'is_reviewed',
        'is_published',
        'reviewed_at',
        'created_at',
        'updated_at',
        'published_at',
        'meta',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'is_reviewed' => 'boolean',
        'is_published' => 'boolean',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    public function comment(): HasOne
    {
        return $this->hasOne(Comment::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
