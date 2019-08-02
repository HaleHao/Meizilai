<?php

namespace App\Admin\Controllers;

use App\Models\Device;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Service\MqttService;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class DeviceController extends Controller
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
            ->description('设备管理')
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
            ->description('设备管理')
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
            ->header('新增')
            ->description('设备管理')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Device);

        $grid->id('ID');
        $grid->device_sn('设备编号');
        $grid->column('store.name','所属店铺');
        $grid->qrcode_path('设备二维码')->gallery(['width' => 100, 'height' => 100]);
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->actions(function (Grid\Displayers\Actions $actions){
            $actions->disableView();
            $open_url = route('admin.device.open',['id' => $actions->row->id]);
            $actions->append('<a class="btn btn-success btn-xs" href="' . $open_url . '">开启</a>&nbsp;&nbsp;');
            $close_url = route('admin.device.close',['id' => $actions->row->id]);
            $actions->append('<a class="btn btn-danger btn-xs" href="' . $close_url . '">关闭</a>&nbsp;&nbsp;');
        });

        $grid->disableExport();
//        $grid->filter(fu)
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            // 在这里添加字段过滤器
            $filter->like('device_sn', '设备编号');
//            $filter->
            $filter->equal('store_id', '店铺')->select(
                Store::all()->pluck('name', 'id')
            );
        });
        return $grid;
    }


    public function open()
    {

            $id = request('id');

        $device = Device::where('id',$id)->first();
        $this->openDevice($device->device_sn);
        admin_toastr('开启成功','success');
        return back()->withInput();
    }

    public function close()
    {

            $id = request('id');

        $device = Device::where('id',$id)->first();
        $this->closeDevice($device->device_sn);
        admin_toastr('关闭成功','success');
        return back()->withInput();
    }


    private function closeDevice($device_sn)
    {
        $host = config("MQTT_HOST", "47.104.12.54"); //主机
        $port = config("MQTT_PORT", "4883"); //端口
        $username = config("MQTT_APPID", "6b68c250a1264025b7a546e0c66ad834");; //如果没有则为空
        $password = config("MQTT_Secret", "3a6d44d6b62c4a95afc2efad5348a989"); //如果没有则为空
        //获取设备,开启机器

        //phpMQTT 有四个参数：主机，端口，客户端id，证书。官网这里的案例没写证书，请参考phpMQTT类
        //没有证书的时候只能连接1883端口，不能连接8883端口。
        $mqtt = new MqttService($host, $port, "868729033031571aa", '');
        //连接机器
        $mqtt->connect(true, NULL, $username, $password);
        //订阅机器
        $device_id = "6b68c250a1264025b7a546e0c66ad834/" . $device_sn;

        $topics[$device_id] = array("qos" => 0, "function" => "procmsg");
        $mqtt->subscribe($topics, 0);
        //发送消息
        //关闭机器

        $device_control = 'EC 08 04 00 00 00 00 00 0C EF';

        $relay_control = config('global.device.relay_control.close');

        $sound = config('global.device.sound_control.close');

        //转为16进制数据
        $device_control = hex2bin(preg_replace('# #', '', $device_control));
        $relay_control = hex2bin(preg_replace('# #', '', $relay_control));
        $soundHEX = hex2bin(preg_replace('# #', '', $sound));

        $mqtt->publish($device_id, $device_control, 0);

        $mqtt->publish($device_id, $relay_control, 0);

        $mqtt->publish($device_id, $soundHEX, 0);
        //关闭连接
        $mqtt->close();

    }

    public function openDevice($device_sn)
    {
        //phpMQTT 有四个参数：主机，端口，客户端id，证书。官网这里的案例没写证书，请参考phpMQTT类
        //没有证书的时候只能连接1883端口，不能连接8883端口。

        $host = config("MQTT_HOST", "47.104.12.54"); //主机
        $port = config("MQTT_PORT", "4883"); //端口
        $username = config("MQTT_APPID", "6b68c250a1264025b7a546e0c66ad834");; //如果没有则为空
        $password = config("MQTT_Secret", "3a6d44d6b62c4a95afc2efad5348a989"); //如果没有则为空
        //获取设备,开启机器
        $mqtt = new MqttService($host, $port, "868729033031571aa", '');
        //连接机器
        $mqtt->connect(true, NULL, $username, $password);
        //订阅机器
        Log::info("6b68c250a1264025b7a546e0c66ad834/" . $device_sn);
        $device_id = "6b68c250a1264025b7a546e0c66ad834/" . $device_sn;
        $topics[$device_id] = array("qos" => 0, "function" => "procmsg");
        $mqtt->subscribe($topics, 0);
        //发送消息

        $device_control = 'EC 08 04 01 00 00 00 00 0D EF';

        $relay_control = config('global.device.relay_control.open','10 04 01');

        $sound = config('global.device.sound_control.open','10 05 01 20');

        //转为16进制数据
        $device_control = hex2bin(preg_replace('# #', '', $device_control));
        $relay_control = hex2bin(preg_replace('# #', '', $relay_control));
        $soundHEX = hex2bin(preg_replace('# #', '', $sound));

        $mqtt->publish($device_id, $device_control, 0);

        $mqtt->publish($device_id, $relay_control, 0);

        $mqtt->publish($device_id, $soundHEX, 0);
        //关闭连接
        $mqtt->close();

    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Device::findOrFail($id));

        $show->id('Id');
        $show->device_sn('Device sn');
        $show->store_id('Store id');
        $show->qrcode_path('Qrcode path');
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
        $form = new Form(new Device);

        $form->text('device_sn', '设备编号')->rules('required',[
            'required' => '请填写设备编号'
        ]);

        $form->text('device_name','设备名称')->rules('required|max:50',[
            'required' => '请填写设备名称',
            'max' => '设备名称长度不能超过50个字符'
        ]);
        $form->select('store_id', '所属店铺')->options(Store::all()->pluck('name','id'))->rules('required',[
            'required' => '请选择店铺'
        ]);
        $form->number('device_minute','设备使用时长')->help('按分钟来填写');

        $status = [
            'on'  => ['value' => 1, 'text' => 'WiFi', 'color' => 'success'],
            'off' => ['value' => 2, 'text' => '2G', 'color' => 'info'],
        ];
        $form->switch('device_type','设备类型')->states($status)->default(1);
//        $form->text('qrcode_path', 'Qrcode path');
        $form->saved(function (Form $form){
            $device = $form->model();
//            dd($store);
            if (!$device->qrcode_path) {
                $url = config('DEVICE_URL', 'https://gzchenyu.cn/pays') . '?store_id='. $device->store_id .'&device_id=' . $device->id;
                $device->qrcode_path = $this->generateQrcode($url);
                $device->save();
            }
        });

       $form->tools(function(Form\Tools $tools){
            $tools->disableView();
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
