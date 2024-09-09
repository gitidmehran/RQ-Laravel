<?php

namespace App\Models;

use App\Models\AyatTranslationView;
use App\Models\AyatsAbrahamicLocutionTranslation;
use App\Models\AyatsTranslation;
use App\Models\EnglishTranslations;
use App\Models\UrduTranslations;
use App\Models\Words;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class InfoData extends Model
{
    use HasFactory;
    protected $table ="infodata";
    public $timestamps = false;

    protected $fillable = [
        'id','surah_id','ayatNo','arabic','simple_arabic','dottedless_arabic'
    ];

    protected $hidden = ['created_at','updated_at'];
    
    public function words(){
        return $this->hasMany(Words::class,'ayat_id','id');
    }

    public function scopeId($query,$ids){
        $query->whereIn('id',$ids);
    }

    public function ayatsTranslations(){
        // return $this->hasMany(AyatsTranslation::class,'ayat_id','id')->where('translation','!=','');
        return $this->hasMany(AyatsTranslation::class,'ayat_id','id');
    }

    public function scopeOfRelated($query){
        return $query->where('scholar_id',$this->surah_id);
    }

    public function scopeRequestFilters($query,$request){
        // FILTER AGAINST WORD SEARCH
        if($request->has('search') && !empty($request->get('search')))
            $query->where('arabic','like','%'.$request->get('search').'%');

        // FITLER AGAINST SURAH NUMBER
        if($request->has('surahId') && !empty($request->get('surahId')))
            $query->where('surah_id',$request->get('surahId'));

        // FILTER AGAINST SINGLE VERSE
        if($request->has('verse') && !empty($request->get('verse')))
            $query->where('ayatNo',$request->get('verse'));

        // FILTER AYATS BETWEEN TWO NUMBERS
        if($request->has('fromVerse') && !empty($request->get('fromVerse')) && $request->has('toVerse') && !empty($request->get('toVerse'))){
            $range = [$request->get('fromVerse'),$request->get('toVerse')];
            $query->whereBetween('ayatNo',$range);
        }

        return $query;
    }

    public function ayatsTranslationsByView(){
        return $this->hasMany(AyatTranslationView::class,'ayat_id','id');
    }

}
