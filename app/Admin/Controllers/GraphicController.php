<?php

namespace App\Admin\Controllers;

use App\Models\Graphic;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class GraphicController extends Controller
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
            ->description('图文专栏列表')
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
            ->header('修改')
            ->description('图文专栏修改')
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
            ->description('图文专栏新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Graphic);

        $grid->id('ID')->sortable();
        $grid->title('标题');
        $grid->cover_url('封面图')->gallery(['width' => 100, 'height' => 100]);
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->disableRowSelector();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->actions(function(Grid\Displayers\Actions $actions){
           $actions->disableView();
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
        $show = new Show(Graphic::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Graphic);

        $form->text('title','标题')->rules('required|max:30',[
            'required' => '请填写标题',
            'max' => '长度不能超过30个字符'
        ]);
        $form->image('cover_url','封面图片')->rules('required',[
            'required' => '请选择封面图片'
        ]);
        $form->number('sort','排序');
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('is_show','是否显示')->states($states)->default(1);
        $form->editor('description','描述内容')->rules('required',[
            'required' => '请填写描述内容'
        ]);
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
        });
        return $form;
    }
}
