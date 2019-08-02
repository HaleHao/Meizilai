<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreServe;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ServeController extends Controller
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
            ->description('店铺服务列表')
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
            ->description('服务修改')
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
            ->description('服务新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreServe);
        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            $grid->model()->where('store_id',$store_id);
        }
        $grid->id('ID')->sortable();
        $grid->name('服务名称');
        $grid->column('store.name','店铺名称');
        $grid->cover_url('图片')->gallery(['width' => 100, 'height' => 100]);

        $grid->price('价格')->sortable();

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
        $form = new Form(new StoreServe());
        $form->text('name','名称')->rules('required|max:30',[
            'required'=>'请填写名称',
            'max' => '不能超过30个字符'
        ])->placeholder('请填写图片的名称');
        $form->image('cover_url','图片')->rules('required',[
            'required' => '请上传图片'
        ]);
        $form->currency('price', '价格')->symbol('￥')->rules('required',[
            'required' => '请填写价格'
        ]);
        $form->editor('description','描述');
        if (Admin::user()->isAdministrator()) {
            $form->select('store_id', '所属店铺')->options(Store::all()->pluck('name', 'id'))->rules('required',[
                'required' => '请选择店铺'
            ]);
        }else{
            $form->display('store_id','店铺ID')->value(Admin::user()->store_id);
        }
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        return $form;
    }




}
