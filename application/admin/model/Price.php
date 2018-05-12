<?php
/**
 * 添加商品模型
 */

namespace app\admin\model;

use think\Model;

class Price extends Model
{
    private static $c;

    public static function install()
    {
        if (self::$c == null) {
            self::$c = new Price();
        }
        return self::$c;
    }
//商品的属性
    public function getAttributesAttr($attr)
    {
        $s = [1 => '荐', 2 => '新', 3 => '热', 4 => '促', 5 => '包', 6 => '限', 7 => '折'];
        return $s[$attr];
    }
//
    public function getStatusAttr($status)
    {
        $u = [0 => '上架', 1 => '下架'];
        return $u[$status];
    }
    //获取图片转换成数组
    public function getPathAttr($path){
        return unserialize($path);
    }
}