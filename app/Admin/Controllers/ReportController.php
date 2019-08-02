<?php

namespace App\Admin\Controllers;

use App\Models\Report;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ReportController extends Controller
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
            ->description('宣传报道列表')
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
            ->description('宣传报道修改')
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
            ->description('宣传报道新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Report);

        $grid->id('ID');
        $grid->title('标题');
//        $grid->description('描述');
        $states = [
          'on' => ['value' => 1 , 'text' => '显示' , 'color' => 'success'],
          'off' => ['value' => 0 , 'text' => '隐藏' , 'color' => 'danger']
        ];
        $grid->is_show('是否显示')->switch($states);
        $grid->sort('排序');
        $grid->cover_url('封面')->gallery(['width' => 100, 'height' => 100]);
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableFilter();
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
        $show = new Show(Report::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->description('Description');
        $show->is_show('Is show');
        $show->sort('Sort');
        $show->cover_url('Cover url');
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
        $form = new Form(new Report);

        $form->text('title', '标题')->rules('required|max:30',[
            'required' => '请填写标题',
            'max' => '长度不能超过30个字符'
        ]);
        $form->image('cover_url', '封面图片')->rules('required',[
            'required' => '请选择封面图片'
        ]);
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('is_show', '是否显示')->states($states)->default(1);
        $form->number('sort', '排序');
        $form->editor('description', '描述')->rules('required',[
            'required' => '请填写描述'
        ]);
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
        });
        return $form;
    }
}
