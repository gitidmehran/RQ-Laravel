<form method="post" action="{{url($action)}}" class="make_ajax" enctype="multipart/form-data">
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{@$page_title}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>
  <div class="modal-body" style="max-height: 400px;overflow-y: auto;">
    <input type="hidden" name="surah_id" value="{{$surah}}">
    <div class="row">
        <div class="col-12">
            <table class="table table-bordered table-striped" dir="rtl">
                <thead>
                    <th>#</th>
                    <th>Ayat</th>
                    <th>Roles</th>                   
                    <th>Sequence</th>                   
                </thead>
                <tbody>
                    @foreach(@$ayats as $key => $val)
                    <tr>
                        <td width="1%">
                            <div class="form-check">
                                <input 
                                class="form-check-input" 
                                name="ayats_data[{{$val['id']}}][ayat_id]" 
                                type="checkbox" 
                                value="{{$val['id']}}" 
                                id="defaultCheck{{$val['id']}}"
                                />
                            </div>
                        </td>
                        <td class="arabic-word-font" dir="rtl">{{$val['arabic']}}</td>
                        <td width="25%" dir="ltr">
                            <select class="form-control roles" name="ayats_data[{{$val['id']}}][roles][]" multiple="multiple" style="width:100%">
                              
                            </select>
                        </td>
                        <td width="1%">
                            <input type="number" name="ayats_data[{{$val['id']}}][sequence]" class="form-control">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Save</button>
</div>
</form>
<script type="text/javascript">
    if(typeof  jQuery === "undefined"){
        const script_tag = document.createElement('script');
        script_tag.setAttribute("type","text/javascript");
        script_tag.setAttribute("src","{{ asset('js/jquery-3.6.0.min.js') }}");
    }
    $(document).ready(function(){
        $('.roles').select2({
            tags: true,
        });
    });
</script>