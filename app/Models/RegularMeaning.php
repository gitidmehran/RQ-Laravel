<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegularMeaning extends Model
{
    use HasFactory;
    protected $fillable = [
        'id','word_id','compound_word_id','english_translation','urdu_translation'
    ];
}
