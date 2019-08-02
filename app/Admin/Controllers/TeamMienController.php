<?php

namespace App\Admin\Controllers;

use App\Models\OperationManual;
use App\Http\Controllers\Controller;
use App\Models\TeamMien;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class TeamMienController extends Controller
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
        $operation = TeamMien::first();
        if ($operation) {
            $id = $operation->id;
            return $content
                ->header('团队风采')
                ->description('团队风采')
                ->body($this->form()->edit($id));
        }
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->form());
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
            ->description('操作引导修改')
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
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OperationManual);

        $grid->id('Id');
        $grid->name('Name');
        $grid->description('Description');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

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
        $show = new Show(OperationManual::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
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
        $form = new Form(new TeamMien());
        $form->setAction('mien/1');
        $form->text('name', '名称');
        $form->editor('description', '团队介绍');
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
            $tools->disableDelete();
            $tools->disableList();
        });
        $form->disableReset();
        $form->saved(function (){
            admin_toastr('保存成功','success');
           return redirect('admin/mien');
        });
        return $form;
    }
}
