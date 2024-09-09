<?php

//namespace App\Http\Controllers;
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Words;
use Illuminate\Http\Request;

class WordController extends Controller
{
    /*
protected $per_page = '';
public function __construct(){
$this->per_page = Config('constants.perpage_showdata');

}*/
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
