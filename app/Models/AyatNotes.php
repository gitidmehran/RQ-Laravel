<?php

namespace App\Models;

use App\Models\InfoData;
use App\Models\Languages;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyatNotes extends Model
{
    use HasFactory;

    protected $fillable = ['scholar_id','language_id', 'ayat_id','note_label','note_file'];
    protected $hidden = ['created_at','updated_at'];

    public function ayat(){
        return $this->belongsTo(InfoData::class,'ayat_id','id');
    }

    public function scholar(){
        return $this->belongsTo(User::class,'scholar_id','id');
    }

    public function language(){
        return $this->belongsTo(Languages::class,'language_id','id');
    }
}
