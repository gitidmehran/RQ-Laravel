@extends('./layout/layout')
@section('header-scripts')
<style type="text/css">
  table,
  th,
  td {
    border: 1px solid black;
  }

  td {
    padding: 5px 2px !important;
    text-align: center;
  }
  td p{
    margin: 0px !important;
    padding: 0px !important;
    border-bottom: 1px solid;
  }

</style>
@endsection
@section('content')
<div class="container-fluid">
  @include('filterMenu')
  <div class="row mt-3">
    @if (!empty($data))
    @foreach ($data as $key => $val)
    <div class="col-12 mt-3">
      <div class="card card-sm">
        <div class="card-body">
          
          <h5 class="card-title arabic-word-font" dir="rtl">
            ({{ $val['surahNo'] }}:{{ $val['ayatNo'] }}). 
            <a class="text-decoration-none text-black" href="https://corpus.quran.com/treebank.jsp?chapter={{ $val['surahNo'] }}&verse={{ $val['ayatNo'] }}" target="_blank">
              {{ $val['arabic'] }}
            </a>
          </h5>
          
          {{-- AYAT TRANSLATIONS AGAINST DIFFERENT SCHOLARS --}}
          @if (!empty($val['ayats_translations']))
          @foreach ($val['ayats_translations'] as $ikey => $value)
          <div class="row @if(in_array($value['scholar_id'].'-'.$value['language_id'],$ayat_scholar_checked_languages)) mt-3 @endif">
            <div class="col-12">
              <table class="table table-bordered">
                <tbody>
                  @if(in_array($value['language_id'],$ayat_languages_settings) && in_array($value['scholar_id'].'-'.$value['language_id'],$ayat_scholar_checked_languages))
                  <tr class="p-5">
                    <td width="6%"><strong>{{$value['scholar']}}</strong></td>
                    <td width="2%"><span class="badge bg-dark">{{$value['language']}}</span></td>
                    <td style="text-align: {{$value['language_id']==1?'right':'left'}}" data-id="{{$value['language_id']}}" @if($value['language_id']==1) dir="rtl" @endif class="@if($value['language_id'] == 1) urdu-word-font @endif">{{!empty($value['translation'])?$value['translation']:'-'}}</td>
                  </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
          @endforeach
          @endif
          @if(!empty($val['words']))
            @include('ayatview.words',['words_headings'=>$words_headings,'words'=>$val['words'],'language_headings'=>$language_headings])
          @endif
        </div>
      </div>
    </div>
    @endforeach
    @endif
  </div>
</div>
@endsection
