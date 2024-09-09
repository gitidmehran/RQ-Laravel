<?php

namespace App\Models;

use App\Models\User;
use App\Models\Words;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherWordTranslationInfo extends Model
{
    use HasFactory;

    protected $fillable = ['word_id','scholar_id','addresser','addressee','quranic_lexicon','quranic_lexicon_type','quranic_lexicon_number','disable','reference_word','um_ul_kitaab'];
    protected $hidden = [
        'created_at',
        'updated_at'];
    
    public function word(){
        return $this->belongsTo(Words::class,'word_id','id');
    }
    public function referredword()
    {
        return $this->belongsTo(Words::class,'reference_word','id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class,'scholar_id','id');
    }

    public function scopeOfSpecialScholars($query,$scholarId){
        if(!empty($scholarId)){
            if(is_array($scholarId)){
                $query = $query->whereIn('scholar_id',$scholarId);
            }else{
                $query = $query->where('scholar_id',$scholarId);
            }    
        }
        return $query;
    }
}
