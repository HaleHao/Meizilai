<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Goods;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class GoodsController extends Controller
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
            ->description('商品列表')
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
            ->description('修改商品')
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
            ->description('新增商品')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Goods);
        $grid->model()->where('goods_type',0);
        $grid->id('ID')->sortable();
        $grid->name('商品名称');
        $grid->column('category.title','所在分类');
        $grid->mall_price('商城价')->sortable()->setAttributes(['style' => 'color:red;']);
        $grid->market_price('市场价')->sortable();
        $grid->cover_url('封面')->gallery(['width' => 100, 'height' => 100]);
        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $grid->is_put('是否上架')->switch($states);
        $grid->is_hot('是否热卖')->switch($states);
        $grid->is_buy('是否出售')->switch($states);
        $grid->is_new('是否新品')->switch($states);
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
        $show = new Show(Goods::findOrFail($id));

        $show->id('ID');
        $show->name('Name');
        $show->title('Title');
        $show->description('Description');
        $show->is_hot('Is hot');
        $show->price('Price');
        $show->real_price('Real price');
        $show->is_buy('Is buy');
        $show->cover_url('Cover url');
        $show->is_put('Is put');
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
        $form = new Form(new Goods);
//        $form->tab()
        $form->tab('基本信息', function ($form) {

            $form->text('name', '商品名称')->rules('required|max:50',[
                'required' => '请填写商品名称',
                'max' => '长度不能超过50个字符'
            ]);

            $form->text('title', '商品标题')->rules('required|max:50',[
                'required' => '请填写商品标题',
                'max' => '长度不能超过50个字符'
            ]);

            $form->editor('description', '商品描述')->rules('required',[
                'required' => '请填写商品描述'
            ]);

            $is_hot = [
                'on'  => ['value' => 1, 'text' => '热卖', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '正常', 'color' => 'danger'],
            ];

            $form->switch('is_hot', '是否热销')->states($is_hot)->default(1);

            $form->currency('mall_price', '商城价')->symbol('￥')->rules('required',[
                'required' => '请填写商城价'
            ]);

            $form->currency('market_price', '市场价')->symbol('￥')->rules('required',[
                'required' => '请填写市场价'
            ]);

            $is_buy = [
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
            ];
            $form->select('category_id','商品分类')->options(
                Category::orderBy('order','desc')->pluck('title','id')
            )->rules('required',[
                'required' => '请选择商品分类'
            ]);

            $form->switch('is_buy', '是否出售')->states($is_buy)->default(1);

            $form->image('cover_url', '封面图片')->rules('required',[
                'required' => '请选择封面图片'
            ]);

            $form->switch('is_put', '是否上架')->states($is_buy)->default(1);

            $form->switch('is_new','是否新品')->states($is_buy)->default(1);
            $form->number('sort','排序');

            $form->number('inventory','库存');

        })->tab('轮播图',function ($form){
            $form->hasMany('images', '相册',function (Form\NestedForm $form) {
                $form->image('img_url','图片');
            });
        });
//        $form->tools()
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        return $form;
    }
}
