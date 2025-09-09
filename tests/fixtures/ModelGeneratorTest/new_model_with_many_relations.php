<?php

namespace App\Models\Forum;

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
use App\Models\User;

//TODO: add @property annotation for each model's field
/**
 */
class Post extends Model
{
    use ModelTrait;

    protected $fillable = [
    ];

    protected $hidden = ['pivot'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
