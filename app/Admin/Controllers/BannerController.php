<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class BannerController extends Controller
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
            ->description('轮播图列表')
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
            ->description('轮播图修改')
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
            ->description('轮播图新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner);
        $grid->id('ID')->sortable();
        $grid->img_url('图片')->gallery(['width' => 100, 'height' => 100]);
        $grid->name('名称')->editable();
        $grid->url('URL地址')->editable();
        $states = [
            'on' => ['value' => 1,'text' => '显示','color' => 'success'],
            'off' => ['value' => 0,'text' => '隐藏' , 'color' => 'danger']
        ];
        $grid->is_show('是否显示')->switch($states);

        $grid->sort('排序')->editable()->sortable();

        $grid->column('created_at','创建时间');
        $grid->column('updated_at','更新时间');
//        $grid
        $grid->actions(function(Grid\Displayers\Actions $actions){
            $actions->disableView();
        });
        $grid->disableExport();
        $grid->disableFilter();
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
        $show = new Show(Banner::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Banner);
        $form->text('name','名称')->rules('required|max:30',[
            'required'=>'请填写名称',
            'max' => '不能超过30个字符'
        ])->placeholder('请填写图片的名称');
        $form->image('img_url','图片')->rules('required',[
            'required' => '请上传图片'
        ]);
        $form->url('url','跳转地址')->placeholder('点击图片跳转的地址');
        $states = [
            'on' => ['value' => 1,'text' => '显示','color' => 'success'],
            'off' => ['value' => 0,'text' => '隐藏' , 'color' => 'danger']
        ];
        $form->switch('is_show','是否显示')->states($states)->default(1);
        $form->number('sort','排序');
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        return $form;
    }




}
