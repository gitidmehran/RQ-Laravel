<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordTranslationView extends Model
{
    protected $table = 'word_translation_view';

    protected $fillable = ['ayat_id','scholar_id','language_id','scholar_name','language_name','translation'];

    public function scopeOfSettingsFilter($query,$settings){
        $scholarIds = $settings['wordScholarsIDs'] ?? [];
        $languageIds = $settings['allowedWordLanguages'] ?? [];


        if(!empty($scholarIds)){
            if(is_array($scholarIds))
                $query =  $query->whereIn('scholar_id',$scholarIds);
            else
                $query =  $query->where('scholar_id',$scholarIds);
        }

        if(!empty($languageIds)){
            if(is_array($languageIds))
                $query =  $query->whereIn('language_id',$languageIds);
            else
                $query =  $query->where('language_id',$languageIds);
        }

       return $query;
    }

    public function scopeOfSpecialScholars($query,$scholarIds){
        if(is_array($scholarIds))
            $query =  $query->whereIn('scholar_id',$scholarIds);
        else
            $query =  $query->where('scholar_id',$scholarIds);
        return $query;
    }  
}
