<?php

namespace App\Admin\Controllers;

use App\Models\Event;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class EventController extends Controller
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
            ->description('活动管理')
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
            ->description('活动管理')
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
            ->description('活动管理')
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
            ->description('活动管理')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Event);

        $grid->id('活动ID');
        $grid->title('标题');
        $grid->cover_url('封面')->gallery(['width' => 100, 'height' => 100]);
        $grid->sort('排序');
//        $grid->column('add_time';
//        $grid->content('Content');
        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->is_show('是否显示')->switch($states);
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->disableExport();
//        $grid->dis
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
        $show = new Show(Event::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->cover_url('Cover url');
        $show->add_time('Add time');
        $show->content('Content');
        $show->is_show('Is show');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->sort('Sort');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Event);

        $form->text('title', '标题')->rules('required|max:30',[
            'required' => '请填写标题',
            'max' => '长度不能超过30个字'
        ]);
        $form->image('cover_url', '封面')->rules('required',[
            'required' => '请选择图片封面'
        ]);
//        $form->datetime('event_time', '');
        $form->editor('content', '内容')->rules('required',[
            'required' => '请填写内容描述'
        ]);
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('is_show','是否显示')->states($states)->default(1);

        $form->number('sort', '排序');

        return $form;
    }
}
