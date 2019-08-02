<?php

namespace App\Admin\Controllers;

use App\Models\Merchant;
use App\Http\Controllers\Controller;
use App\Models\MerchantCategory;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MerchantController extends Controller
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
            ->description('商家入驻管理')
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
            ->description('商家入驻管理')
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
            ->description('商家入驻管理')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Merchant);

        $grid->id('Id');
        $grid->column('category.title','所属分类');
        $grid->name('商家名称');
        $grid->cover_url('封面图')->gallery(['width' => 100, 'height' => 100]);
        $grid->province('省');
        $grid->city('市');
        $grid->district('区/县');
        $grid->address('详细地址');
        $grid->updated_at('更新时间');
        $grid->created_at('创建时间');

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
        $show = new Show(Merchant::findOrFail($id));

        $show->id('Id');
        $show->category_id('Category id');
        $show->name('Name');
        $show->cover_url('Cover url');
        $show->province('Province');
        $show->city('City');
        $show->district('District');
        $show->address('Address');
        $show->lat('Lat');
        $show->lng('Lng');
        $show->updated_at('Updated at');
        $show->created_at('Created at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Merchant);

//        $form->number('category_id', 'Category id');
        $form->text('name', '商家名称')->rules('required',[
            'required' => '商家名称是必须的'
        ]);
        $form->image('cover_url', '封面图')->rules('required',[
            'required' => '请选择封面图'
        ]);
        $form->number('grade','评分')->min(1)->max(5);
        $form->distpicker(['province', 'city', 'district']);
        $form->text('address', '详细地址');
        $form->map('lat', 'lng', '选择地址');
        $form->select('category_id','选择分类')->options(MerchantCategory::where('is_show',1)->pluck('title','id'))->rules('required',[
            'required' => '请选择分类'
        ]);

        $form->editor('description','描述')->rules('required',[
            'required' => '请填写商家描述'
        ]);

        return $form;

    }
}
