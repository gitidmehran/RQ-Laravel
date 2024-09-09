<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Impersonations;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Utility\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ImpersonationsController extends Controller
{

    // START IMPERSONATION - API
    public function impersonate($userId)
    {
        try {
            $user = auth()->user();
            $role = $user->role;
            $persona = User::find($userId);
            $permissions = !empty($user->user_permissions)?json_decode($user->user_permissions,true):[];
            if($role ==1 || in_array($userId,$permissions)){
                // CREATE TOKEN FOR TARGET USER
                $personaToken = $persona->createToken('IMPERSONATION token');

                // SAVE IMPERSONATION DETAIL IN DATABASE
                $impersonationData =[
                    'user_id' => $user->id,
                    'personal_access_token_id' => $personaToken->token->id
                ];
                Impersonations::create($impersonationData);

                // DELETE IMPERSONATOR TOKEN
                $user->token()->delete();
                $personaData = [
                    'role' => $persona->role,
                    'name' => $persona->name
                ];

                $impersonator = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ];
                $persona->last_activity = Carbon::now();
                $persona->save();
                $response = [
                    'requested_id' => $userId,
                    'persona' => $personaData,
                    'impersonator' => $impersonator,
                    'token' => $personaToken->accessToken
                ];
            }else{
                $response = array('success' => false, 'message' => 'Permissions are not allowed');
            }
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }


    // LEAVE IMPERSONATION - API
    public function leaveImpersonate()
    {
        try {
            $impersonatedUser = auth()->user();
            $currentAccessToken = $impersonatedUser->token();
            $impersonation = Impersonations::where('personal_access_token_id', $currentAccessToken->id)->first();
            $impersonator = User::find($impersonation->user_id);
            $impersonatorToken = $impersonator->createToken('API token')->accessToken;

            // Logout impersonated user
            $impersonatedUser->token()->delete();

            $persona = [
                'id' => $impersonator->id,
                'name' => $impersonator->name,
                'email' => $impersonator->email,
                'role' => $impersonator->role,
            ];
            
            $impersonator->last_activity = Carbon::now();
            $impersonator->save();
            
            $response = [
                "requested_id" => $impersonator->id,
                "persona" => $persona,
                "token" => $impersonatorToken,
            ];
        } catch (\Exception $e) {
            $response = array('success' => false, 'message' => $e->getMessage());
        }
        $response = Utility::convertKeysToCamelCase($response);
        return response()->json($response);
    }
}
