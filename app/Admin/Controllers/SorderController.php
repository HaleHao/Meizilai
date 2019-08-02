<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\GorderTab;
use App\Admin\Extensions\Tools\SorderTab;
use App\Models\OrderServe;
use App\Models\Sorder;
use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Service\WeekdayService;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;

class SorderController extends Controller
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
            ->description('服务订单')
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
        $order = Sorder::where('id', $id)->with('store', 'beautician')->first();

        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            if ($order->store_id != $store_id){
                admin_toastr('您不是该店铺的管理员','error');
                return redirect(route('admin.corder.index'))->withInput();
            }
        }

        return $content
            ->header('详情')
            ->description('服务订单')
            ->row(function (Row $row) use ($order, $id) {
                $tableData = [
                    ['订单ID', data_get($order, 'id')],
                    ['订单编号', data_get($order, 'order_sn')],
                    ['订单状态', data_get(config('global.serve.order_status'), data_get($order, 'order_status'))],
                    ['支付状态', data_get(config('global.serve.pay_status'), data_get($order, 'pay_status'))],
                    ['总金额', "<strong style='color: red'>" . '¥' . data_get($order, 'total_price') . "</strong>"],
                    ['提交时间', date('Y-m-d H:i:s', data_get($order, 'submit_time'))],
                    ['服务时间', self::weekday(data_get($order, 'serve_time')) . ' ' . date('m月d日 H:i', data_get($order, 'serve_time'))],
                    ['客户姓名', data_get($order, 'username')],
                    ['联系电话', data_get($order, 'mobile')],
                    ['美容师', data_get(data_get($order, 'beautician'), 'username')],
                    ['服务店铺', data_get(data_get($order, 'store'), 'name')]
                ];
                $table = new Table([], $tableData);
                $row->column(12, new Box('订单详情', $table));
            })->row(function (Row $row) use ($order, $id) {
//                $goods_ids =
                $serve = OrderServe::where('order_id',$id)->get();
                $header = [
                    '服务ID',
                    '服务项目',
                    '服务图片',
                    '服务价格',
                ];
                $tableData = [];
                foreach ($serve as $val){
                    $tableData[] = [
                        data_get(data_get($val,'serve'),'id'),
                        data_get(data_get($val,'serve'),'name'),
                        "<img style='width:100px;height: auto;padding: 4px;line-height: 1.42857143;background-color: #fff;border: 1px solid #ddd;border-radius: 4px;' src='".url('uploads/'.data_get(data_get($val,'serve'),'cover_url'))."'>",
                        data_get(data_get($val,'serve'),'price'),
                    ];
                }
                $table = new Table($header,$tableData);
                $row->column(6, new Box('服务信息', $table));


                $beautician = Users::where('id',$order->beautician_id)->first();
                $tableData = [
                    ['美容师ID',data_get($beautician,'id')],
                    ['美容师名称',data_get($beautician,'username')],
                    ['美容师头像',"<img style='width:100px;height: auto;padding: 4px;line-height: 1.42857143;background-color: #fff;border: 1px solid #ddd;border-radius: 4px;' src='".data_get($beautician,'avatar')."'>"],
                    ['评分',data_get($beautician,'grade')],
                    ['服务次数',data_get($beautician,'serve_num')],
                ];
                $table = new Table([],$tableData);
                $row->column(6, new Box('美容师信息', $table));

            })->row(function (Row $row) use ($order, $id) {

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
            ->header('修改')
            ->description('服务订单')
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
            ->header('新增')
            ->description('服务订单')
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
        $grid = new Grid(new Sorder);
        if ($tab) {
            if ($tab == 'all') {
                $grid->model();
            } else if ($tab == 8) {
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
        $grid->order_sn('订单编号')->sortable();
        $grid->column('beautician.username', '美容师');
        $grid->column('order_status', '订单状态')->sortable()->display(function ($value) {
            return "<span class='label label-info'>" . data_get(config('global.serve.order_status'), $value) . "</span>";
        });
        $grid->column('pay_status', '支付状态')->sortable()->payStatus('pay_status');
        $grid->total_price('总价')->sortable()->setAttributes(['style' => 'color:red;']);
        $grid->column('user.username', '用户名');
        $grid->mobile('联系电话');
        $grid->column('serve_time', '服务时间')->sortable()->display(function ($value) {
//            $res = new WeekdayService($value);
            $weekday = self::weekday($value);
            return $weekday . ' ' . date('m月d日 H:i', $value);
        });
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $id = $actions->row->id;
            $url = route('admin.sorder.show',$id);
            $actions->append('<a class="btn btn-default btn-xs" href="' . $url . '"><i class="fa fa-globe"></i> 查看详情</a>&nbsp;&nbsp;');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new SorderTab());
        });


        $grid->disableCreateButton();
        return $grid;
    }


    static function weekday($time)
    {
        if (is_numeric($time)) {
            $weekday = array('周日', '周一', '周二', '周三', '周四', '周五', '周六');
            return $weekday[date('w', $time)];
        }
        return false;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Sorder::findOrFail($id));

        $show->id('Id');
        $show->order_sn('Order sn');
        $show->store_id('Store id');
        $show->beautician_id('Beautician id');
        $show->order_status('Order status');
        $show->pay_status('Pay status');
        $show->pay_sn('Pay sn');
        $show->total_price('Total price');
        $show->user_id('User id');
        $show->pay_time('Pay time');
        $show->username('Username');
        $show->mobile('Mobile');
        $show->remark('Remark');
        $show->user_delete('User delete');
        $show->apply_time('Apply time');
        $show->submit_time('Submit time');
        $show->serve_time('Serve time');
        $show->cancel_time('Cancel time');
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
        $form = new Form(new Sorder);

        $form->text('order_sn', 'Order sn');
        $form->number('store_id', 'Store id');
        $form->number('beautician_id', 'Beautician id');
        $form->switch('order_status', 'Order status');
        $form->switch('pay_status', 'Pay status');
        $form->text('pay_sn', 'Pay sn');
        $form->decimal('total_price', 'Total price')->default(0.00);
        $form->number('user_id', 'User id');
        $form->number('pay_time', 'Pay time');
        $form->text('username', 'Username');
        $form->mobile('mobile', 'Mobile');
        $form->text('remark', 'Remark');
        $form->switch('user_delete', 'User delete');
        $form->number('apply_time', 'Apply time');
        $form->number('submit_time', 'Submit time');
        $form->number('serve_time', 'Serve time');
        $form->number('cancel_time', 'Cancel time');

        return $form;
    }
}
