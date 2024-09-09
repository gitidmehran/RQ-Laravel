<?php
return [
  'words' => [
        'word'=>'اللفظ',
        'root_word'=>'الجذر',
        'meaning_urdu' => 'الجذر اردو ',   // ---col
        'meaning_eng' => 'English Root', // ---col
        'grammatical_description'=>'Grammar',
        'contemporary_grammar' => 'CMPT Grammar',
        'prefix'=>'الأمامي',
        'actual_word'=>'أصلي',
        'filtered_word'=>'المخلص',
        'postfix'=>'الخلفي',
        // 'addresser' => 'Addresser',
        // 'addressee' => 'Addressee',
        // 'reference_type' => 'AbrahamicLocution',
        // 'grammer_detail' => 'Grammer',
    ],
    'perpage_showdata' => 10,
    'pagination' => [50,100,500],
    'roles' => [
        1 => 'Admin',
        2 => 'Programmer',
        3 => 'Scholar',
        4 => 'Basic User'
    ],
    'user_status' => [
        1 => 'Published',
        2 => 'Non Published',
    ],
    'default_scholars' => [1,2],
    'references' => [
      'single_al_word' => 'Single AL Word',
      'dual_al_word' => 'Multi AL Words',
    //   'two_plus_al_words'    => '2+ AL Words (NoRef)',
      'None'   => 'None AL Phrase (NoRef)'
  ],
  'word_characters' => [
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
],
'menu' => [
    [
        'label'=> 'Teams',
        'route'=> 'teams',
        'access_roles'=> [1,2] 
    ],
    [
        'label'=> 'Users',
        'route'=> 'users',
        'access_roles'=> [1,2] 
    ],
        // [
        //     'label'=> 'Stories',
        //     'route'=> 'stories',
        //     'access_roles'=> [1,2,3] 
        // ],
    [
        'label'=> 'Word By Word Quran',
        'route'=> 'word-by-word-quran',
        'access_roles'=> [1,2,3] 
    ],
    [
        'label'=> 'Word Search',
        'route'=> 'word-search',
        'access_roles'=> [1,2,3] 
    ],
    [
        'label'=> 'Scholar Translations',
        'route'=> 'scholar-translations',
        'access_roles'=> [1,2,3] 
    ],
    [
        'label'=> 'Notes',
        'route'=> 'rq-notes',
        'access_roles'=> [1,2,3] 
    ],
    [
        'label'=> 'Add New Translation',
        'route'=> 'translation',
        'access_roles'=> [1,2,3] 
    ],
    [
        'label'=> 'My Translations',
        'route'=> 'my-translations',
        'access_roles'=> [1,2,3] 
    ],
        // [
        //     'label'=> 'Reference Words',
        //     'route'=> 'reference-words',
        //     'access_roles'=> [1,2] 
        // ]      
],
];
?>