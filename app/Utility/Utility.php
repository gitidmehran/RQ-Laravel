<?php

namespace App\Utility;

use Illuminate\Support\Facades\Storage;
use Str;

/**
 * 
 */
class Utility
{
	
	public static function convertKeysToCamelCase($array){
		$finalArray = array();
		if(!empty($array)):
	      foreach ($array as $key=>$value):
	         if(strpos($key, '_') || strpos($key, '-'))
	            $key  =  lcfirst(str_replace(['_','-'], "", ucwords($key, "_"))); //let's convert key into camelCase

	         if(!is_array($value))
	            $finalArray[$key] = $value;
	         else
	            $finalArray[$key] = self::convertKeysToCamelCase($value);
	      endforeach;
	   endif;
      return $finalArray;
	}


	public static function convertKeysToSnakeCase($array){
		$finalArray = array();
		if(!empty($array)):
	      foreach ($array as $key=>$value):
	          $key  =  ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $key)), '_'); //let's convert key into pascalcase

	         if(!is_array($value))
	            $finalArray[$key] = $value;
	         else
	            $finalArray[$key] = self::convertKeysToSnakeCase($value);
	      endforeach;
	   endif;
      return $finalArray;
	}

	public static function getTranslation($translations,$scholar,$language)
	{
		$filter_translation = [];
		foreach ($translations as $key => $value) {
			if($value['scholar_id']==$scholar && $value['language_id']==$language){
				$filter_translation = $value;
			}
		}
		$translation = $filter_translation['translation'] ?? '-';
		$class = $filter_translation['class'] ?? '';
		$data = ['translation'=>$translation,'class' => $class];
		return  $data;
	}

	public static function uplaodFileToS3Bucket($request,$path, $fileName, $oldFileName=""){
		$url = "";
		$s3 = Storage::disk('s3');
		if($request->hasFile($fileName)){
			// UNLINK OLD FILE
			$oldFilePath = $path.'/'.$oldFileName;
			if(!empty($oldFileName) && $s3->exists($oldFilePath)){
				$s3->delete($oldFilePath);
			}

			$file = $request->file($fileName);
			$fileExtenshion = $file->getClientOriginalExtension();
			$fileName = time().Str::random(60).'.'.$fileExtenshion;
			
			$filepath = $path.'/'.$fileName;
			Storage::disk('s3')->put($filepath, fopen($file, 'r+'), 'public');
         // $url = Storage::disk('s3')->url($filepath);
         // $url = explode('/', $url);
         // $url = end($url);
         $url = $fileName;
		}
		return $url;
	}

	public static function generateS3BucketUrl($url, $path, $files = []){
		$awsUrl = config('app.aws_url');
		$withOutRootUrl = $path.'/'.$url;
		$url = $awsUrl.'/'.$path.'/'.$url;
		if(!empty($files) && !in_array($withOutRootUrl, $files)) $url="";
		
		return $url;
	}

	public static function deleteFileFromS3Bucket($file, $path){
		$awsUrl = config('app.aws_url');
		$url = $path.'/'.$file;
		$storage = Storage::disk('s3');
		if($storage->exists($url)) $storage->delete($url);
		return $awsUrl.'/'.$url;
	}

	private function formatWordsArray($data,$settings){
	    $list = [];
	      $word_selection = $settings['words_settings'] ?? $this->defualtSelection;
	    foreach ($data as $key => $value) {
	        $keyvalues = $translations = [];
	        $keyvalues['word_id'] = $value['id'];

	        // GET WORDS COLUMN ACCORDING TO SELECTION
	        foreach ($word_selection as $val) {
	            $keyvalues[$val] = $value[$val];
	        }

	        // IF USER ENABLE SETTING THEN SHOW WORD TRANSLATIONS
	        if(!empty($settings['show_word_translation_settings'])){
	            if(!empty($settings['word_translation_settings'])){
	               foreach ($settings['word_translation_settings'] as $val) {
	                  $keyvalues[$val] = @$value['other_word_info'][$val];
	               }   
	            }
	            
	            $keyvalues['reference_type_number'] = @$value['other_word_info']['reference_type_number'];
	            $keyvalues['reference_type'] = @$value['other_word_info']['reference_type'];
	            $word_references= $value['word_references'];

	            if(!empty($value['translations'])){
	               foreach ($value['translations'] as $val) {
	                  $class = $val['language']['id']==1 ? 'urdu-word-font':'arabic-word-font';
	                  if(!empty($word_references)){
	                     $references_scholar_ids = array_unique(array_column($word_references,'scholar'));
	                     if(in_array($val['scholar']['id'],$references_scholar_ids)){
	                        $class = $class.' highlight-word';
	                     }
	                  }
	                  
	                  if(!empty($value['phrases_words'])){
	                     
	                     $phrases_scholar_ids = array_unique(array_column($value['phrases_words'],'scholar'));
	                     if(in_array($val['scholar']['id'],$phrases_scholar_ids) && !strpos($class, 'highlight-word')){
	                        $class = $class.' highlight-word';
	                     }
	                     
	                  }
	                  $translations[]= [
	                     'language_id'   => $val['language']['id'],
	                     'language_name' => $val['language']['short_name'],
	                     'scholar_id'    => $val['scholar']['id'],
	                     'scholar_name'  => $val['scholar']['short_name'],
	                     'translation'   => $val['translation'],
	                     'class' => $class
	                  ];
	               }
	            }
	        }         
	        $list[$key] = $keyvalues;
	        $list[$key]['phrases_words'] = $value['phrases_words'];
	        // IF TRANSLATIONS ARE NOT EMPTY THEN FORMATE ACCORDING TO LANGUAGE
	        if(!empty($translations)){
	            usort($translations, function ($item1, $item2) {
	                return @$item2['language_id'] <=> @$item1['language_id'];
	            });
	            $list[$key]['translations'] = $translations;
	        }
	    }
	    $idsNeedsToRemove = [];

	    // FORMATING ARRAY AGAINST PHRASES
	    foreach ($list as $key => $value) {
	        if(!empty($value['phrases_words'])){
	            $phrase = $value['phrases_words'];
	            $words_names_array = array_column(array_column($phrase,'phraseword'),'word');
	            $root_words_array = array_column(array_column($phrase,'phraseword'),'root_word');
	            $words_name = implode(' ', $words_names_array);
	            $root_words_name = implode(' ',$root_words_array);      
	            foreach (@$phrase as  $ival) {
	               $idsNeedsToRemove[] = $ival['phrase_word_id'];
	            }
	            $list[$key]['word'] = $value['word'].' '.$words_name;
	            $list[$key]['root_word'] = !empty($value['root_word']) ? $value['root_word'].' '.$root_words_name:$root_words_name;
	            unset($list[$key]['phrases_words']);
	        }else{
	            unset($value['phrases_words']);
	            $list[$key] = $value;
	        }
	    }

	    $finaldata = array_filter($list,fn($item) => (!in_array($item['word_id'], $idsNeedsToRemove)));
	    return $finaldata;
	}

	public static function filterArray($array,$value,$column){
      $filer_Array = array();
      $one = array_column($array, $column);
      $two = array_keys($one,$value);
      foreach ($two as $key => $value) {
         $filer_Array[] = $array[$value];
      }
      return $filer_Array;
   }

   public static function getLanguageName($languages,$language_id){
   	$key = array_search($language_id, array_column($languages, 'id'));
   	$name = $languages[$key]['name'] ?? '';
   	return $name;
   }

}