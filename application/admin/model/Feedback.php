<?php
namespace app\admin\model;
use think\Model;

class Feedback extends Model{
    private static $feed;
    public static function install(){
        if(is_null(self::$feed)){
            self::$feed=new Feedback();
        }
        return self::$feed;
    }
}