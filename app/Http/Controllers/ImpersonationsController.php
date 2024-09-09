<?php

namespace App\Http\Controllers;

use App\Models\Impersonations;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationsController extends Controller
{
    // START IMPERSONATION - WEB
    public function web_impersonate(Request $request, $userId)
    {
        $impersonator = auth()->user();
        $persona = User::find($userId);
        
        // echo "<pre>"; print_r( $impersonator ); exit;
        // Check if persona user exists, can be impersonated and if the impersonator has the right to do so.
        if (!$persona || !$persona->canBeImpersonated( $impersonator->id, 1 ) || !$impersonator->canImpersonate()) {
            return false;
        }
        
        $request->session()->put('impersonate', true); // if you need to check if session is impersonated or not
        $request->session()->put('impersonate_admin_id', Auth::id());

        auth()->guard('web')->logout();
        Auth::loginUsingId( $userId );

        return redirect()->intended(RouteServiceProvider::HOME);
    }


    // LEAVE IMPERSONATION - WEB
    public function web_leaveImpersonate( Request $request )
    {
        $request->session()->put('impersonate', false); // if you need to check if session is impersonated or not
        $userId = $request->session()->get('impersonate_admin_id');

        auth()->guard('web')->logout();
        Auth::loginUsingId( $userId, true );

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    // START IMPERSONATION - API
    public function impersonate($userId)
    {
        $impersonator = auth()->user();
        $persona = User::find($userId);
        
        // echo "<pre>"; print_r( $impersonator ); exit;
        // Check if persona user exists, can be impersonated and if the impersonator has the right to do so.
        if (!$persona || !$persona->canBeImpersonated() || !$impersonator->canImpersonate()) {
            return false;
        }
        
        // Create new token for persona
        $personaToken = $persona->createToken('IMPERSONATION token');

        // Save impersonator and persona token references
        $impersonation = new Impersonations();
        $impersonation->user_id = $impersonator->id;
        $impersonation->personal_access_token_id = $personaToken->accessToken->id;
        $impersonation->save();

        // Log out impersonator
        $impersonator->currentAccessToken()->delete();

        $response = [
            "requested_id" => $userId,
            "persona" => $persona,
            "impersonator" => $impersonator,
            "token" => $personaToken->plainTextToken
        ];

        return response()->json(['data' => $response], 200);
    }


    // LEAVE IMPERSONATION - API
    public function leaveImpersonate()
    {
        // Get impersonated user
        $impersonatedUser = auth()->user();

        // Find the impersonating user
        $currentAccessToken = $impersonatedUser->currentAccessToken();
        $impersonation = Impersonations::where('personal_access_token_id', $currentAccessToken->id)->first();
        $impersonator = User::find($impersonation->user_id);
        $impersonatorToken = $impersonator->createToken('API token')->plainTextToken;

        // Logout impersonated user
        $impersonatedUser->currentAccessToken()->delete();

        $response = [
            "requested_id" => $impersonator->id,
            "persona" => $impersonator,
            "token" => $impersonatorToken,
        ];

        return response()->json(['data' => $response], 200);
    }
}
