<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RootWordMeaning extends Model
{
    use HasFactory;

    protected $fillable = [
        'id','root_word','first_root_word','second_root_word','third_root_word','meaning_urdu','meaning_eng'
    ];

    public function language(){
        return $this->belongsTo(Languages::class,'language');
    }
}
