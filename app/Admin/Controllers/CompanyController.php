<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Api\WechatPayController;
use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\CompanyLog;
use App\Models\CompanyWithdrawLog;
use App\Models\Store;
use App\Service\WechatTransfersService;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
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
        $id = Company::first()->id;
        if ($id) {
            return $content
                ->header('公司信息')
                ->description('公司信息')
                ->body($this->form()->edit($id));
        }
        return $content
            ->header('公司信息')
            ->description('公司信息')
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
            ->header('Edit')
            ->description('description')
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
        $grid = new Grid(new Company);

        $grid->id('Id');
        $grid->company_name('Company name');
        $grid->earnings('Earnings');
        $grid->withdraw('Withdraw');
        $grid->enc_bank_no('Enc bank no');
        $grid->enc_true_name('Enc true name');
        $grid->bank_code('Bank code');
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
        $show = new Show(Company::findOrFail($id));

        $show->id('Id');
        $show->company_name('Company name');
        $show->earnings('Earnings');
        $show->withdraw('Withdraw');
        $show->enc_bank_no('Enc bank no');
        $show->enc_true_name('Enc true name');
        $show->bank_code('Bank code');
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
        $form = new Form(new Company);
        $form->setAction('/admin/company/1');
        $form->text('company_name', '公司名称');
        $form->display('earnings', '收益')->default(0.00);
        $form->display('withdraw', '提现')->default(0.00);
        $form->text('enc_bank_no', '收款方银行卡号');
        $form->text('enc_true_name', '收款方姓名');
        $options = [];
        foreach (config('global.bank') as $key => $value) {
            $options[$value['bank_code']] = $value['bank_name'];
        }
        $form->select('bank_code', '收款方开户行')->options($options);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableDelete();
            $tools->disableList();
//            $colors =
            $company = Company::first();
            $url = route('admin.company.earnings');
            $icon = 'fa-send-o';
            $text = '收益提现';
            $color = 'success';
            $view = view('admin.tools.earnings', compact('color', 'icon', 'text', 'url', 'company'));
            $tools->append($view);
        });
        $form->saved(function () {
            admin_toastr('保存成功', 'success');
            return redirect('admin/company');
        });
        return $form;
    }


    //收益提现
    protected function earnings(Request $request)
    {
        $enc_bank_no = $request->input('enc_bank_no');
        if (!$enc_bank_no) {
            admin_toastr('请填写银行卡号', 'error');
            return back()->withInput();
        }
        $enc_true_name = $request->input('enc_true_name');
        if (!$enc_true_name) {
            admin_toastr('请填写收款方姓名', 'error');
            return back()->withInput();
        }
        $back_code = $request->input('back_code');
        if (!$back_code) {
            admin_toastr('请选择开户银行', 'error');
            return back()->withInput();
        }
        $id = $request->input('id');
        //将金额整合后，在根据店铺进行提现
        try {
            DB::beginTransaction();
            $log = CompanyLog::where('withdraw_type', 0)->groupBy('store_id')
                ->selectRaw('sum(withdraw_amount) as amount,store_id')
                ->get();
            foreach ($log as $value) {
                //
                $wechat = new WechatTransfersService($value->store_id);
                $out_trade_no = $this->getOrderSn();
                $amount = $value->amount * 100;
                $desc = '企业付款到银行卡';
                $result = $wechat->payForBank($out_trade_no, $amount, $enc_bank_no, $enc_true_name, $back_code, $desc);
                //保存导数据库
                if ($result) {
                    //保存提现记录
                    $withdraw = new CompanyWithdrawLog();
                    $withdraw->company_id = $id;
                    $withdraw->store_id = $value->store_id;
                    $withdraw->payment_no = $result;
                    $withdraw->earning_amount = $value->amount;
                    $withdraw->withdraw_amount = $value->amount;
                    $withdraw->withdraw_type = 1;
                    $withdraw->add_time = time();
                    $withdraw->add_date = date('Y-m-d');
                    $withdraw->save();
                    //修改收益记录
                    CompanyLog::where('store_id', $value->store_id)->where('withdraw_type', 0)->where('withdraw_amount', '>', 0)->update([
                        'withdraw_type' => 1,
                        'withdraw_amount' => 0
                    ]);
                    //修改公司收益记录
                    $company = Company::where('id',$id)->first();
                    $company->earnings = $company->earnings - $value->amount;
                    $company->withdraw = $company->withdraw + $value->amount;
                    $company->save();
                }
            }
            DB::commit();
            admin_toastr('提现成功，请耐心等待到账');
            return back()->withInput();
        } catch (\Exception $exception) {
            DB::rollback();
            admin_toastr('提现失败，请重试','error');
            return back()->withInput();
        }
    }

    protected function getOrderSn()
    {
        $sn = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $sn;
    }
}
