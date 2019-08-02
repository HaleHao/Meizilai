<?php

namespace App\Admin\Controllers;

use App\Models\CompanyLog;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CompanyLogController extends Controller
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
            ->description('公司收益')
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
            ->header('Detail')
            ->description('description')
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
        $grid = new Grid(new CompanyLog);

        $grid->id('Id');
        $grid->column('company.company_name','公司名称');
        $grid->column('store.name','店铺名称');
        $grid->earnings_amount('收益金额');
        $grid->withdraw_type('提现状态');
        $grid->order_amount('订单金额');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableRowSelector();

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
        $show = new Show(CompanyLog::findOrFail($id));

        $show->id('Id');
        $show->company_id('Company id');
        $show->store_id('Store id');
        $show->beautician_id('Beautician id');
        $show->user_id('User id');
        $show->order_id('Order id');
        $show->earnings_amount('Earnings amount');
        $show->withdraw_amount('Withdraw amount');
        $show->withdraw_type('Withdraw type');
        $show->order_amount('Order amount');
        $show->add_time('Add time');
        $show->add_date('Add date');
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
        $form = new Form(new CompanyLog);

        $form->number('company_id', 'Company id');
        $form->number('store_id', 'Store id');
        $form->number('beautician_id', 'Beautician id');
        $form->number('user_id', 'User id');
        $form->number('order_id', 'Order id');
        $form->decimal('earnings_amount', 'Earnings amount');
        $form->decimal('withdraw_amount', 'Withdraw amount');
        $form->switch('withdraw_type', 'Withdraw type');
        $form->decimal('order_amount', 'Order amount');
        $form->number('add_time', 'Add time');
        $form->datetime('add_date', 'Add date')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
