<?php

namespace app\admin\controller;

use app\common\Counts;
use think\Controller;

class Charts extends Controller
{
    public function line()
    {
        $last= mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $week=Counts::install()->getWeekCount('home_count.dat',time());
        $last_week=Counts::install()->getWeekCount('home_count.dat',$last);
        $this->assign('week',$week);
        $this->assign('last',$last_week);
        return $this->fetch('/charts/line_charts');
    }

    public function pie()
    {
        $a=Counts::install()->getCount('home_count.dat');
        $this->assign('mon',$a);
        return $this->fetch('/charts/pie_charts');
    }
}