<?php

namespace RonasIT\Support\Tests\Support\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property Collection<Category> $categories
 */
class WelcomeBonus extends Model
{
    use ModelTrait;

    public function getConnectionName(): string
    {
        return 'pgsql';
    }

    protected $fillable = [
        'title',
        'name',
    ];

    public function some_relation()
    {
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}