<?php

namespace App\Models;

use App\Models\Languages;
use App\Models\User;
use App\Models\Words;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordNotes extends Model
{
    use HasFactory;

    protected $fillable = ['word_id','scholar_id','language_id','note_label','note_file'];

    public function word(){
        return $this->belongsTo(Words::class,'word_id','id');
    }

    public function scholar(){
        return $this->belongsTo(User::class,'scholar_id','id');
    }

    public function language(){
        return $this->belongsTo(Languages::class,'language_id','id');
    }

    protected $hidden = ['created_at','updated_at'];

    protected static function booted(){
        $role = Auth::user()->role;
        $authId = Auth::id();
        static::addGlobalScope(function(Builder $builder) use ($role,$authId){
            if($role==3){
                $builder->where('scholar_id',$authId);
            }
        });
    }
}
