<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable=[
        'title',
        'content',
        'image',
        'category_id'
    ];
    protected  $table = 'posts';
    //Relacion de uno a muchos inversa (muchos post  un usuario)
    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id');
    }
    use HasFactory;
}
