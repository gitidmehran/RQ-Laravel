<?php

namespace App\Models;

use App\Models\InfoData;
use App\Models\OtherWordTranslationInfo;
use App\Models\PhrasesWords;
use App\Models\RegularMeaning;
use App\Models\WordNotes;
use App\Models\WordReferences;
use App\Models\WordTranslationView;
use App\Models\WordsTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Words extends Model
{
    use HasFactory;
    protected $fillable = [
        'id','surah_no','ayat_no','ayat_id','reference','word','simple_word','root_word','seperate_root_word','root_word_id','grammatical_description','contemporary_grammar','prefix','actual_word','filtered_word','postfix','simple_word','usmani_style','arabic_grammar','dottedless_word'
    ];

    protected $hidden = [
        'actual_word',
        'filtered_word',
        'postfix',
        'addresser',
        'addressee',
        'created_at',
        'updated_at'
    ];

    public function regular_meaning(){
        return $this->belongsTo(RegularMeaning::class,'id','word_id');
    }

    public function rootWordMeaning(){
        return $this->belongsTo(RootWordMeaning::class,'root_word_id','id');
    }

    public function translations(){
        return $this->hasMany(WordsTranslations::class,'word_id','id');
    }

    public function single_translation(){
        return $this->belongsTo(WordsTranslations::class,'id','word_id');
    }

    public function ayat(){
        return $this->belongsTo(InfoData::class,'ayat_id');
    }

    public function wordNotes(){
        return $this->hasMany(WordNotes::class,'word_id','id');
    }

    public function phrasesWords()
    {
        return $this->hasMany(PhrasesWords::class,'phrase_word_id','id');
    }

    public function phrase_translations(){
        return $this->hasMany(PhrasesWordsTranslations::class,'word_id','id');
    }

    public function singleReferenceWord(){
        return $this->belongsTo(WordReferences::class,'id','reference_word_id')->select('id','word_id','scholar_id','reference_word_id');
    }

    public function phraseReferenceWord(){
        return $this->belongsTo(PhrasesWords::class,'id','phrase_word_id')->select('id','word_id','scholar_id','phrase_word_id');
    }

    public function wordReferences(){
        return $this->hasMany(WordReferences::class,'word_id','id');
    }

    public function otherWordInfo(){
        return $this->belongsTo(OtherWordsInfo::class,'id','word_id');
    }

    public function otherTranslationInfo(){
        return $this->hasMany(OtherWordTranslationInfo::class,'word_id','id');
    }

    public function relatedWords(){
        return $this->hasMany(OtherWordTranslationInfo::class,'reference_word','id');
    }

    public function scopeRequestFilters($query,$request){
        // APPLY SEARCH FILTERS
        if($request->has('search') && !empty($request->input('search'))){
            $search = $request->get('search');
            $search_type = $request->get('searchType');

            if($search_type=="word"){ // FILTER ON BEHALF OF WORD MATCHING
              $query = $query->where('word', 'like', '%'.$search.'%');
            }else if($search_type=="root_word"){ // FILTER ON BEHALF OF ROOT WORD MATCHING
              $query = $query->where('root_word',$search);
            }else if($search_type=="simple_word"){
               $query = $query->where('simple_word', 'like', '%'.$search.'%');
            }
        };
        return $query;
    }

    public function scopeWithFilters($query,$search=null,$ids=null,$notIds=null){
        // SEARCH ORDER BY MATCHING WORD
        if(!empty($search))
            $query->where('word','like','%'.$search.'%');

        // FILTER WORDS AGAINTS SPECIFIC IDS
        if(!empty($ids)){
            if(is_array($ids)){
                $query->whereIn('id',$ids);
            }else{
                $query->where('id',$ids);
            }
        }

        // SKIP SPECIFIC IDS
        if(!empty($notIds)){
            if(is_array($notIds)){
                $query->whereIntegerNotInRaw('id',$notIds);
            }else{
                $query->where('id','!=',$notIds);
            }
        }
        
    }

    public function scopeWithTranslationFilter($query,$scholarIds,$showSettings=null){
        if(!empty($showSettings)){
            $query = $query->with([
                'translations' => fn($newquery) => $newquery->OfSpecialScholars($scholarIds)->with(['language:id,short_name','scholar:id,short_name']),
                'root_word_meaning' => fn($q) => $q
            ]);
        }else{
            $query->with('wordReferences','phrasesWords','otherWordInfo');
        }
        return $query;
    }

    public function wordTranslationsByView(){
        return $this->hasMany(WordTranslationView::class,'word_id','id');
    }
}
