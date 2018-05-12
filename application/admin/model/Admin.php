<?php
/**
 * 用户管理模型
 */

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    private static $admins;

    public static function install()
    {
        if (is_null(self::$admins)) {
            self::$admins = new Admin();
        }
        return self::$admins;
    }


    //获取时间字符转化格式
    protected function getAddTimeAttr($time)
    {
        return date('Y-m-d H:i', $time);
    }
    //获取角色
    protected function getRoleIdAttr($id){
        $status=[0=>'总编',1=>'超级管理员',2=>'栏目主辑'];
        return $status[$id];
    }
}