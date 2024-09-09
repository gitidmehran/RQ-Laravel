<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuranSurahs;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
    $arrayWords = 
	[
		"A" => "أ",
		"b" => "ب",
		"t" => "ت",
		"v" => "ث",
		"j" => "ج",
		"hh" => "ح",
		"x" => "خ",
		"d" => "د",
		"st" => "ذ",
		"r" => "ر",
		"z" => "ز",
		"s" => "س",
		"dl" => "ش",
		"ss" => "ص",
		"dd" => "ض",
		"tt" => "ط",
		"zz" => "ظ",
		"ee" => "ع",
		"g" => "غ",
		"f" => "ف",
		"q" => "ق",
		"k" => "ك",
		"l" => "ل",
		"m" => "م",
		"n" => "ن",
		"w" => "و",
		"h" => "ه",
		"y" => "ي",
    ];
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('Secret')->accessToken;
            $surahs = QuranSurahs::select('id','arabic')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Authentication Successful',
                'surahs' => $surahs,
                'arrayWords' => $arrayWords,
                'token' => $token
                
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}
