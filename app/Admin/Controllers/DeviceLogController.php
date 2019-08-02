<?php

namespace App\Admin\Controllers;

use App\Models\DeviceLog;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Users;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class DeviceLogController extends Controller
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
            ->description('设备开机记录')
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
        return $content
            ->header('详情')
            ->description('设备开机记录')
            ->body($this->detail($id));
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
        $grid = new Grid(new DeviceLog);

        $grid->id('Id');
        $grid->column('device.device_name', '设备名称');
//        $grid->store_id('Store id');
//        $grid->user_id('User id');
        $grid->content('内容');
        $grid->column('open_type', '开启状态')->display(function ($v) {
            if ($v == 0) {
                return "<span class='label label-danger'>已关闭</span>";
            } else {
                return "<span class='label label-success'>启动中</span>";
            }
        });
        $grid->device_minute('时长（分）');
        $grid->column('open_time', '开启时间')->display(function ($v) {
            return date('Y-m-d H:i:s', $v);
        });
//        $grid->close_time('Close time');
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableExport();
        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();

            $filter->between('updated_at', '时间')->datetime();
//            $filter->
//            $filter->
//            $filter->equal('store.store_name')
            $filter->equal('store_id', '店铺')->select(
                Store::all()->pluck('name', 'id')
            );

            $filter->where(function ($query) {

                $user_arr = Users::where('nickname','like',"%{$this->input}%")->pluck('id');
                $query->whereIn('user_id',  $user_arr);

            }, '用户名');
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
        $show = new Show(DeviceLog::findOrFail($id));

        $show->id('Id');
        $show->device_id('Device id');
        $show->store_id('Store id');
        $show->user_id('User id');
        $show->content('Content');
        $show->open_type('Open type');
        $show->open_time('Open time');
        $show->close_time('Close time');
        $show->device_minute('Device minute');
        $show->send_time('Send time');
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
        $form = new Form(new DeviceLog);

        $form->number('device_id', 'Device id');
        $form->number('store_id', 'Store id');
        $form->number('user_id', 'User id');
        $form->textarea('content', 'Content');
        $form->switch('open_type', 'Open type');
        $form->number('open_time', 'Open time');
        $form->number('close_time', 'Close time');
        $form->number('device_minute', 'Device minute');
        $form->number('send_time', 'Send time');

        return $form;
    }
}
