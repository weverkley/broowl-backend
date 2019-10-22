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
        'content', 'url', 'channel_id', 'type'
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function options()
    {
        return $this->hasMany(Poll::class);
    }
}
