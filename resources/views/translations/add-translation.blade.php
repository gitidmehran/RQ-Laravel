@extends('./layout/layout')
@section('content')
    <div class="container-fluid mt-3">
        <div class="card">
            <div class="card-header">Add Translation</div>
            <div class="card-body">
                @if(\Session::has('success'))
                    <div class="alert alert-success" role="alert">{{\Session::get('success')}}</div>
                @endif
                @if(\Session::has('error'))
                    <div class="alert alert-danger" role="alert">{{\Session::get('error')}}</div>
                @endif
                <form method="post" action="{{url('dashboard/save-translation')}}" class="make_ajax">
                    @csrf
                    <div class="row">
                        @if(in_array(\Auth::user()->role,[1,2]))
                            <div class="col-md-12 mb-2">
                                <label>Scholars</label>
                                <select class="form-control scholar" name="scholar">
                                    <option value="">Select Scholar</option>
                                    @foreach ($scholars as $key => $value)
                                        <option value="{{ $value['id'] }}" @if (isset($filter) && @$filter['scholar'] == $value['id']) selected @endif>
                                            {{$value['name']}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-12">
                            <label>Surahs</label>
                            <select class="form-control surah" name="surah">
                                <option value="">Select Surah</option>
                                @foreach ($surahs as $key => $value)
                                    <option value="{{ $value['id'] }}" @if (isset($filter) && @$filter['surah'] == $value['id']) selected @endif>
                                        {{ $value['arabic'] }}-{{ $value['id'] }}</option>
                                @endforeach
                            </select>
                        </div>                        
                        <div class="col-md-12 mt-2">
                            <label>Select Verse</label>
                            <select class="form-control from_verse" id="from-verse" name="from_verse">

                            </select>
                        </div>
                    </div>
                    <div class="row d-none translation-div">
                        <div class="col-12">
                            <div class="loader bg-white text-center pt-5 d-none" style="height:200px">
                                <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div>
                            </div>
                            <div class="card mt-4" id="translation">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary d-none my-3" id="savebtn" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.notes').select2({ width: '100%' });
    });
</script>
@endsection