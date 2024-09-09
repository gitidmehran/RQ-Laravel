<form method="post" action="{{url($action)}}" class="make_file_ajax" enctype="multipart/form-data">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Add New {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="note_label">Note Label</label>
                    <input type="text" class="form-control" id="note_label" name="note_label" placeholder="Note Label">
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="note_label">Note File</label>
                    <input type="file" class="form-control" id="note_file" name="note_file" placeholder="Note Label">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>