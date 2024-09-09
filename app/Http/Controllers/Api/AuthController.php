<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\QuranSurahs;
use App\Models\User;
use App\Models\loginLogs;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $changelogPath = base_path('CHANGELOG.md');
        $changelogContent = file_get_contents($changelogPath);
        // Version Extraction
        preg_match('/\d+\.\d+\.\d+/', $changelogContent, $matches);
        $version = @$matches[0]?? '';

        // Date Extraction
        preg_match('/\d{4}-\d{2}-\d{2}/', $changelogContent, $matches);
        $date = @$matches[0] ?? '';

        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
        if (auth()->attempt($data)) {
            $user = auth()->user();
            $tokens = $user->tokens;

            // Revoking Previous Tokens
            if(!empty($tokens) && $user->role!=4) {
                foreach ($tokens as $token) {
                    $token->revoke();
                }
            }

            $token = auth()->user()->createToken('Secret')->accessToken;
            $surahs = QuranSurahs::select('id', 'arabic')->get();
            $user_data = User::select('id', 'name', 'role', 'is_approved')->where('email', $request->email)->get();

            // Setting Last Activity Time
            $user->last_activity = Carbon::now();
            $user->save();
            if($user->role==4){
                loginLogs::create(['user_id' => $user->id]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Authentication Successful',
                'server_version' => $version,
                'last_release_date' => $date,
                'token' => $token,
                'user_data' => $user_data,
                'surahs' => $surahs,
                'arabice_letters' => \Config('constants.word_characters')//$this->arabice_letters//\Config('constants.word_characters')
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();
            $response = array('success'=> true, 'message'=> 'Successfully logged out');
        } catch (\Exception $e) {
            $response = array('success'=> false,'message'=> $e->getMessage());
        }

        return response()->json($response);
    }
}
