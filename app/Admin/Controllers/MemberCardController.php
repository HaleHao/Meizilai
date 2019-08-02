<?php

namespace App\Admin\Controllers;

use App\Models\MemberCard;
use App\Http\Controllers\Controller;
use App\Models\MemberLevel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MemberCardController extends Controller
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
            ->description('会员卡列表')
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
            ->description('会员卡修改')
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
            ->description('会员卡新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new MemberCard);

        $grid->id('ID')->sortable();
        $grid->name('名称')->editable();
        $grid->img_url('图片')->gallery(['width' => 100, 'height' => 100]);
//        $grid->keyword('关键词');
        $grid->mall_price('商城价')->sortable()->setAttributes(['style' => 'color:red;']);
        $grid->market_price('市场价');
        $grid->column('level.name','所属会员等级');
        $grid->sell_num('出售数量');
        $states = [
            'on' => ['value' => 1 , 'text' => '显示' ,'color'=>'success'],
            'off' => ['value' => 1 , 'text' => '隐藏' ,'color'=>'danger']
        ];
        $grid->is_show('是否显示')->switch($states);
        $grid->sort('排序')->sortable();
//        $grid->description('Description');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableFilter();
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->disableCreateButton();

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
        $show = new Show(MemberCard::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->img_url('Img url');
        $show->keyword('Keyword');
        $show->mall_price('Mall price');
        $show->market_price('Market price');
        $show->level_id('Level id');
        $show->sell_num('Sell num');
        $show->is_show('Is show');
        $show->sort('Sort');
        $show->description('Description');
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
        $form = new Form(new MemberCard);

        $form->text('name', '会员卡名称')->rules('required',[
            'required' => '请填写会员卡名称'
        ]);
        $form->image('img_url', '图片')->rules('required',[
            'required' => '请选择图片'
        ]);
        $form->text('keyword', '关键词');
        $form->decimal('mall_price', '商城价');
        $form->decimal('market_price', '市场价');
        $form->number('use_num','使用次数');
        $options = new MemberLevel();

        $form->select('level_id', '对应的会员等级')->options($options->levelOptions());
//        $form->number('sell_num', '出售数量');

        $states = [
            'on' => ['value' => 1 , 'text' => '显示' , 'color' => 'success'],
            'off' => ['value' => 0 , 'text' => '隐藏' , 'color' => 'danger']
        ];
        $form->switch('is_show', '是否显示')->states($states)->default(1);

        $form->number('sort', '排序');
        $form->editor('description', '描述');
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
            $tools->disableDelete();
        });
        return $form;
    }
}
