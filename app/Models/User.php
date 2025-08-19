<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $table = 'users';
    protected $fillable = [
        'name',
        'resource_user_id',
        'country_code',
        'comments_count',
        'img'
    ];
}
