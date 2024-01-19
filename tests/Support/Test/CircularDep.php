<?php

namespace RonasIT\Support\Tests\Support\Test;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

class CircularDep extends Model
{
    use HasFactory, ModelTrait;

    protected $fillable = [
        'title',
        'body',
        'data',
        'drafted',
        'user_id',
        'posted_at',
        'created_at',
        'updated_at'
    ];

    protected static function newFactory(): CircularDepFactory
    {
        return CircularDepFactory::new();
    }

    public function dep()
    {
        return $this->belongsTo(CircularDep::class);
    }
}
