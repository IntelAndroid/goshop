<?php
/**
 * 添加文章模型
 * User: Administrator
 * Date: 2018/3/22 0022
 * Time: 下午 1:26
 */

namespace app\admin\model;

use think\Model;

class Articles extends Model
{
    private static $art;

    public static function install()
    {
        if (is_null(self::$art)) {
            self::$art = new Articles();
        }
        return self::$art;
    }

    //设置图片存入数据库系列化
    public function setThumbAttr($imgUrl)
    {
        return serialize($imgUrl);
    }

    //获取图片时反序列化
    public function getThumbAttr($url)
    {
        return unserialize($url);
    }

    //获取文章类型转化
    public function getArticleTypeAttr($type)
    {
        $status = [1 => '新闻', 2 => '资讯'];
        return $status[$type];
    }
}