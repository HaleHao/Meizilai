<?php


namespace App\Service;

class WeekdayService{

    protected $weekday;

    protected function __construct($time)
    {
        $this->weekday = $this->weekday($time);
    }

    public function weekday($time)
    {
        if(is_numeric($time))
        {
            $weekday = array('周日','周一','周二','周三','周四','周五','周六');
            return $weekday[date('w', $time)];
        }
        return false;
    }
}