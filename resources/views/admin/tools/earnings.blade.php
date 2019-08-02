<button class="btn btn-{{$color}}" data-toggle="modal" data-target="#myModal"><i class="fa {{$icon}}"></i> 收益提现</button>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">{{$text}}</h4>
            </div>
            <form action="{{$url}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{$company->id}}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="exampleInputEmail1">收益金额</label>
                        <input type="text" name="earnings_amount" class="form-control" disabled="disabled" value="{{$company->earnings}}">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">收款方银行卡号</label>
                        <input type="text" name="enc_bank_no" class="form-control" value="{{$company->enc_bank_no}}" placeholder="收款方银行卡号">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">收款方姓名</label>
                        <input type="text" name="enc_true_name" class="form-control" value="{{$company->enc_true_name}}" placeholder="收款方姓名">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">收款方开户行名称</label>
                        <select class="form-control" name="back_code">
                            @foreach(config('global.bank') as $key => $val)
                                <option @if($company->bank_code == data_get($val,'bank_code')) selected @endif value="{{data_get($val,'bank_code')}}">{{data_get($val,'bank_name')}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">确认提现</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                </div>
            </form>
        </div>
    </div>
</div>
