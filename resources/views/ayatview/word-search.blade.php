@extends('./layout/layout')
@section('header-scripts')
<style type="text/css">
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
  <div class="row mt-3 mb-3">
    <div class="col-12">
      <div class="card">
        <h5 class="card-header bg-success">
          Search
        </h5>
        <div class="card-body">
          <form method="get" action="{{@$search_action}}">
            <div class="row mt-3">
              <div class="col-3">
                <label>Search By</label>
                <select class="form-control" name="search_type" required>
                  <option value="">Select Search Type</option>
                  @foreach(['word','root_word'] as $val)
                  <option value="{{$val}}" @if(isset($filter) && @$filter['search_type']==$val) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-3">
                <label>Enter Word/Root Word</label>
                <input type="text" name="search" class="form-control" value="{{@$filter['search']}}" required dir="rtl">
              </div>
              <div class="col-2">
                <label>Lines PerPage</label>
                <div class="input-group mb-3">
                  <select class="form-control" name="per_page">
                    <option value="50" @if(isset($filter) && @$filter['per_page']==50) selected @endif>50</option>
                    <option value="100" @if(isset($filter) && @$filter['per_page']==100) selected @endif>100</option>
                    <option value="500" @if(isset($filter) && @$filter['per_page']==500) selected @endif>500</option>
                  </select>
                  <div class="input-group-append">
                    <span class="input-group-text" id="basic-addon2">{{@$total_records}}</span>
                  </div>
                </div>
              </div>
              <div class="col-2">
                <button class="btn btn-primary mt-3">Search</button>
              </div>
            </div>
          </form>
          <div class="col-12">
            {!! isset($links)?$links:'' !!}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-3">
    @if (!empty($data))
    @foreach ($data as $key => $val)
    <div class="col-12 mt-3">
      <div class="card card-sm">
        <div class="card-body">
          <h5 class="card-title arabic-word-font" dir="rtl">
            <a class="text-decoration-none text-black" href="https://corpus.quran.com/treebank.jsp?chapter={{ $val['surahNo'] }}&verse={{ $val['ayatNo'] }}" target="_blank">
              ({{ $val['surahNo'] }}:{{ $val['ayatNo'] }}). {{ $val['arabic'] }}
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
                    <td style="text-align: {{$value['language_id']==1?'right':'left'}}" data-id="{{$value['language_id']}}" @if($value['language_id']==1) dir="rtl" @endif class="@if($value['language_id']) urdu-word-font @endif">{{!empty($value['translation'])?$value['translation']:'-'}}</td>
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
