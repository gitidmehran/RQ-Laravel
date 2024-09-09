@extends('./layout/layout')
@section('content')
    <div class="container-fluid mt-3">
        <div class="card">
            <div class="card-header">{{@$page_title}}</div>
            <div class="card-body">
                <form method="post" action="{{url($action)}}" class="make_ajax">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Surahs</label>
                            <select class="form-control story-surah" name="surah">
                                <option value="">Select Surah</option>
                                @foreach ($surahs as $key => $value)
                                    <option value="{{ $value['id'] }}" @if (isset($filter) && @$filter['surah'] == $value['id']) selected @endif>
                                        {{ $value['arabic'] }}-{{ $value['id'] }}</option>
                                @endforeach
                            </select>
                        </div>                        
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <table class="table table-bordered" dir="rtl">
                                <thead>
                                    <th>#</th>
                                    <th>Ayat</th>
                                    <th>Roles</th>
                                    <th>Sequenc</th>
                                </thead>
                                <tbody>
                                    @foreach(@$list as $key => $val)
                                        <tr>
                                            <td>{{$val['surah_id']}}:{{$val['ayat_no']}}</td>
                                            <td>{{$val['arabic']}}</td>
                                            <td>
                                                @foreach(@$val['roles'] as $ival)
                                                    <span class="badge bg-success badge-pill">{{$ival}}</span>
                                                @endforeach
                                            </td>
                                            <td>{{$val['sequence']}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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