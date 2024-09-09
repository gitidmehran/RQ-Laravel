<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\AyatsTranslation;
use App\Models\WordsTranslations;
use App\Models\Teams;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'short_name',
        'auth_code',
        'user_settings',
        'is_approved',
        'translated_language',
        'user_permissions'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'last_activity'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function ayatTranslations(){
        return $this->belongsTo(AyatsTranslation::class,'id','scholar_id');
    }

    public function wordTranslations(){
        return $this->belongsTo(WordsTranslations::class,'id','scholar_id')->withoutGlobalScopes();
    }

    public function team(){
        return $this->belongsTo(Teams::class,'team_id');
    }
    

// Ahmad bhai code 
// implement impersonation
    public function canImpersonate()
    {
        $id = json_decode( $this->user_permissions, true );

        if ( !empty( $id['can_impersonate'] ) ) {
            return $this->id == $id['can_impersonate'] ? true : false;
        } else {
            return $this->role == 1 ? true : false;
        }
    }
    
    public function canBeImpersonated( $i, $check )
    {
        $impersonator = $this->find( $i );
        $user_permissions = $this->user_permissions ?? '';

        if( $impersonator && $impersonator->role == 1 ) {
            return !$this->role !== 1 ? true : false;
        }

        if( $this->role !== 1 ) {
            
            if( !empty( $impersonator ) && $check == 1 ) {
                $impersonator_permissions = json_decode( $impersonator->user_permissions, true );
                return in_array( $this->id,  $impersonator_permissions['can_be_impersonated'] ) ? true : false;
            }

            if( !empty( $user_permissions ) ) {
                $id = json_decode( $user_permissions, true );

                if( isset( $id['can_impersonate'] ) ) {
                    return in_array( $i, $id['can_be_impersonated'] ) ? true : false;
                } else {
                    $id = json_decode( $this->find( $i )->user_permissions, true );
                    return in_array( $this->id,  $id['can_be_impersonated'] ) ? true : false;
                }
            }
        }
        return !$this->role !== 1 ? true : false;
    }
    
    public function isImpersonated() {
        $token = $this->currentAccessToken();
        return $token->name == 'IMPERSONATION token';
    }

        
}
