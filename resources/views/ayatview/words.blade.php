<div class="row mt-3">
  <div class="col-12">
    <table class="table table-bordered table-striped" dir="rtl">
      <tr>
        <td dir="rtl" class="arabic-header">
          @if(!empty($words_headings))
          @foreach($words_headings as $key => $val)
            <p class="arabic-word-font">{{$val}}</p>
          @endforeach
          @endif

          @if(!empty($language_headings) && $show_word_translation_settings)
          @foreach($language_headings as $key => $val)
            @if(in_array($val['language_id'],$word_languages_settings) && in_array($val['scholar_id'],$word_scholars_settings) && in_array($val['scholar_id'].'-'.$val['language_id'],$word_scholar_checked_languages))
              <p class="arabic-word-font p-0">
                <span class="badge bg-dark">{{$val['label']}}</span>
              </p>
            @endif
          @endforeach
          @endif
        </td>
        @foreach(@$words as $key => $val)
          <td>
            @foreach($words_headings as $ikey => $ival)
              <p 
                  class="{{$ikey=='grammatical_description'?'urdu-word-font':'arabic-word-font'}} @if($ikey=='word') {{@$val['heading_class']}}@endif"
                  @if( !empty( $val['role'] ) && $val['role'] == 1 )
                    @if( $ikey=='meaning_urdu' || $ikey=='meaning_eng' ) 
                      data-bs-toggle="modal" data-bs-target="#data_modal" onclick="loadModal('/dashboard/get-root-word', {{$val['root_word_id']}})"
                    @endif 
                  @endif
                > <!--p-->
                @if($ikey=="word")
                  <a 
                    style="color: {{ !empty( $val['heading_class'] ) ? 'lime' : '' }}"
                    class="text-decoration-none" 
                    href="https://corpus.quran.com/wordmorphology.jsp?location=({{$val['surah_no']}}:{{$val['ayat_no']}}:{{$val['reference']}})"
                    target="_blank" 
                  >{{!empty($val[$ikey])?$val[$ikey]:'-'}}</a>
                @elseif ( $ikey == "root_word" && !empty( $val[ $ikey ] ) )
                  <a style="text-decoration: none;" target="_blank" href="https://corpus.quran.com/qurandictionary.jsp?q={{ $val['eng_root_word'] }}">
                    {{ !empty( $val[$ikey] ) ? $val[$ikey] : '-' }}
                  </a>
                @else
                  {{!empty($val[$ikey])?$val[$ikey]:'-'}}
                @endif
              </p>
            @endforeach
            @if($show_word_translation_settings)
                @foreach($language_headings as $value)
                  @if(in_array($value['language_id'],$word_languages_settings) && in_array($value['scholar_id'],$word_scholars_settings) && in_array($value['scholar_id'].'-'.$value['language_id'],$word_scholar_checked_languages))
                  @if(!empty($val['translations']))
                  @php
                    $utilityTranslation = App\Utility\Utility::getTranslation($val['translations'],$value['scholar_id'],$value['language_id']);
                  @endphp
                    <p class="{{$utilityTranslation['class']}} @if($value['language_id']==1) urdu-word-font @endif">{{$utilityTranslation['translation']}}</p>
                  @else
                    <p class="arabic-word-font">-</p>
                  @endif
                  @endif
                @endforeach
            @endif
          </td>
        @endforeach
      </tr>
    </table>
  </div>

</div>          