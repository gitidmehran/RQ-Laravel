<?php

namespace App\Models;

use App\Models\StoryAyats;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Story extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','scholar_id','sections','status'];

    protected $hidden = ['created_at','updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'scholar_id','id');
    }

    protected static function booted(){
        static::creating(function($model){
            $model->scholar_id = Auth::id();
        });
    }
}
