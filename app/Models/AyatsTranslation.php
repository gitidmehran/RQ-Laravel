<?php

namespace App\Models;

use App\Models\InfoData;
use App\Models\Languages;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyatsTranslation extends Model
{
    use HasFactory;
    protected $fillable = [
        'id','ayat_id','scholar_id','translation','language_id','addressee','addresser','created_user'
    ];

    protected $casts = [
        'ayat_id' => 'int',
        'scholar_id' => 'int',
        'language_id' => 'int',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function ayat(){
        return $this->belongsTo(InfoData::class,'ayat_id');
    }

    public function scopeOfNotEmpty($query){
        return $query->where('translation','!=','');
    }

    public function language(){
        return $this->belongsTo(Languages::class,'language_id')->select('id','name','short_name');
    }

    public function scholarinfo(){
        return $this->belongsTo(User::class,'scholar_id')->where('role',3);
    }

    public function scopeOfSpecialScholars($query,$scholarIds){
       if(!empty($scholarIds)){
            if(is_array($scholarIds))
                return $query->whereIn('scholar_id',$scholarIds);
            else
                return $query->where('scholar_id',$scholarIds);
       }
    }

    public function scopeOfSingleScholar($query,$scholarId){
        if(!empty($scholarId))
            return $query->where('scholar_id',$scholarId);
    }
}
