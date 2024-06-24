<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    public function userDoingtheFollowing()
    {
        // follows that belong to the user
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userBeingFollowed()
    {
        return $this->belongsTo(User::class, 'followeduser');
    }
}
