<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Auth;

class WordsTranslations extends Model
{
    use HasFactory;
    protected $fillable = ['id','word_id','scholar_id','translation','language_id'];

    public function language(){
        return $this->belongsTo(Languages::class,'language_id');
    }

    public function scholar(){
        return $this->belongsTo(User::class,'scholar_id');
    }

    public function scopeOfSpecialScholars($query,$scholarIds,$languages=null){
        // FILTERS AGAINST SCHOLARS
        if(!empty($scholarIds)){
           if(is_array($scholarIds)){
            return $query->whereIn('scholar_id',$scholarIds);
           }else{
            return $query->where('scholar_id',$scholarIds);
           }
        }
    }   
}
