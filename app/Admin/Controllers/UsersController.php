<?php

namespace App\Admin\Controllers;

use App\Models\MemberCard;
use App\Models\MemberLevel;
use App\Models\Store;
use App\Models\Users;
use App\Http\Controllers\Controller;
use App\Models\WithdrawLog;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
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
            ->description('用户列表')
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
            ->description('用户列表')
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
            ->description('用户列表')
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
            ->description('用户列表')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Users);

        if (!Admin::user()->isAdministrator()) {
            $store_id = Admin::user()->store_id;
            $grid->model()->where('store_id', $store_id);
        }

        $grid->model()->orderBy('id','asc');

        $grid->id('ID')->sortable();

        $grid->openid('Openid');

        $grid->nickname('昵称')->sortable();

        $grid->avatar('头像')->gallery(['width' => 50, 'height' => 50]);

        $grid->column('username', '用户名')->sortable();

        $grid->column('store.name', '所属店铺')->sortable();

        $grid->column('user_type', '用户类型')->userType('user_type')->sortable();

        $grid->column('firstUser.nickname', '上级用户');

        $grid->column('level.name', '会员等级')->sortable();

        $grid->column('card.name', '会员卡')->sortable();

        $grid->qrcode_path('推广二维码')->gallery(['width' => 100, 'height' => 100]);

        $grid->disableCreateButton();
//        $grid->disableFilter();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableView();
            $id = $actions->row->id;
            $user = Users::where('id', $id)->first();
            if ($user->user_type == 2) {
                $url = route('admin.users.beautician', $id);
                $actions->append('<a class="btn btn-info btn-xs" href="' . $url . '"><i class="fa fa-globe"></i> 升级为美容师</a>&nbsp;&nbsp;');
            }

        });

        $grid->tools(function (Grid\Tools $tools){
            $url = route('admin.user.zero');
            $tools->append('<a class="btn btn-info btn-lg" href="' . $url . '"><i class="fa fa-globe"></i>用户收益归零</a>&nbsp;&nbsp;');
        });

        $grid->filter(function (Grid\Filter $filter){
           $filter->disableIdFilter();
           $filter->like('nickname','用户昵称');
           $filter->like('mobile','用户手机号码');
//           $filter->like('')
//            $filter->where(function ($query))
            $filter->equal('store_id','店铺')->select(function (){
                return Store::pluck('name','id');
            });
            $filter->equal('card_id','会员卡')->select(function (){
                return MemberCard::pluck('name','id');
            });
            $filter->equal('level_id','会员等级')->select(function (){
                return MemberLevel::pluck('name','id');
            });
        });

        return $grid;
    }

//    public function storekeeper($id)
//    {
//        if (!$id) {
//            admin_toastr('参数获取失败', 'error');
//            return back()->withInput();
//        }
//        DB::beginTransaction();
//        try {
//            $user = Users::where('id', $id)->lockForUpdate()->first();
//
//            if (!Admin::user()->isAdministrator()) {
//                $store_id = Admin::user()->store_id;
//                if ($user->store_id != $store_id) {
//                    admin_toastr('您不是该店铺的管理员', 'error');
//                    return redirect(route('admin.lorder.index'))->withInput();
//                }
//            }
//            $user->is_storekeeper = 1;
//            $user->user_type = 4;
//            $user->save();
//
//            DB::commit();
//            admin_toastr('设置店主成功', 'success');
//            return back()->withInput();
//
//
//        } catch (\Exception $exception) {
//            DB::rollBack();
//            admin_toastr('设置店主失败', 'error');
//            return back()->withInput();
//        }
//    }

    //TODO 将用户的数据全部归零
    public function zero()
    {
        Users::where('earnings','>',0)->where('id',144)->chunk(100,function ($users){
            foreach ($users as $user){
                $withdraw = new WithdrawLog();
                $withdraw->event_name = '提现';
                $withdraw->store_id = $user->store_id;
                $withdraw->user_id = $user->id;
                $withdraw->add_time = time();
                $withdraw->withdraw_amount = $user->earnings;
                $withdraw->save();
                $user->earnings = 0;
                $user->save();
            }
        });
        admin_toastr('归零成功', 'success');
        return back()->withInput();

    }

    public function beautician($id)
    {
        if (!$id) {
            admin_toastr('参数获取失败', 'error');
            return back()->withInput();
        }
        DB::beginTransaction();
        try {
            $user = Users::where('id', $id)->lockForUpdate()->first();

            if (!Admin::user()->isAdministrator()) {
                $store_id = Admin::user()->store_id;
                if ($user->store_id != $store_id) {
                    admin_toastr('您不是该店铺的管理员', 'error');
                    return redirect(route('admin.lorder.index'))->withInput();
                }
            }
            $user->is_beautician = 1;
            $user->user_type = 3;
            $user->save();

            DB::commit();
            admin_toastr('升级美容师成功', 'success');
            return back()->withInput();


        } catch (\Exception $exception) {
            DB::rollBack();
            admin_toastr('升级美容师失败', 'error');
            return back()->withInput();
        }
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Users::findOrFail($id));

        $show->id('Id');
        $show->openid('Openid');
        $show->nickname('Nickname');
        $show->user_type('User type');
        $show->first_user_id('First user id');
        $show->second_user_id('Second user id');
        $show->store_id('Store id');
        $show->level_id('Level id');
        $show->super_id('Super id');
        $show->is_partner('Is partner');
        $show->card_id('Card id');
        $show->is_storekeeper('Is storekeeper');
        $show->is_beautician('Is beautician');
        $show->grade('Grade');
        $show->serve_num('Serve num');
        $show->avatar('Avatar');
        $show->address_id('Address id');
        $show->card_num('Card num');
        $show->card_type('Card type');
        $show->withdraw('Withdraw');
        $show->earnings('Earnings');
        $show->username('Username');
        $show->real_name('Real name');
        $show->mobile('Mobile');
        $show->qrcode_path('Qrcode path');
        $show->sex('Sex');
        $show->birthday('Birthday');
        $show->reg_time('Reg time');
        $show->last_ip('Last ip');
        $show->last_login('Last login');
        $show->password('Password');
        $show->province('Province');
        $show->city('City');
        $show->country('Country');
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
        $form = new Form(new Users);
//        dd($id);

        $user_id = data_get(request()->route()->parameters(), 'user');
        $user = Users::where('id', $user_id)->first();
        $form->select('first_user_id', '上级用户')->options(Users::where('store_id', $user->store_id)->pluck('nickname', 'id'))->rules('required',[
            'required' => '请选择上级用户'
        ]);
        $form->select('level_id', '会员等级')->options(MemberLevel::all()->pluck('name', 'id'))->rules('required',[
            'required' => '请选择会员等级'
        ]);
        $form->select('card_id', '会员卡')->options(MemberCard::all()->pluck('name', 'id'))->rules('required',[
            'required' => '请选择会员卡'
        ]);
        $form->text('username', '用户名称')->rules('required|max:10',[
            'required' => '请填写用户名称',
            'max' => '长度不能超过10个字符'
        ]);
        $form->mobile('mobile', '手机号码')->rules('required|max:11',[
            'required' => '请填写手机号码',
            'max' => '长度不能超过11个字符'
        ]);
        $form->select('user_type', '用户类型')->options(config('global.user_type'))->rules('required',[
            'required' => '请选择用户类型'
        ]);
        $form->number('card_num','会员卡使用次数');
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableDelete();
        });
        $form->saving(function (Form $form) {
            $user = $form->model();
            $card_id = $form->card_id;
            $user_card_id = $user->card_id;
            //判断用户是否更换了会员卡
            if ($card_id != $user_card_id) {
                $card = MemberCard::where('id', $card_id)->first();
                $user->card_num = $card->use_num;
                $user->card_type = $card->use_type;

            }
            $user_type = $form->user_type;
            if ($user_type == 0) {
                $user->is_partner = 0;
                $user->is_storekeeper = 0;
                $user->is_beautician = 0;
            }
            if ($user_type == 1) {
                $user->is_partner = 0;
                $user->is_storekeeper = 0;
                $user->is_beautician = 0;
            }
            if ($user_type == 2) {
                $user->is_partner = 1;
                $user->is_storekeeper = 0;
                $user->is_beautician = 0;
            }
            if ($user_type == 3) {
                $user->is_partner = 1;
                $user->is_storekeeper = 0;
                $user->is_beautician = 1;
            }
            if ($user_type == 4) {
                $user->is_partner = 1;
                $user->is_storekeeper = 1;
                $user->is_beautician = 0;
            }
            $user->save();
        });
        return $form;
    }
}
