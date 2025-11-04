<?php

namespace RonasIT\Support\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

/**
 * @property Post|null $post
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

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}