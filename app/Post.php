<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'channel_id'
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
