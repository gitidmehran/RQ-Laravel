<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Auth;

class OtherWordsInfo extends Model
{
    use HasFactory;
    protected $fillable = ['word_id','scholar_id','addresser','addressee','reference_type','reference_type_number'];

     
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
