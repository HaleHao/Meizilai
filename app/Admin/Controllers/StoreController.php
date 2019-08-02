<?php

namespace App\Admin\Controllers;

use App\Models\Store;
use App\Http\Controllers\Controller;
use App\Models\Users;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StoreController extends Controller
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
            ->description('店铺列表')
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
            ->description('店铺列表')
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
            ->description('店铺新增')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Store);

        $grid->id('ID');
        $grid->name('店铺名称');
        $grid->title('店铺标题');
        $grid->cover_url('封面图片')->gallery(['width' => 100, 'height' => 100]);
//        $grid->description('Description');
//        $grid->lng('Lng');
//        $grid->lat('Lat');
        $grid->province('省');
//        $grid->province_code('Province code');
        $grid->city('市');
//        $grid->city_code('City code');
        $grid->district('区/县');
//        $grid->region_code('Region code');
        $grid->address('详细地址');
        $grid->url('跳转地址')->editable();
        $grid->qrcode_path('店铺二维码')->gallery(['width' => 100, 'height' => 100]);
        $grid->updated_at('更新时间');

        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableFilter();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $id = $actions->row->id;
            $user = Users::where('store_id', $id)->where('is_storekeeper', 1)->where('user_type', 4)->first();
            if (!$user) {
                $url = route('admin.store.bing', $id);
                $icon = 'fa-send-o';
                $text = '绑定店主';
                $color = 'success';
                $users = Users::where('store_id', $id)->get();
                $actions->append(view('admin.actions.store', compact('id', 'url', 'icon', 'text', 'users', 'color')));
            }
        });
        return $grid;
    }


    public function bing($id, Request $request)
    {
        $user_id = $request->input('user_id');
        $user = Users::where('id', $user_id)->first();

        if (!$id){
            admin_toastr('参数获取失败','error');
            return back()->withInput();
        }

        if (!$user) {
            admin_toastr('用户获取失败', 'error');
            return back()->withInput();
        }

        if ($user->store_id != $id){
            admin_toastr('该用户不属于该店铺','error');
            return back()->withInput();
        }

        if (!Admin::user()->isAdministrator()) {
            $store_id = Admin::user()->store_id;
            if ($user->store_id != $store_id) {
                admin_toastr('您不是该店铺的管理员', 'error');
                return redirect(route('admin.gorder.index'))->withInput();
            }
        }

        $user->user_type = 4;
        $user->is_storekeeper = 1;
//        Users::where('store_id',)
        if (!$user->password){
            $user->password = Admin::user()->password;
        }
        $result = $user->save();
        if ($result) {
            admin_toastr('绑定店主成功,店主为' .$user->nickname, 'success');
            return back()->withInput();
        }
        admin_toastr('绑定店主失败', 'error');
        return back()->withInput();
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Store::findOrFail($id));

        $show->id('ID')->sortable();
        $show->name('店铺名称');
        $show->title('店铺标题');
        $show->cover_url('封面图片')->gallery(['width' => 100, 'height' => 100]);
//        $show->lng('Lng');
//        $show->lat('Lat');
        $show->province('省');

        $show->city('市');
        $show->district('区/县');
        $show->address('详细地址');
        $show->qrcode_path('店铺二维码')->gallery(['width' => 100, 'height' => 100]);
        $show->created_at('创建时间');
        $show->updated_at('更新时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Store);
        $form->tab('基本信息', function ($form) {
            $form->text('name', '店铺名称')->rules('required',[
                'required' => '请填写店铺名称'
            ]);
            $form->text('title', '店铺标题')->rules('required',[
                'required' => '请填写店铺标题'
            ]);
            $form->editor('description', '店铺描述')->rules('required',[
                'required' => '请填写店铺描述'
            ]);
            $form->image('cover_url', '店铺封面')->rules('required',[
                'required' => '请选择店铺封面'
            ]);
            //        $form->text('lng', '纬度');
            //        $form->text('lat', '经度');
            $form->distpicker(['province', 'city', 'district'])->rules('required',[
                'required' => '请填写地址'
            ]);
            $form->text('address', '详细地址')->rules('required',[
                'required' => '请填写详细地址'
            ]);
            $form->map('lat', 'lng', '选择地址')->rules('required',[
                'required' => '请选择地址'
            ]);


        })->tab('店铺风采', function ($form) {
            $form->hasMany('images', '店铺相册', function (Form\NestedForm $form) {
                $form->image('img_url', '图片');
            });
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        $form->saved(function (Form $form) {
            $store = $form->model();
//            dd($store);
            if (!$store->qrcode_path) {
                $url = config('APP_URL', 'https://gzchenyu.cn/') . 'shopDetail?id=' . $store->id;
                $store->qrcode_path = $this->generateQrcode($url);
                $store->url = config('AD_URL', 'https://gzchenyu.cn/') . '?store_id=' . $store->id;
                $store->save();
            }
        });
        return $form;
    }

    protected function generateQrcode($url)
    {
        if (!file_exists(public_path('uploads/qrcodes'))) {
            mkdir(public_path('uploads/qrcodes'), 0777, true);
        }
        $name = date('YmdHis_') . str_random(8);
        $qrcode_name = 'uploads/qrcodes/' . $name . '.png';
        $url_name = 'qrcodes/' . $name . '.png';
        QrCode::format('png')->encoding('UTF-8')->margin(1)->size(500)->generate($url, public_path($qrcode_name));
        return $url_name;
    }
}
