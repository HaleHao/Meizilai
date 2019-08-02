<?php

namespace App\Admin\Controllers;

use App\Models\Ad;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class AdController extends Controller
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
            ->description('首页广告')
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
            ->description('首页广告')
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
            ->description('首页广告')
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
            ->description('首页广告')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Ad);

        $grid->id('Id');
        $grid->img_url('广告图片')->gallery(['width' => 100, 'height' => 100]);
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $grid->is_show('是否显示')->switch($states);
        $grid->sort('排序');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableExport();
        $grid->disableRowSelector();
//        $grid->disableTools();
        $grid->actions(function (Grid\Displayers\Actions $actions){
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
        $show = new Show(Ad::findOrFail($id));

        $show->id('Id');
        $show->img_url('Img url');
        $show->is_show('Is show');
        $show->sort('Sort');
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
        $form = new Form(new Ad);

        $form->image('img_url', '广告图片')->rules('required',[
            'required' => '请选择广告图片'
        ]);
        $states = [
            'on' => ['value'=> 1,'text' => '显示','color' =>'success'],
            'off' => ['value'=> 0,'text' => '隐藏','color' =>'danger'],
        ];
        $form->switch('is_show', '是否显示')->states($states)->default(1);
        $form->number('sort', '排序');

        return $form;
    }
}
