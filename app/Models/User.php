<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Table Name
    protected $table = 'users';

    // Primary Key
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Field Access Control
    protected $fillable = ['user_id', 'username', 'email', 'password', 'role'];
    protected $hidden   = ['password'];

}
