<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
class Notes extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['note_label', 'note_file','created_user'];

    protected static function booted(){
        $role = Auth::user()->role;
        $authId = Auth::id();
        static::addGlobalScope(function(Builder $builder) use ($role,$authId){
            if($role==3){
                $builder->where('created_user',$authId);
            }
        });
    }
}
