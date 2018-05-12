<?php
/**
 * 商品分类模型
 */
namespace app\admin\model;
use think\Model;

class Classify extends Model{
private static $cate;
public static function install(){
    if(self::$cate==null){
        self::$cate=new Classify();
    }
    return self::$cate;
}
}