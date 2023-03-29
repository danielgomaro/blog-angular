<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable=[
        'name',
        'surname',
        'description',
        'email',
        'password',
    ];
    protected $hidden=[
        'password',
        'remember_token'
    ];
    //Relacion de uno a varios
    public function posts(){
        return $this->hasMany('App\Models\Post');
    }
    protected $casts=[
        'email_verified_at' => 'datetime',
    ];
}
