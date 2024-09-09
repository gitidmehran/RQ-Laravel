<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impersonations extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','personal_access_token_id'];
}
