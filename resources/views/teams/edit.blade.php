<form method="post" action="{{url($action.'/'.$row['id'])}}" class="make_file_ajax" enctype="multipart/form-data">
	 @method('PUT')
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Update {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required value="{{@$row['name']}}">
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Short Name</label>
                    <input type="text" class="form-control" id="short_name" name="short_name" placeholder="Short Name" value="{{@$row['short_name']}}">
                </div>
            </div>           
        </div>
    </div>
    <div class="modal-footer">
    	<button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>