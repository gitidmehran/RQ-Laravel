<?php

namespace App\Http\Controllers;

use App\Models\Words;
use Illuminate\Http\Request;

class WordController extends Controller
{
    protected $singular = "Word";
    protected $plural = "Words";
    protected $view = "words.";
    public function referenceWords(Request $request){
    //     $words = Words::with(['translations' => function($q){
    //         $q->where('is_reference_word',1);
    //     }])->limit(50)->get()->toArray();
        $words = Words::whereHas('translations',function($q){
            $q->where('is_reference_word',1);
        })->limit(50)->get()->toArray();
        echo '<pre>';print_r($words);die;
    }
}
