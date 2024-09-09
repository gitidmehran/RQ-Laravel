<form method="post" action="{{url('/dashboard/add-meaning')}}" class="make_ajax" enctype="multipart/form-data">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{$title}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
        <div class="row">

            <div class="col-12">
                <table class="table table-striped table-hover table-condensed" dir="rtl">
                    <thead>
                        <tr>
                            <th><strong>Root Word</strong></th>
                            <th><strong>Add Urdu Meaning</strong></th>
                            <th><strong>Add English Meaning</strong></th>
                            <th><strong>English Root Word</strong></th>
                            <th><strong>Action</strong></th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($root_word_meanings as $r)
                        <input type="hidden" value="{{$r->id}}" name="rootword_id" class="form-control" id="rootword_id">

                        <tr>
                            <td>
                                {{$r->root_word}}
                            </td>
                            <td>
                                <div class="form-group">
                                    <input type="text" value="{{$r->meaning_urdu}}" name="rootwordmeaningurdu" class="form-control" id="exampleInputEmail2">
                                </div>
                            </td>
                            <td dir="ltr">
                                <div class="form-group">
                                    <input type="text" value="{{$r->meaning_eng}}" name="rootwordmeaningeng" class="form-control" id="exampleInputEmail1">
                                </div>
                            </td>
                            <td>
                                {{$r->english_root_word}}
                            </td>
                            <td>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
    <div class="modal-footer"></div>
</form>