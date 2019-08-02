<?php

namespace App\Admin\Controllers;

use App\Models\Store;
use App\Models\StoreInfo;
use App\Http\Controllers\Controller;
use App\Models\Users;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StoreInfoController extends Controller
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
            ->description('店铺信息')
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
            ->description('店铺信息')
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
            ->description('店铺信息')
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
            ->description('店铺信息')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StoreInfo);
        if (!Admin::user()->isAdministrator()){
            $store_id = Admin::user()->store_id;
            $grid->model()->where('store_id',$store_id);
        }
        $grid->id('Id');
        $grid->column('store.name','店铺名称');
        $grid->wx_appid('微信APPID');
        $grid->wx_secret('微信Secret');
        $grid->mch_id('商户号');
        $grid->carriage('运费');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        //判断是否超级管理员
        if (!Admin::user()->isAdministrator()) {
            $grid->disableCreateButton();
        }
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableView();

//            $actions->
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
        $show = new Show(StoreInfo::findOrFail($id));

        $show->id('Id');
        $show->store_id('Store id');
        $show->wx_appid('Wx appid');
        $show->wx_secret('Wx secret');
        $show->redirect_url('Redirect url');
        $show->mch_id('Mch id');
        $show->carriage('Carriage');
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
        $form = new Form(new StoreInfo);
        if (Admin::user()->isAdministrator()) {
            $form->select('store_id', '店铺')->options(Store::all()->pluck('name', 'id'));
        }
        $form->text('wx_appid', '微信APPID')->rules('required',[
            'required' => '请填写微信APPID',
        ]);
        $form->text('wx_secret', '微信secret')->rules('required',[
            'required' => '请填写微信secret',
        ]);
        $form->text('redirect_url', '微信回调地址')->rules('required',[
            'required' => '请填写微信回调地址',
        ]);
        $form->text('mch_id', '微信商户号')->rules('required',[
            'required' => '请填写微信商户号',
        ]);
        $form->text('mch_secret', '微信商户密钥')->rules('required',[
            'required' => '请填写微信商户号',
        ]);

        $form->text('template_id', '微信消息模板ID');

        $form->decimal('carriage', '商品运费')->default(0.00);

        $form->file('apiclient_cert', 'apiclient_cert证书')->rules('required',[
            'required' => '请上传微信apiclient_cert证书',
        ]);
        $form->file('apiclient_key', 'apiclient_key证书')->rules('required',[
            'required' => '请上传微信apiclient_key证书',
        ]);



        return $form;
    }
}
