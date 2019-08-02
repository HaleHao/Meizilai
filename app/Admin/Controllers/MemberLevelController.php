<?php

namespace App\Admin\Controllers;

use App\Models\MemberCard;
use App\Models\MemberLevel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MemberLevelController extends Controller
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
            ->description('会员等级列表')
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
            ->description('会员等级详情')
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
            ->description('会员等级修改')
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
            ->description('会员等级新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MemberLevel);

        $grid->id('ID')->sortable();
        $grid->name('名称');
        $grid->level('等级');
        $grid->price('价格')->sortable()->setAttributes(['style' => 'color:red;']);
        $grid->column('card.name','对应会员卡');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
            $actions->disableDelete();
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
        $show = new Show(MemberLevel::findOrFail($id));
        $show->id('Id');
        $show->name('Name');
        $show->level('Level');
        $show->use_type('Use type');
        $show->use_num('Use num');
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
        $form = new Form(new MemberLevel);

        $form->text('name', '名称')->rules('required',[
            'required' => '请填写名称'
        ]);
        $form->number('level', '等级');
        $form->currency('price','价格')->symbol('￥');
        $form->select('card_id','对应的会员卡')->options(MemberCard::all()->pluck('name','id'))->rules('required',[
            'required' => '请选择对应的会员卡'
        ]);
        $form->currency('ratio','分销比例')->symbol('');
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
            $tools->disableDelete();
        });
        return $form;
    }
}
