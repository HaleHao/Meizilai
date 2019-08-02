<?php

namespace App\Admin\Controllers;

use App\Models\CompanyIntroduction;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CompanyIntroductionController extends Controller
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
        $id = CompanyIntroduction::first()->id;
        if ($id){
        return $content
            ->header('公司介绍')
            ->description('公司介绍')
            ->body($this->form()->edit($id));
        }
        return $content
            ->header('公司介绍')
            ->description('公司介绍')
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
            ->description('公司介绍修改')
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
        $grid = new Grid(new CompanyIntroduction);

        $grid->id('Id');
        $grid->company_name('Company name');
        $grid->company_description('Company description');
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
        $show = new Show(CompanyIntroduction::findOrFail($id));

        $show->id('Id');
        $show->company_name('Company name');
        $show->company_description('Company description');
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
        $form = new Form(new CompanyIntroduction);
        $form->setAction('/admin/introduction/1');
        $form->text('company_name', '公司名称')->rules('required|max:50',[
            'required' => '请填写公司名称',
            'max' => '字符长度不能超过50'
        ]);
        $form->editor('company_description', '公司介绍')->rules('required',[
            'required' => '请填写公司介绍'
        ]);
        $form->tools(function (Form\Tools $tools){
            $tools->disableView();
            $tools->disableDelete();
            $tools->disableList();
        });

        $form->saved(function (){
            admin_toastr('保存成功','success');
            return redirect('admin/introduction');
        });

        $form->disableReset();
        return $form;
    }


}
