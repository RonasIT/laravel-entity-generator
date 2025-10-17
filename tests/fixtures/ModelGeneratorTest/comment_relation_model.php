<?php

namespace RonasIT\Support\Tests\Support\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}