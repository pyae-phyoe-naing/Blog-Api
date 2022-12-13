<?php

namespace App\Models;

use App\Models\User;
use App\Models\Media;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    public function cateogry(){
        return $this->belongsTo(Category::class,'category_id','id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    public function image(){
        // Polymoph
        return $this->morphOne(Media::class, 'model');
        //
       // return $this->hasOne(Media::class,'model_id','id');
    }
}
