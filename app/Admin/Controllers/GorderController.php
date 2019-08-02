<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\GorderTab;
use App\Admin\Extensions\Tools\ShowArtwork;
use App\Http\Controllers\Api\WechatPayController;
use App\Models\Goods;
use App\Models\Gorder;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Service\ExpressService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class GorderController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('列表')
            ->description('商品订单')
            ->body($this->grid());
    }


    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        $order = Gorder::where('id', $id)->with('order_goods')->first();

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.gorder.index'))->withInput();
            }
        }

        return $content
            ->header('详情')
            ->description('商品订单')
            ->row(function (Row $row) use ($order, $id) {
                $tableData = [
                    ['订单ID', data_get($order, 'id')],
                    ['订单编号', data_get($order, 'order_sn')],
                    ['订单状态', data_get(config('global.goods.order_status'), data_get($order, 'order_status'))],
                    ['支付状态', data_get(config('global.goods.pay_status'), data_get($order, 'pay_status'))],
                    ['总金额', "<strong style='color: red'>".'¥' . data_get($order, 'total_price')."</strong>"],
                    ['配送方式', data_get(config('global.goods.delivery_method'),data_get($order,'delivery_method'))],
                    ['提交时间', date('Y-m-d H:i:s', data_get($order, 'submit_time'))],
                    ['收货人', data_get($order, 'username')],
                    ['联系电话', data_get($order, 'mobile')],
                    ['备注', data_get($order, 'remark')],
                ];
                if (data_get($order,'delivery_method') == 1){
                    $tableData[] = ['收货地址', data_get($order, 'province') . data_get($order, 'city') . data_get($order, 'district') . data_get($order, 'address')];
                }else{
                    $store = Store::where('id',$order->store_id)->first();
                    $tableData[] = ['自提门店',$store->province.$store->city.$store->district.$store->address];
                }
                $table = new Table([], $tableData);
                $row->column(12, new Box('订单详情', $table));
            })->row(function (Row $row) use ($order, $id) {
//                $goods_ids =
                $headers = [
                    '商品名称',
                    '商品图片',
                    '商品数量',
                    '商品单价',
                    '商品总价',

                ];
                $tableData = [

                ];
                //if( is_array( $order->goods ) && !empty( $order->goods ) ) {
                foreach ($order->order_goods as $val) {
                    $goods = Goods::findOrFail($val->goods_id);
                    $tableData[] = [
                        data_get($val, 'goods_name'),
                        "<img style='width:100px;height: auto;padding: 4px;line-height: 1.42857143;background-color: #fff;border: 1px solid #ddd;border-radius: 4px;' src='".url('uploads/'.data_get($goods,'cover_url'))."'>",
                        data_get($val, 'goods_num'),
                        data_get($val, 'goods_price'),
                        data_get($val, 'total_price'),
//                        data_get( $val , 'making_up_price' ) ,
                    ];
                }
                //}
                $table = new Table($headers, $tableData);

                $row->column(6, new Box('商品信息', $table));
                if ($order->delivery_method==1 && $order->order_status == 2) {
                    $result = new ExpressService($order->order_sn, $order->express, $order->express_sn);
                    $data = $result->result;
                    $success = data_get($data, 'Success');
                    $table = '暂无快递信息';
                    if ($success){
                        $traces = data_get($data, 'Traces');
                        $headers = [
                            '时间',
                            '物流消息',
                        ];
                        $tableData = [];
                        foreach ($traces as $val) {
                            $tableData[] = [
                                data_get($val, 'AcceptTime'),
                                data_get($val, 'AcceptStation')
                            ];
                        }
                        $table = new Table($headers, $tableData);
                    }
                    $row->column(6, new Box('快递信息', $table));
                }
            });

    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $tab = \request('tab');

        $grid = new Grid(new Gorder());
        if ($tab) {
            if ($tab == 'all') {
                $grid->model();
            } else if ($tab == 9) {
                $grid->model()->where('order_status', 0);
            } else {
                $grid->model()->where('order_status', $tab);
            }
        }

            if (!Admin::user()->isAdministrator()){
                $store_id = Admin::user()->store_id;
                $grid->model()->where('store_id',$store_id);
            }

        $grid->id('Id')->sortable();
        $grid->column('store.name', '店铺名称');
        $grid->order_sn('订单编号')->sortable();
        $grid->column('order_status', '订单状态')->sortable()->display(function ($value) {
            return "<span class='label label-info'>" . data_get(config('global.goods.order_status'), $value) . "</span>";
        });
        $grid->column('pay_status', '支付状态')->sortable()->payStatus('pay_status');
//        $grid->pay_sn('Pay sn');
        $grid->total_price('总价')->sortable()->setAttributes(['style' => 'color:red;']);
        $grid->column('user.username', '用户名');
        $grid->column('submit_time', '提交时间')->sortable()->display(function ($value) {
            return date('Y-m-d H:i:s', $value);
        });
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $id = $actions->row->id;
            $order = Gorder::findOrFail($id);

//            $url = url('/admin/gorder/' . $id);
            $url = route('admin.gorder.show',$id);
            $actions->append('<a class="btn btn-default btn-xs" href="' . $url . '"><i class="fa fa-globe"></i> 查看详情</a>&nbsp;&nbsp;');

            if ($order->order_status == 1 && $order->pay_status == 1) {
                $deliver_url = route('admin.gorder.deliver', $id);
                $icon = 'fa-send-o';
                $text = '快递发货';
                $color = 'success';
                $express = config('global.express');
                $actions->append(view('admin.actions.express', compact('id', 'deliver_url', 'icon', 'text', 'express', 'color')));
            }
            //订单处于待接单状态
            if ($order->order_status == 7 && $order->pay_status == 1) {
                $receiving_url = route('admin.gorder.receiving', $id);
                $actions->append('<a class="btn btn-warning btn-xs" href="' . $receiving_url . '"><i class="fa fa-check"></i> 确认接单</a>&nbsp;&nbsp;');

                $reject_url = route('admin.gorder.reject', $id);
                $actions->append('<a class="btn btn-danger btn-xs" href="' . $reject_url . '"><i class="fa fa-remove"></i> 拒绝接单</a>&nbsp;&nbsp;');
            }
            //订单处于待取货状态
            if ($order->order_status == 3 && $order->pay_status == 1) {
                $claim_url = route('admin.gorder.claim', $id);
                $actions->append('<a class="btn btn-info btn-xs" href="' . $claim_url . '"><i class="fa fa-ship"></i> 确认取货</a>&nbsp;&nbsp;');
            }
        });


        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new GorderTab());
        });

        $grid->disableCreateButton();
//        $grid->disableActions();
        return $grid;
    }

    /**
     * TODO 快递发货
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * Date: 2019/3/28 0028
     */
    public function deliver($id, Request $request)
    {

        //判断用户是否超级管理员或者是改店铺下的管理员

        $express = $request->input('express');
        $express_sn = $request->input('express_sn');
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        $order = Gorder::findOrFail($id);

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.gorder.index'))->withInput();
            }
        }

        if (!$order) {
            admin_toastr('订单获取失败', 'error');
            return back()->withInput();
        }
        if ($order->order_status != 1 || $order->pay_status != 1) {
            admin_toastr('该订单状态不能发货', 'error');
            return back()->withInput();
        }
        if (!$express) {
            admin_toastr('请填写快递公司', 'error');
            return back()->withInput();
        }
        if (!$express_sn) {
            admin_toastr('请填写快递单号', 'error');
            return back()->withInput();
        }
        $order->order_status = 2;
        $order->express = $express;
        $order->express_sn = $express_sn;
        $order->delivery_time = time();
        $result = $order->save();
        if ($result) {
            admin_toastr('发货成功,订单ID：' . $id, 'success');
            return back()->withInput();
        }
        admin_toastr('发货失败', 'error');
        return back()->withInput();
    }

    /**
     * 确认用户取货
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * Date: 2019/3/28 0028
     */
    public function claim($id)
    {
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        $order = Gorder::findOrFail($id);

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.gorder.index'))->withInput();
            }
        }

        if (!$order) {
            admin_toastr('订单获取失败', 'error');
            return back()->withInput();
        }
        if ($order->order_status != 3 || $order->pay_status != 1) {
            admin_toastr('该订单不能确认用户取货', 'error');
            return back()->withInput();
        }
        $order->order_status = 4;
        $order->delivery_time = time();
        $result = $order->save();
        if ($result) {
            admin_toastr('确认客户取货成功,订单ID：' . $id, 'success');
            return back()->withInput();
        }
        admin_toastr('确认客户取货失败', 'error');
        return back()->withInput();
    }

    /**
     * 确认接单
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * Date: 2019/3/28 0028
     */
    public function receiving($id)
    {
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        $order = Gorder::findOrFail($id);

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.gorder.index'))->withInput();
            }
        }

        if (!$order) {
            admin_toastr('订单获取失败', 'error');
            return back()->withInput();
        }
        if ($order->order_status != 7 || $order->pay_status != 1) {
            admin_toastr('该订单不能接单', 'error');
            return back()->withInput();
        }
        $delivery = $order->delivery_method;
        if ($delivery == 1) {
            $order->order_status = 1;
        }
        if ($delivery == 2) {
            $order->order_status = 3;
        }
        $res = $order->save();
        if ($res) {
            admin_toastr('成功接单，订单ID为' . $id);
            return back()->withInput();
        }
        admin_toastr('接单失败，请重试', 'error');
        return back()->withInput();
    }

    /**
     * 拒绝接单
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * Date: 2019/3/28 0028
     */
    public function reject($id)
    {
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        $order = Gorder::findOrFail($id);

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.gorder.index'))->withInput();
            }
        }

        if (!$order) {
            admin_toastr('订单获取失败', 'error');
            return back()->withInput();
        }
        if ($order->order_status != 7 || $order->pay_status != 1) {
            admin_toastr('该订单不能拒接', 'error');
            return back()->withInput();
        }
//        给用户退款
        $payModel = new WechatPayController($order->store_id);
        $transaction_id = $order->pay_sn;
        $refund_no = $order->order_sn;
        $total_fee = $order->total_price * 100;
        $refund_desc = '店主拒接您购买的商品订单';
//                $refundNotifyUrl = url('api/v1/refund/level');
        $result = $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
//        dd($result);
        if ($result) {
            $order->order_status = 8;
            $order->pay_status = 2;
            $order->save();
            admin_toastr('订单拒接成功，订单ID：' . $id . ',退款金额：￥' . $order->total_price, 'success');
            return back()->withInput();
        }
        admin_toastr('订单拒接失败', 'error');
        return back()->withInput();
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {


        $show = new Show(Gorder::findOrFail($id));
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableList();
                $tools->disableDelete();
            });
        $show->id('Id');
//        $show->
        $show->order_sn('Order sn');
        $show->order_status('Order status');
        $show->pay_status('Pay status');
        $show->pay_sn('Pay sn');
        $show->total_price('Total price');
        $show->user_id('User id');
        $show->goods_num('Goods num');
        $show->express_sn('Express sn');
        $show->delivery_method('Delivery method');
        $show->carriage('Carriage');
        $show->pay_time('Pay time');
        $show->user_delete('User delete');
        $show->username('Username');
        $show->mobile('Mobile');
        $show->province('Province');
        $show->city('City');
        $show->district('District');
        $show->address('Address');
        $show->remark('Remark');
        $show->cancel_remark('Cancel remark');
        $show->submit_time('Submit time');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
//        $show->
        $box = new Box('Box标题', $show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Gorder);

        $form->number('store_id', 'Store id');
        $form->text('order_sn', 'Order sn');
        $form->switch('order_status', 'Order status');
        $form->switch('pay_status', 'Pay status');
        $form->text('pay_sn', 'Pay sn');
        $form->decimal('total_price', 'Total price')->default(0.00);
        $form->number('user_id', 'User id');
        $form->number('goods_num', 'Goods num');
        $form->text('express_sn', 'Express sn');
        $form->switch('delivery_method', 'Delivery method');
        $form->decimal('carriage', 'Carriage')->default(0.00);
        $form->number('pay_time', 'Pay time');
        $form->switch('user_delete', 'User delete');
        $form->text('username', 'Username');
        $form->mobile('mobile', 'Mobile');
        $form->text('province', 'Province');
        $form->text('city', 'City');
        $form->text('district', 'District');
        $form->text('address', 'Address');
        $form->text('remark', 'Remark');
        $form->text('cancel_remark', 'Cancel remark');
        $form->number('submit_time', 'Submit time');

        return $form;
    }
}
