<?php

namespace app\admin\controller;


use app\common\Counts;
use app\common\ServerInfo;


class Index extends Base
{
    //初始页
    public function index()
    {
        $this->assign('name', isset($_SESSION['user_login'])?$_SESSION['user_login']:'ADMIN');
        Counts::install()->setCount('admin_count.dat');
        return $this->fetch('/home/index');

    }

    //欢迎页
    public function welcome()
    {
        //获取原始数组
//        $sysInfo = SaveData::install()->setSpanCache(function () {
//            return ServerInfo::install()->getSystemInfo();
//        }, 'c_n_s', 'n_n_s');
        //管理员统计
        $this->assign('count_admin',Counts::install()->getCount('admin_count.dat'));
        //获取网站登录统计
        $this->assign('count_home', Counts::install()->getCount('home_count.dat'));
        //获取新闻统计
        $this->assign('count_news', Counts::install()->getCount('news_count.dat'));
        //获取产品统计
        $this->assign('count_product', Counts::install()->getCount('product_count.dat'));
        //获取联系统计
        $this->assign('count_contact', Counts::install()->getCount('contact_count.dat'));
        //获取Ip地址
        $this->assign('ip_config', getIpAddress());
        //统计登录运行时间
        $minute = floor((strtotime(date('Y-m-d H:i:s')) - strtotime(getLastLoginTime())) % 86400 / 60);
        $this->assign('count_time', $minute);
        //获取上次登录时间
        $this->assign('login_time', getLastLoginTime());
        //获取CPU数量
//        $this->assign('cpu_num', $sysInfo['cpu']['num']);
        //获取CPU类型
//        $this->assign('cpu_model', $sysInfo['cpu']['model']);
        //获取使用内存
//        $this->assign('mem_used', ServerInfo::install()->unitConversion($sysInfo['memUsed']));
        //获取浏览器类型
        $this->assign('see_type', ServerInfo::install()->GetBrowser());
        //获取内存总数
        $this->assign('mem_total', ServerInfo::install()->getDiskMemory()['total']);
        //获取使用内存
        $this->assign('mem_free', ServerInfo::install()->getDiskMemory()['free']);
        //获取使用内存比例
        $this->assign('mem_prop', ServerInfo::install()->getDiskMemory()['proportion']);
        return $this->fetch('/home/welcome');
    }

}