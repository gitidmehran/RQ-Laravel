<?php

namespace App\Models;

use App\Models\User;
use App\Models\Words;
use App\Models\WordsTranslations;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordReferences extends Model
{
    use HasFactory;
    protected $table = "word_references";
    protected $fillable = ['word_id','reference_word_id','scholar_id'];

    public function word(){
        return $this->belongsTo(Words::class,'word_id','id');
    }

    public function wordTranslation()
    {
        return $this->hasOneThrough(WordsTranslations::class,Words::class,'id','word_id');
    }

    public function scholarInfo(){
        return $this->belongsTo(User::class,'scholar_id','id');
    }

    public function referenceword(){
        return $this->belongsTo(Words::class,'id','reference_word_id');
    }

    protected static function booted(){
        $user = Auth::user();
        
        // static::addGlobalScope(function(Builder $builder) use ($user){
        //     $role = $user->role;
        //     $role = $user->is_approved;
        //     $authId = $user->id;
        //     $user_settings = !empty($user->user_settings)?json_decode($user->user_settings,true):[];
        //     $default_scholars = \Config('constants.default_scholars');
        //     $scholarIds = $user_settings['word_scholars_settings'] ?? $default_scholars;
        //     if($role==3 && $is_approved==0){
        //         $builder->where('scholar_id',$authId);
        //     }else{
        //         $builder->whereIn('scholar_id',$scholarIds);
        //     }
        // });
    }
}
