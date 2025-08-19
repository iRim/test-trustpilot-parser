<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public $table = 'comments';
    protected $fillable = [
        'link_id',
        'user_id',
        'rate',
        'title',
        'text',
        'date_added',
        'date_exp',
    ];

    protected $casts = [
        'date_added' => 'timestamp',
        'date_exp' => 'timestamp',
    ];
}
