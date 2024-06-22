<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
    ];

    // Establish the relationship between models Post and User via 'user_id'

    public function user()
    // post belongs to the user with 'user_id'
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
