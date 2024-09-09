<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuranSurahs extends Model
{
    use HasFactory;
     protected $table ="quran_surah";
     protected $fillable = [
        'id','arabic','latin','english','localtion','sajda','ayah'
    ];
}
