<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'user_id',
    ];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
        ];
    }

    // Establish the relationship between models Post and User via 'user_id'

    public function user()
    // post belongs to the user with 'user_id'
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
