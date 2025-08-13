<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property int media_id
 * @property int|null priority
 * @property string title
 * @property string|null description
 * @property float rating
 * @property float|null seo_score
 * @property bool is_published
 * @property bool|null is_reviewed
 * @property Carbon|null reviewed_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon published_at
 * @property array meta
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
        'media_id',
        'priority',
        'title',
        'description',
        'rating',
        'seo_score',
        'is_published',
        'is_reviewed',
        'reviewed_at',
        'created_at',
        'updated_at',
        'published_at',
        'meta',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'is_published' => 'boolean',
        'is_reviewed' => 'boolean',
        'meta' => 'array',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function comment()
    {
        return $this->hasOne(Comment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}