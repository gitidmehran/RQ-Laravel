<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class loginLogs extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];
    protected $hidden = ['created_at','updated_at'];

}