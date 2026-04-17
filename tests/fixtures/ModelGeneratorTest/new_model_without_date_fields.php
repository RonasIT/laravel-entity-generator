<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property int $id
 * @property int|null $priority
 * @property int $media_id
 * @property float|null $seo_score
 * @property float $rating
 * @property string|null $description
 * @property string $title
 * @property bool|null $is_reviewed
 * @property bool $is_published
 * @property array $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'is_reviewed' => 'boolean',
        'is_published' => 'boolean',
        'meta' => 'array',
    ];
}
