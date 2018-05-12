<?php

namespace app\index\controller;

use app\admin\model\Articles;
use app\admin\model\Classify;
use app\admin\model\Price;
use app\common\SaveData;
use think\Controller;
use think\Exception;
use think\exception\DbException;

class Index extends Controller
{
    //首页
    public function index()
    {
//        $data1 = SaveData::install()->setSingleCache(function () {
//            $data = [];
//            try {
//                $a = Articles::install()->field(['id', 'title', 'publish_time', 'thumb'])->where('article_type', 'eq', 1)->order('id desc')->limit(5)->select();
//                if (empty($a)) {
//                    exit("数据库没有数据");
//                }
//                foreach ($a as $k => $v) {
//                    $data[$k] = $v->toArray();
//                    $data[$k]['publish_time'] = str_replace('-', '/', substr($data[$k]['publish_time'], '5'));
//                }
//            } catch (DbException $e) {
//                exit('error 10010');
//            }
//            return $data;
//        }, 'home_article_cache');
//        $data2 = SaveData::install()->setSingleCache(function () {
//            $data = [];
//            try {
//                $a = Articles::install()->field(['id', 'title', 'publish_time', 'thumb'])->where('article_type', 'eq', 2)->order('id desc')->limit(5)->select();
//                if (empty($a)) {
//                    exit("数据库没有数据");
//                }
//                foreach ($a as $k => $v) {
//                    $data[$k] = $v->toArray();
//                    $data[$k]['publish_time'] = str_replace('-', '/', substr($data[$k]['publish_time'], '5'));
//                }
//            } catch (DbException $e) {
//                exit('error 10010');
//            }
//            return $data;
//        }, 'home_article_cache2');
//        $arr_program = SaveData::install()->setSingleCache(function () {
//            try {
//                $programs = [];
//                $program = [];
//                $ids = Classify::install()->field('id')->where(['name' => 'program'])->find();
//                if(empty($ids)){
//                    exit('数据库没有创建一级分类:program');
//                }
//                $id=$ids->getData('id');
//                $a = Price::install()->field(['id', 'name', 'sketch', 'link', 'attributes', 'path'])->where(['tpid' => $id])->order('id desc')->select();
//                foreach ($a as $k => $item) {
//                    $program[$k] = $item->toArray();
//                }
//                for ($i = 0; $i < ceil(count($program) / 4); $i++) {
//                    $programs[$i] = array_slice($program, $i * 4, 4);
//                }
//            } catch (Exception $e) {
//                exit;
//            }
//            return $programs;
//        }, 'home_program_cache');
//        $arr_web=SaveData::install()->setSingleCache(function () {
//            try {
//                $webs = [];
//                $web = [];
//                $ids = Classify::install()->field('id')->where(['name' => 'web'])->find();
//                if(empty($ids)){
//                   exit('数据库没有创建一级分类:web');
//                }
//                $id=$ids->getData('id');
//                $w = Price::install()->field(['id', 'name', 'unit', 'nowprice', 'path', 'attributes'])->where(['tpid' => $id])->order('id desc')->select();
//                foreach ($w as $k => $item) {
//                    $web[$k] = $item->toArray();
//                }
//                for ($i = 0; $i < ceil(count($web) / 3); $i++) {
//                    $webs[$i] = array_slice($web, $i * 3, 3);
//                }
//            } catch (Exception $e) {
//                exit;
//            }
//            return $webs;
//        }, 'home_webs_cache');
//        $this->assign('data', $data1);
//        $this->assign('data2', $data2);
//        $this->assign('programs', $arr_program);
//        $this->assign('webs', $arr_web);
//        return $this->fetch('/home/index');
echo 'ok';
    }


}
