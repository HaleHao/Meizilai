<?php

namespace App\Admin\Controllers;

use App\Models\Material;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MaterialController extends Controller
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
            ->description('素材圈列表')
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
            ->description('素材圈修改')
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
            ->description('素材圈新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Material);

        $grid->id('ID')->sortable();
        $grid->title('标题');
        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $grid->is_show('是否显示')->switch($states);
        $grid->sort('排序');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableRowSelector();
        $grid->disableExport();
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
        $show = new Show(Material::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->descprition('description');
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
        $form = new Form(new Material);

        $form->text('title', '标题')->rules('required|max:30',[
            'required' => '请填写标题',
            'max' => '字符长度不能超过30'
        ]);

        $form->editor('description', '描述')->rules('required',[
            'required' => '请填写描述'
        ]);

        $form->number('sort','排序');

        $states = [
            'on'  => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('is_show','是否显示')->states($states)->default(1);

        $form->hasMany('images','图片',function (Form\NestedForm $form){

           $form->image('img_url','图片');

        });
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
        });

        return $form;
    }
}
