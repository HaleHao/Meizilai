<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\CorderTab;
use App\Models\Corder;
use App\Http\Controllers\Controller;
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

class CorderController extends Controller
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
            ->description('会员卡订单')
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
        $order = Corder::where('id', $id)->with('card')->first();

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.corder.index'))->withInput();
            }
        }

        return $content
            ->header('详情')
            ->description('会员订单详情')
            ->row(function (Row $row) use ($order, $id) {
                $tableData = [
                    ['订单ID', data_get($order, 'id')],
                    ['订单编号', data_get($order, 'order_sn')],
                    ['订单状态', data_get(config('global.card.order_status'), data_get($order, 'order_status'))],
                    ['支付状态', data_get(config('global.card.pay_status'), data_get($order, 'pay_status'))],
                    ['总金额', "<strong style='color: red'>" . '¥' . data_get($order, 'total_price') . "</strong>"],
                    ['提交时间', date('Y-m-d H:i:s', data_get($order, 'submit_time'))],
                    ['用户名', data_get($order, 'username')],
                    ['联系电话', data_get($order, 'mobile')],
                    ['会员卡', data_get(data_get($order, 'card'), 'name')],
                    ['会员卡图片', "<img style='width:100px;height: auto;padding: 4px;line-height: 1.42857143;background-color: #fff;border: 1px solid #ddd;border-radius: 4px;' src='" .url('uploads/'.data_get(data_get($order, 'card'),'img_url')). "'>"],
//                    ['联系电话', data_get($order, 'mobile')],
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
        $grid = new Grid(new Corder);
        $tab = \request('tab');
        if ($tab) {
            if ($tab == 'all') {
                $grid->model();
            } else if ($tab == 2) {
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
        $grid->order_sn('订单编号');
        $grid->column('order_status', '订单状态')->sortable()->display(function ($value) {
            return "<span class='label label-info'>" . data_get(config('global.level.order_status'), $value) . "</span>";
        });
        $grid->column('pay_status', '支付状态')->sortable()->payStatus('pay_status');
        $grid->total_price('总价')->setAttributes(['style' => 'color:red;']);
        $grid->column('card.name','会员卡');
        $grid->username('用户名称');
        $grid->mobile('手机号码');
        $grid->column('submit_time', '提交时间')->sortable()->display(function ($value) {
            return date('Y-m-d H:i:s', $value);
        });
        $grid->created_at('创建时间')->sortable();
        $grid->updated_at('更新时间')->sortable();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $id = $actions->row->id;

            $url = route('admin.corder.show', $id);
            $actions->append('<a class="btn btn-default btn-xs" href="' . $url . '"><i class="fa fa-globe"></i> 查看详情</a>&nbsp;&nbsp;');

        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new CorderTab());
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
        $show = new Show(Corder::findOrFail($id));

        $show->id('Id');
        $show->store_id('Store id');
        $show->order_sn('Order sn');
        $show->order_status('Order status');
        $show->pay_status('Pay status');
        $show->pay_sn('Pay sn');
        $show->total_price('Total price');
        $show->user_id('User id');
        $show->pay_time('Pay time');
        $show->submit_time('Submit time');
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
        $form = new Form(new Corder);

        $form->number('store_id', 'Store id');
        $form->text('order_sn', 'Order sn');
        $form->switch('order_status', 'Order status');
        $form->switch('pay_status', 'Pay status');
        $form->text('pay_sn', 'Pay sn');
        $form->decimal('total_price', 'Total price')->default(0.00);
        $form->number('user_id', 'User id');
        $form->number('pay_time', 'Pay time');
        $form->number('submit_time', 'Submit time');

        return $form;
    }
}
