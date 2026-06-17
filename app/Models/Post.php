<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id',
    ];

        public function autor()
        {
            return $this->belongsTo(User::class, 'user_id');
        }
}
