<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
use Carbon\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $priority
 * @property int $media_id
 * @property float|null $seo_score
 * @property float $rating
 * @property string|null $description
 * @property string $title
 * @property bool|null $is_reviewed
 * @property bool $is_published
 * @property array $meta
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
        'meta',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'is_reviewed' => 'boolean',
        'is_published' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
