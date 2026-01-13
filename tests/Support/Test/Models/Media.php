<?php

namespace RonasIT\Support\Tests\Support\Test\Models;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class Media extends Model
{
    use ModelTrait;

    protected $fillable = [
        'title',
        'body',
        'data',
        'drafted',
        'user_id',
        'posted_at',
        'created_at',
        'updated_at',
    ];

    public function preview()
    {
        return $this->belongsTo(self::class);
    }
}
