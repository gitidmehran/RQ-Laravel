<?php

namespace App\Models;

use App\Models\InfoData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryAyats extends Model
{
    use HasFactory;
    protected $fillable = ['ayat_id','story_id','sequence'];

    protected $hidden = ['created_at','updated_at'];

    public function ayat(){
        return $this->belongsTo(InfoData::class,'id','ayat_id');
    }
}
