
<button class="btn btn-{{$color}} btn-xs" data-toggle="modal" data-target="#myModal-{{$id}}"><i class="fa {{$icon}}"></i> {{$text}}</button>

<div class="modal fade" id="myModal-{{$id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">{{$text}}</h4>
            </div>
            <form action="{{$url}}" method="post" >
                {{ csrf_field() }}
                <div class="modal-body">
                    {{--<input type="hidden" name="order_id" value="{{$id}}">--}}
                    <div class="form-group">
                        <label for="exampleInputEmail1">店铺下的用户列表</label>
                        <select class="form-control" name="user_id">
                            @foreach($users as $key => $val)
                                <option value="{{data_get($val,'id')}}">   {{data_get($val,'nickname')}} --[ {{data_get($val,'id')}}]</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" >确认</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>

                </div>
            </form>
        </div>
    </div>
</div>
