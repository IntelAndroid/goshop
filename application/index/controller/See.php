<?php
/**
 * 查看文章内容
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 下午 2:23
 */

namespace app\index\controller;

use app\admin\model\Articles;
use app\admin\model\Classify;
use app\admin\model\Price;
use app\common\SaveData;
use Exception;
use think\Controller;

class See extends Controller
{
    //展示文章内容
    public function con()
    {
        if (!empty($_GET['code'])) {
            if (is_numeric($_GET['code'])) {
                try {
                    $data = Articles::install()->field(['title', 'content', 'link', 'email', 'abstract', 'publish_time', 'thumb'])->where('id', 'eq', $_GET['code'])->find()->toArray();
                    if (empty($data)) {
                        $this->error('读取内容出错!');
                        exit;
                    }
                } catch (Exception $e) {
                    exit('error 10010');
                }
                $this->assign('data', $data);
                $this->assign('host', '//' . $_SERVER['HTTP_HOST'] . '#dynamic');
                return $this->fetch('/home/information');
            }
        }
        $this->error('读取内容出错!');
    }

    //小程序案例
    public function item()
    {
        $arr_class = SaveData::install()->setSingleCache(function () {
            try {
                $arr_class = array();
                $ids = Classify::install()->where(['name' => 'programs'])->field('id')->find();
                if (empty($ids)) {
                    exit('数据库没有创建一级分类:programs');
                }
                $id = $ids->getData('id');
                $cl = Classify::install()->where('pid', 'eq', $id)->field(['id', 'name', 'pid'])->order('id desc')->select();
                foreach ($cl as $v) {
                    $programs = [];
                    $arr2 = array();
                    $price = Price::install()->where(['tid' => $v['id'], 'tpid' => $v['pid']])->field(['id', 'name', 'sketch', 'link', 'attributes', 'path'])->select();
                    foreach ($price as $ps) {
                        array_push($arr2, $ps->toArray());
                    }
                    for ($i = 0; $i < ceil(count($arr2) / 4); $i++) {
                        $programs[$i] = array_slice($arr2, $i * 4, 4);
                    }
                    $v['element'] = $programs;
                    unset($arr2);
                    array_push($arr_class, $v->toArray());
                }
            } catch (Exception $e) {
                exit;
            };
            return $arr_class;
        }, 'see_program_cache');
        $banners = array();
        foreach ($arr_class as $k => $items) {
            foreach ($items['element'] as $path) {
                foreach ($path as $k => $pa) {
                    if ($k < 5) {
                        $mag['name'] = $pa['name'];
                        $mag['path'] = $pa['path'][0];
                        array_push($banners, $mag);
                    }
                }

            }
        }
        $this->assign('program', $arr_class);
        $this->assign('banner', $banners);
        return $this->fetch('/home/program-details');
    }

    //网站案例
    public function web()
    {
        $arr_class = SaveData::install()->setSingleCache(function () {
            try {
                $arr_class = array();
                $ids = Classify::install()->where(['name' => 'webs'])->field('id')->find();
                if (empty($ids)) {
                    exit('数据库没有创建一级分类:webs');
                }
                $id = $ids->getData('id');
                $cl = Classify::install()->where('pid', 'eq', $id)->field(['id', 'name', 'pid'])->order('id desc')->select();
                foreach ($cl as $v) {
                    $programs = [];
                    $arr2 = array();
                    $price = Price::install()->where(['tid' => $v['id'], 'tpid' => $v['pid']])->field(['id', 'name', 'unit', 'nowprice', 'attributes', 'path'])->select();
                    foreach ($price as $ps) {
                        array_push($arr2, $ps->toArray());
                    }
                    for ($i = 0; $i < ceil(count($arr2) / 3); $i++) {
                        $programs[$i] = array_slice($arr2, $i * 3, 3);
                    }
                    $v['element'] = $programs;
                    unset($arr2);
                    array_push($arr_class, $v->toArray());
                }
            } catch (Exception $e) {
                exit;
            };
            return $arr_class;
        }, 'see_webs_cache');

        $banners = array();
        foreach ($arr_class as $k => $items) {
            foreach ($items['element'] as $path) {
                foreach ($path as $k => $pa) {
                    if ($k < 5) {
                        $mag['name'] = $pa['name'];
                        $mag['path'] = $pa['path'][0];
                        array_push($banners, $mag);
                    }
                }

            }
        }

        $this->assign('banner', $banners);
        $this->assign('web', $arr_class);
        return $this->fetch('/home/web-details');
    }

    //网站说明
    public function exp()
    {
        if (empty($_GET)) {
            alertMes('数据不为空', null);
            exit;
        }
        if (is_numeric($_GET['code'])) {
            try {
                $good = Price::install()->field(['name', 'sketch','link','unit', 'path', 'curprice', 'nowprice', 'inventory', 'restrict', 'already', 'details'])->where(['id' => $_GET['code']])->find()->toArray();
                $this->assign('data', $good);
            } catch (\think\Exception $e) {
                exit('error 10010');
            }
            return $this->fetch('/home/web-explain');
        } else {
            alertMes('数据错误！', null);
            exit;
        }

    }

    public function about()
    {
        return $this->fetch('/home/about');
    }
}