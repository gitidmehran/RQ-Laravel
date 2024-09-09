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
            ({{ $val->surahNo }}:{{ $val->ayatNo }}). {{ $val->arabic }}</h5>
          
          {{-- AYAT TRANSLATIONS AGAINST DIFFERENT SCHOLARS --}}
          @php
            $ayats_translations = \App\Utility\Utility::filterArray($translations,$val->id,'ayat_id');
          @endphp
          @if (!empty($ayats_translations))
          @foreach ($ayats_translations as $ikey => $value)
          <div class="row mt-3">
            <div class="col-12">
              <table class="table table-bordered">
                <tbody>
                  <tr class="p-5">
                    <td width="6%"><strong>{{$value->scholar}}</strong></td>
                    <td width="2%"><span class="badge bg-dark">{{$value->language}}</span></td>
                    <td style="text-align: {{$value->language==1?'right':'left'}}" data-id="{{$value->language}}" @if($value->language==1) dir="rtl" @endif>{{!empty($value->translation)?$value->translation:'-'}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          @endforeach
          @endif
          @php
            $ayat_words = \App\Utility\Utility::filterArray($words,$val->id,'ayat_id');
          @endphp
          @if(!empty($ayat_words))
            <div class="row mt-3">
              <div class="col-12">
                <table class="table table-bordered table-striped" dir="rtl">
                  <tr>
                    <td>
                       @if(!empty($words_headings))
                        @foreach($words_headings as $key => $val)
                          <p class="arabic-word-font">{{$val}}</p>
                        @endforeach
                      @endif
                    </td>
                    @foreach($ayat_words as  $v)
                    <td>
                    @foreach($words_headings as $ikey => $ival)
                      <p class="arabic-word-font">
                        {{!empty($v->$ikey)?$v->$ikey:'-'}}
                      </p>
                    @endforeach

                    @php
                      $word_translations = \App\Utility\Utility::filterArray($words_translations,$v->id,'word_id');
                    @endphp
                    @if(!empty($word_translations))
                      @foreach($word_translations as $wtval)
                        <p class="arabic-word-font">{{!empty($wtval->translation)?$wtval->translation:'-'}}</p>
                      @endforeach
                    @endif
                    </td>
                    @endforeach
                  </tr>
                </table>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
    @endforeach
    @endif
  </div>
</div>
@endsection
