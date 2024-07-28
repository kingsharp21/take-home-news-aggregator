<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'author', 'source' , 'url' , 'urlToImage' , 'category', 'published_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
