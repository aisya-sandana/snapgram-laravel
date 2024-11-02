<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {


    protected $primaryKey = 'userID';

    
    protected $fillable = [
        'username',
        'email',
        'password',
        'namaLengkap',
        'alamat'
    ];

    protected $hidden = ['password', 'remember_token'];
}