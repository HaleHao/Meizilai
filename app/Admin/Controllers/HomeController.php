<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\Corder;
use Encore\Admin\Facades\Admin;
use App\Models\Gorder;
use App\Models\Lorder;
use App\Models\Sign;
use App\Models\Sorder;
use App\Models\Users;
use Carbon\Carbon;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('首页')
            ->row(function (Row $row) {

                $begin_time = time();
                $end_time = strtotime('-6 days');
                $sum = [];
                $date = [];
                for ($start = $end_time; $start <= $begin_time; $start += 24 * 3600) {
                    $time = date('Y-m-d', $start);
                    $sum[] = CompanyLog::where('add_date', $time)->sum('earnings_amount');
                    $date[] = $time;
                }


                $date = json_encode($date);
                $sum = json_encode($sum);
                $text = "公司七日收益";
                if (Admin::user()->isAdministrator()) {
                    $line = view('admin.chartjs.company', compact('sum', 'date', 'text'));
                    $row->column(1 / 2, new Box('公司近七日收益', $line));
                }

                for ($start = $end_time; $start <= $begin_time; $start += 24 * 3600) {
                    $stime = strtotime(date('Y-m-d 00:00:00', $start));
                    $etime = strtotime(date('Y-m-d 23:59:59', $start));
                    if (!Admin::user()->isAdministrator()) {
                        $store_id = Admin::user()->store_id;
                        $gorder[] = Gorder::where('store_id',$store_id)->where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                        $lorder[] = Lorder::where('store_id',$store_id)->where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                        $sorder[] = Sorder::where('store_id',$store_id)->where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                        $corder[] = Corder::where('store_id',$store_id)->where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                    }else{
                        $gorder[] = Gorder::where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                        $lorder[] = Lorder::where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                        $sorder[] = Sorder::where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                        $corder[] = Corder::where('pay_status', 1)->whereBetween('submit_time', [$stime, $etime])->sum('total_price');
                    }
                }
                $gorder = json_encode($gorder);
                $lorder = json_encode($lorder);
                $sorder = json_encode($sorder);
                $corder = json_encode($corder);

                $order = view('admin.chartjs.order', compact('date', 'gorder', 'lorder', 'sorder', 'corder'));
                if (!Admin::user()->isAdministrator()) {
                    $column = 12;
                }else{
                    $column = 1 / 2;
                }
                $row->column($column, new Box('七天订单成交额', $order));


            })->row(function (Row $row) {

                if (!Admin::user()->isAdministrator()) {
                    $store_id = Admin::user()->store_id;
                    $total = Users::where('is_partner', 1)->where('user_type', 3)->where('store_id',$store_id)->where('is_storekeeper', 0)->count();
                    $yes = Sign::where('sign_date', date('Y-m-d'))->where('store_id',$store_id)->count();
                } else {
                    $total = Users::where('is_partner', 1)->where('user_type', 3)->where('is_storekeeper', 0)->count();
                    $yes = Sign::where('sign_date', date('Y-m-d'))->count();
                }
                $no = $total - $yes;
                $data = [$no, $yes];
                $data = json_encode($data);
                $sign = view('admin.chartjs.sign_doughnut', compact('data'));
                $row->column(1 / 2, new Box('今日签到人数', $sign));


                $begin_time = time();
                $end_time = strtotime('-6 days');
                $day_num = [];
                $no_num = [];
                for ($start = $end_time; $start <= $begin_time; $start += 24 * 3600) {
                    $date[] = date('Y-m-d', $start);
                    if (!Admin::user()->isAdministrator()) {
                        $store_id = Admin::user()->store_id;
                        $yes_num = Sign::where('sign_date', date('Y-m-d', $start))->where('store_id',$store_id)->count();
                    } else {
                        $yes_num = Sign::where('sign_date', date('Y-m-d', $start))->count();
                    }
                    $day_num[] = $yes_num;
                    $no_num[] = $total - $yes_num;
                }
                $date = json_encode($date);
                $day_num = json_encode($day_num);
                $no_num = json_encode($no_num);
                $scatter = view('admin.chartjs.sign_line', compact('date', 'day_num', 'no_num'));
                $row->column(1 / 2, new Box('近七日签到人数', $scatter));

//                $bar = view('admin.chartjs.line-stacked');
//                $row->column(1 / 3, new Box('Chart.js Line Chart - Stacked Area', $bar));
            });
    }


}
