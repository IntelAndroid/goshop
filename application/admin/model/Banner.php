<?php

namespace app\admin\model;

use think\Model;

class Banner extends Model
{
    private static $ban;

    public static function install()
    {
        if (is_null(self::$ban)) {
            self::$ban = new Banner();
        }
        return self::$ban;
    }

    //获取图片时反序列化
    public function getPathAttr($url)
    {
        return unserialize($url);
    }
    //获取类型转化
    public function getTypeAttr($type)
    {
        $status = [1 => '中首页轮播', 2 => '英首页轮播',3=>'中手机轮播',4=>'英手机轮播'];
        return $status[$type];
    }
    //获取时间
    public function getTimeAttr($time){
        return date('Y-m-d H:i',$time);
    }
    //判断title为空
    public function setTitleAttr($title){
        if($title=='null'){
            return '标题没有设置';
        }else{
            return $title;
        }

    }
    //判断content为空
    public function setContentAttr($content){
        if(empty($content)){
            return '内容没有设置';
        }else{
            return $content;
        }
    }
}