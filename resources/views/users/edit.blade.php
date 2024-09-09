<form method="post" action="{{url($action.'/'.$row['id'])}}" class="make_file_ajax" enctype="multipart/form-data">
	 @method('PUT')
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Update {{@$singular}}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required value="{{@$row['name']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Short Name</label>
                    <input type="text" class="form-control" id="short_name" name="short_name" placeholder="Short Name" value="{{@$row['short_name']}}">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" autocomplete="off" value="{{@$row['email']}}" autocomplete="nope">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off" value="">
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Role</label>
                    <select class="form-control" name="role" id="role" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $key => $val)
                            <option value="{{$key}}" @if(@$row['role']==$key) selected @endif>{{$val}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6 my-2">
                <div class="form-group">
                    <label for="name">Status</label>
                    <select class="form-control" name="is_approved" id="is_approved">
                        <option value="">Select Status</option>
                        @foreach(\Config('constants.user_status') as $key => $val)
                            <option value="{{$key}}" @if($key===@$row['is_approved']) selected @endif>{{$val}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 my-2">
                <div class="form-group">
                    <label for="name">Team</label>
                    <select class="form-control select2" name="team_id" id="team_id">
                        <option value="">Select Team</option>
                        <option value="self">Self</option>
                        @foreach($teams as $key => $val)
                            <option value="{{$val['id']}}" @if($val['id']==@$row['team_id']) selected @endif>{{$val['name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
    	<button type="submit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>