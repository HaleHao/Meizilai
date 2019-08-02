<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;



class CategoryController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('列表')
            ->description('商品分类列表')
            ->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());

                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action(admin_base_path('category'));
                    $form->text('title', trans('admin.title'))->rules('required');
                    $form->number('order','排序');
                    $form->text('description','描述');

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

    /**
     * Redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('category', ['id' => $id]);
    }

    /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        $menuModel = new Category();

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
     * Edit interface.
     *
     * @param string  $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('admin.menu'))
            ->description(trans('admin.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {


        $form = new Form(new Category());

        $form->display('id', 'ID');

        $form->text('title', trans('admin.title'))->rules('required',[
            'required' => '请填写标题名称'
        ]);
        $form->number('order','排序');
        $form->text('description','描述');

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

    /**
     * Help message for icon field.
     *
     * @return string
     */
    protected function iconHelp()
    {
        return 'For more icons please see <a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a>';
    }
}
