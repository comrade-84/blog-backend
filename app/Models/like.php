<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class like extends Model
{
    protected $fillable = ['user_id','post_id'] ;
    public function user(){
     return $this->BelongsTo(User::class);
    } 
    
    public function post(){
        return $this->belongsTo(Post::class) ;
    }
    //
}
