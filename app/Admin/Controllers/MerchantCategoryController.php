<?php

namespace App\Admin\Controllers;

use App\Models\MerchantCategory;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Tree;

class MerchantCategoryController extends Controller
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
            ->description('商家入驻分类')
            ->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());
                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action(admin_base_path('merchant/category'));
                    $form->text('title', trans('admin.title'))->rules('required');
                    $form->number('order','排序');
                    $states = [
                        'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
                    ];
                    $form->switch('is_show','是否显示')->states($states)->default(1);

                    $form->tools(function (Form\Tools $tools) {
                        $tools->disableView();
                    });
                    $form->hidden('_token')->default(csrf_token());
                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
                });
            });
    }


    protected function treeView()
    {
        $menuModel = new MerchantCategory();

        return $menuModel::tree(function (Tree $tree) {
            $tree->disableCreate();

            $tree->branch(function ($branch) {
                $payload = "&nbsp;<strong>{$branch['title']}</strong>";
                if ($branch['is_show'] == 1){
                    $payload .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='label label-success'>显示</span>";
                }else{
                    $payload .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='label label-danger'>隐藏</span>";
                }
//                if (!isset($branch['children'])) {
//                    if (url()->isValidUrl($branch['uri'])) {
//                        $uri = $branch['uri'];
//                    } else {
//                        $uri = admin_base_path($branch['uri']);
//                    }
//
//                    $payload .= "&nbsp;&nbsp;&nbsp;<a href=\"$uri\" class=\"dd-nodrag\">$uri</a>";
//                }

                return $payload;
            });
        });
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
        $grid = new Grid(new MerchantCategory);



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
        $show = new Show(MerchantCategory::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MerchantCategory);


        $form->display('id', 'ID');

        $form->text('title', trans('admin.title'))->rules('required');
        $form->number('order','排序');

        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('is_show','是否显示')->states($states)->default(1);

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        return $form;
    }
}
