<?php

namespace App\Admin\Controllers;

use App\Models\Accredit;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Intervention\Image\Facades\Image;

class AccreditController extends Controller
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
            ->description('授权管理')
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
            ->description('授权管理')
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
            ->description('授权管理')
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
            ->description('授权管理')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Accredit);
        $grid->id('Id');
        $grid->username('姓名');
        $grid->mobile('手机号码');
        $grid->id_card('身份证');
        $grid->start_time('开始时间');
        $grid->end_time('结束时间');
        $grid->contract_sn('合同编号');
        $grid->contract('合同')->gallery(['width' => 100, 'height' => 100]);
        $grid->company('公司');
        $grid->product('产品');
        $grid->address('地址');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->disableExport();
        $grid->actions(function(Grid\Displayers\Actions $actions){
            $actions->disableEdit();
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
        $show = new Show(Accredit::findOrFail($id));

        $show->id('Id');
        $show->mobile('手机号码');
        $show->id_card('身份证');
        $show->username('姓名');
        $show->start_time('开始时间');
        $show->end_time('结束时间');
        $show->contract_sn('合同编号');
        $show->contract('合同')->image();
        $show->company('公司名称');
        $show->product('代理产品');
        $show->address('代理区域');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
//                $tools->disableList();
//                $tools->disableDelete();
            });
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


//        dd(Accredit::all());
        $form = new Form(new Accredit);
        $form->mobile('mobile', '手机号码')->options(['mask' => '99999999999'])->rules('required',[
            'required' => '请填写手机号码',
        ]);
        $form->text('id_card', '身份证')->rules('required',[
            'required' => '请填写身份证'
        ]);
        $form->text('username', '姓名')->rules('required',[
            'required' => '请填写身份证'
        ]);
        $form->date('start_time', '开始时间')->rules('required',[
            'required' => '请填写开始时间'
        ]);
        $form->date('end_time', '结束时间')->rules('required',[
            'required' => '请填写结束时间'
        ]);
//        $form->text('contract_sn', 'Contract sn');
//        $form->text('contract', 'Contract');
        $form->text('company', '公司名称')->rules('required',[
            'required' => '请填写公司名称'
        ]);
        $form->text('product', '产品')->rules('required',[
            'required' => '请填写产品'
        ]);
        $form->text('address', '区域')->rules('required',[
            'required' => '请填写区域'
        ]);
        $form->text('contract_sn', '合同编号')->rules('required',[
            'required' => '请填写合同编号'
        ]);
        $form->text('legal_person', '法人')->rules('required',[
            'required' => '请填写法人'
        ]);

        $form->saved(function (Form $form) {
            if (data_get($form->model(), 'contract_sn')) {
                $img = Image::make('uploads/images/contract.png')->resize('688', '972');

                $img->text($form->username, 220, 435, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });

                $img->text($form->address, 170, 477, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });

                $img->text($form->product, 150, 515, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });

                $img->text(date('Y', strtotime($form->start_time)), 180, 602, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });

                $img->text(date('m', strtotime($form->start_time)), 260, 602, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });


                $img->text(date('d', strtotime($form->start_time)), 320, 602, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });

                $img->text(date('Y', strtotime($form->end_time)), 385, 602, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });


                $img->text(date('m', strtotime($form->end_time)), 460, 602, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });


                $img->text(date('d', strtotime($form->end_time)), 520, 602, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });


                $img->text($form->legal_person, 480, 775, function ($font) {

                    $font->file('ttf/WeiRuanYaHei.ttf');

                    $font->size(20);

//            $font->align('center');

                    $font->valign('bottom');

                    $font->color('#000');
                });


                $token = date('YmdHis') . substr(md5(time() . rand(00000, 99999)), 0, 8) . '.jpg';
                $path = 'uploads/contract/' . $token;
                $filename = 'contract/' . $token;
                $img->save($path);

                $form->model()->contract = $filename;
                $form->model()->save();
            }

        });

        return $form;
    }
}
