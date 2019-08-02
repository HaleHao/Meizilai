<?php

namespace App\Admin\Controllers;

use App\Models\Question;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class QuestionController extends Controller
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
            ->description('百问百答列表')
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
            ->description('百问百答')
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
            ->description('百问百答新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Question);
        $grid->id('ID')->sortable();
        $grid->title('标题');
        $grid->cover_url('封面图')->gallery(['width' => 100, 'height' => 100]);
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();
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
        $show = new Show(Question::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Question);

        $form->text('title','标题')->rules('required|max:30',[
            'required' => '请填写标题',
            'max' => '长度不能超过30'
        ]);
        $form->image('cover_url','封面')->rules('required',[
            'required' => '请选择封面'
        ]);
        $form->number('sort','排序');
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('is_show','是否显示')->states($states)->default(1);
        $form->editor('description','描述')->rules('required',[
            'required' => '请填写描述'
        ]);
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
        });
        return $form;
    }
}
