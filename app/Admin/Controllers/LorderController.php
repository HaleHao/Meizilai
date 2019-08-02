<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\LorderTab;
use App\Http\Controllers\Api\WechatPayController;
use App\Models\Lorder;
use App\Http\Controllers\Controller;
use App\Models\Sorder;
use App\Models\Users;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class LorderController extends Controller
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
            ->description('会员等级订单')
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
        $order = Lorder::where('id', $id)->first();

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.lorder.index'))->withInput();
            }
        }

        return $content
            ->header('详情')
            ->description('会员等级订单')
            ->row(function (Row $row) use ($order, $id) {
                $tableData = [
                    ['订单ID', data_get($order, 'id')],
                    ['订单编号', data_get($order, 'order_sn')],
                    ['订单状态', data_get(config('global.level.order_status'), data_get($order, 'order_status'))],
                    ['支付状态', data_get(config('global.level.pay_status'), data_get($order, 'pay_status'))],
                    ['总金额', "<strong style='color: red'>" . '¥' . data_get($order, 'total_price') . "</strong>"],
                    ['提交时间', date('Y-m-d H:i:s', data_get($order, 'submit_time'))],
                    ['用户名', data_get($order, 'username')],
                    ['联系电话', data_get($order, 'mobile')],
                ];
                if (data_get($order, 'pay_status') == 1) {
                    $tableData[] = ['支付流水号', data_get($order, 'pay_sn')];
                    $tableData[] = ['支付时间', date('Y-m-d H:i:s', data_get($order, 'pay_time'))];
                }
                $table = new Table([], $tableData);
                $row->column(12, new Box('订单详情', $table));
            })->row(function (Row $row) use ($order, $id) {
                $user = Users::where('id', $order->user_id)->with('firstUser', 'card')->first();
                $tableData = [
                    ['用户ID', data_get($user, 'id')],
                    ['用户名', data_get($user, 'username')],
                    ['用户昵称', data_get($user, 'nickname')],
                    ['用户头像', "<img style='width:100px;height: auto;padding: 4px;line-height: 1.42857143;background-color: #fff;border: 1px solid #ddd;border-radius: 4px;' src='" . data_get($user, 'avatar') . "'>"],
                    ['联系方式', data_get($user, 'mobile')],
                    ['会员卡', data_get(data_get($user, 'card'), 'name')],
//                    ['会员卡',data_get($user,'mobile')],
                ];

                $table = new Table([], $tableData);
                $row->column(6, new Box('用户信息', $table));

                $tableData = [
                    ['用户ID', data_get(data_get($user, 'firstUser'), 'id')],
                    ['用户名', data_get(data_get($user, 'firstUser'), 'username')],
                    ['用户昵称', data_get(data_get($user, 'firstUser'), 'nickname')],
                    ['用户头像', "<img style='width:100px;height: auto;padding: 4px;line-height: 1.42857143;background-color: #fff;border: 1px solid #ddd;border-radius: 4px;' src='" . data_get(data_get($user, 'firstUser'), 'avatar') . "'>"],
                    ['联系方式', data_get(data_get($user, 'firstUser'), 'mobile')],
                ];
                $table = new Table([], $tableData);
                $row->column(6, new Box('上级用户信息', $table));
            })->row(function (Row $row) use ($order, $id) {
//                $url =
                if ($order->order_status == 1 && $order->pay_status == 1) {
                    $agree = route('admin.lorder.agree', $id);
                    $reject = route('admin.lorder.reject', $id);
                    $row->column(12, '<a class="btn btn-success btn-sx" href="' . $agree . '">同意</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-danger btn-sx" href="' . $reject . '">拒绝</a>');
                }
            });
    }

    /**
     * 同意审核
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * Date: 2019/4/1 0001
     */
    public function agree($id)
    {
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        DB::beginTransaction();
        try {
            $order = Lorder::where('id', $id)->lockForUpdate()->first();

            if (!Admin::user()->isAdministrator()){
                $store_id = Admin::user()->store_id;
                if ($order->store_id != $store_id){
                    admin_toastr('您不是该店铺的管理员','error');
                    return redirect(route('admin.lorder.index'))->withInput();
                }
            }

            if (!$order) {
                admin_toastr('订单获取失败', 'error');
                return back()->withInput();
            }
            $order->order_status = 2;
            $order->save();
            $user = Users::where('id', $order->user_id)->lockForUpdate()->first();
            $user->is_partner = 1;
            $user->user_type = 2;
            $user->level_id = $order->level_id;
            $user->save();
            DB::commit();
            admin_toastr('通过审核成功', 'success');
            return back()->withInput();
        } catch (\Exception $exception) {
            DB::rollBack();
            admin_toastr('通过审核失败', 'error');
            return back()->withInput();
        }
    }

    /**
     * 拒绝
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * Date: 2019/4/1 0001
     */
    public function reject($id)
    {
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        DB::beginTransaction();
        try {
            $order = Lorder::where('id', $id)->lockForUpdate()->first();

            if (!Admin::user()->isAdministrator()){
                $store_id = Admin::user()->store_id;
                if ($order->store_id != $store_id){
                    admin_toastr('您不是该店铺的管理员','error');
                    return redirect(route('admin.lorder.index'))->withInput();
                }
            }

            if (!$order) {
                admin_toastr('订单获取失败', 'error');
                return back()->withInput();
            }

            //TODO 对用户进行退款
            $payModel = new WechatPayController($order->store_id);
            $transaction_id = $order->pay_sn;
            $refund_no = $order->order_sn;
            $total_fee = $order->total_price * 100;
            $refund_desc = '店主拒绝您申请的会员升级';
//                $refundNotifyUrl = url('api/v1/refund/level');
            $result = $payModel->refund($transaction_id, $refund_no, $total_fee, $refund_desc);
            if ($result) {
                $order->order_status = 3;
                $order->save();
                DB::commit();
                admin_toastr('拒绝申请成功', 'success');
                return back()->withInput();
            }
            DB::rollBack();
            admin_toastr('拒绝申请失败,退款失败', 'error');
            return back()->withInput();
        } catch (\Exception $exception) {
            DB::rollBack();
            admin_toastr('拒绝申请失败', 'error');
            return back()->withInput();
        }
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
        $grid = new Grid(new Lorder);
        $tab = \request('tab');
        if ($tab) {
            if ($tab == 'all') {
                $grid->model();
            } else if ($tab == 4) {
                $grid->model()->where('order_status', 0);
            } else {
                $grid->model()->where('order_status', $tab);
            }
        }

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            $grid->model()->where('store_id',$store_id);
        }

        $grid->id('订单ID');
        $grid->column('store.name', '店铺名称');
        $grid->column('level.name', '等级名称');
        $grid->order_sn('订单编号');

        $grid->column('order_status', '订单状态')->sortable()->display(function ($value) {
            return "<span class='label label-info'>" . data_get(config('global.level.order_status'), $value) . "</span>";
        });
        $grid->column('pay_status', '支付状态')->sortable()->payStatus('pay_status');
//        $grid->apply_status('审核状态');
        $grid->total_price('总价')->setAttributes(['style' => 'color:red;']);
        $grid->username('用户名称');
        $grid->mobile('手机号码');
        $grid->column('submit_time', '提交时间')->sortable()->display(function ($value) {
            return date('Y-m-d H:i:s', $value);
        });
        $grid->created_at('创建时间')->sortable();
        $grid->updated_at('更新时间')->sortable();

        $grid->disableCreateButton();
//        $grid->

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $id = $actions->row->id;

            $order = Lorder::where('id', $id)->first();

            $url = route('admin.lorder.show', $id);
            $actions->append('<a class="btn btn-default btn-xs" href="' . $url . '"><i class="fa fa-globe"></i> 查看详情</a>&nbsp;&nbsp;');

            if ($order->order_status == 1 && $order->pay_status == 1) {
                $receiving_url = route('admin.lorder.agree', $id);
                $actions->append('<a class="btn btn-success btn-xs" href="' . $receiving_url . '"><i class="fa fa-check"></i> 同意申请</a>&nbsp;&nbsp;');

                $reject_url = route('admin.lorder.reject', $id);
                $actions->append('<a class="btn btn-danger btn-xs" href="' . $reject_url . '"><i class="fa fa-remove"></i> 拒绝申请</a>&nbsp;&nbsp;');
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new LorderTab());
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Lorder::findOrFail($id));

        $show->id('Id');
        $show->level_id('Level id');
        $show->level_name('Level name');
        $show->user_id('User id');
        $show->store_id('Store id');
        $show->order_sn('Order sn');
        $show->username('Username');
        $show->mobile('Mobile');
        $show->pay_sn('Pay sn');
        $show->order_status('Order status');
        $show->pay_status('Pay status');
        $show->apply_status('Apply status');
        $show->total_price('Total price');
        $show->submit_time('Submit time');
        $show->pay_time('Pay time');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Lorder);

        $form->number('level_id', 'Level id');
        $form->text('level_name', 'Level name');
        $form->number('user_id', 'User id');
        $form->number('store_id', 'Store id');
        $form->text('order_sn', 'Order sn');
        $form->text('username', 'Username');
        $form->mobile('mobile', 'Mobile');
        $form->text('pay_sn', 'Pay sn');
        $form->switch('order_status', 'Order status');
        $form->switch('pay_status', 'Pay status');
        $form->switch('apply_status', 'Apply status');
        $form->decimal('total_price', 'Total price');
        $form->number('submit_time', 'Submit time');
        $form->number('pay_time', 'Pay time');

        return $form;
    }
}
