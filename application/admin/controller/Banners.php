<?php

namespace app\admin\controller;

use app\admin\model\Banner;
use app\common\UploadHandle;
use think\Controller;
use think\Db;
use think\Exception;
use think\exception\DbException;


class Banners extends Controller
{
    //[展示页]添加banner
    public function a_b()
    {isNotLogin();
        UploadHandle::install()->delImgName('banner_img_3');
        $this->assign('save_url', '/admin/banners/e_b');
        $this->assign('upload_url', '/admin/upload/ban_img');
        return $this->fetch('/banner/add_banner');
    }

    //[展示页]显示banner
    public function s_b()
    {isNotLogin();
        $data = array();
        try {
            $arr = Banner::all(function ($query) {
                $query->order('id desc');
            });
            foreach ($arr as $val) {
                array_push($data, $val->toArray());
            }

        } catch (Exception $e) {
            exit;
        }
        $this->assign('data', $data);
        return $this->fetch('/banner/show_banner');
    }

//[展示页]显示内容banner
    public function c_b()
    {isNotLogin();
        try {
            if (empty($_GET['id'])) {
                exit;
            }
            if (is_numeric($_GET['id'])) {
                $arr = Banner::get(function ($query) {
                    $query->where('id', 'eq', $_GET['id']);
                })->toArray();
                $this->assign('details', $arr);
            } else {
                $this->error('查询条件不符', '/admin/banners/s_b');
            }

        } catch (Exception $e) {
            exit;
        };

        return $this->fetch('/banner/content_banner');
    }

    //保存数据
    public function e_b()
    {isNotLogin();
        $img_url = UploadHandle::install()->getImgName('banner_img_3');
        if (empty($_POST)) {
            $this->error('不能为空', '/admin/banners/a_b');
            exit;
        }
        if (empty($img_url)) {
            alertMes('上传图片不为空', '/admin/banners/a_b');
            exit;
        }
        $ban = Banner::install();
        $ban->data([
            'type' => submit_input(input('banner_type')),
            'path' => serialize($img_url),
            'time' => time(),
            'title' => submit_input(input('banner_title')),
            'content' => removeXSS(input('editor_content')),
        ], true);
        $ban->allowField(true)->save();
        if (empty($ban->id)) {
            $this->error('保存失败，请重新添加', '/admin/banners/a_b');
            exit;
        } else {
            $this->success('保存成功', '/admin/banners/a_b');
        }
        UploadHandle::install()->delImgName('banner_img_3');
        exit;
    }

    //删除banner
    public function d_b()
    {isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, '查询不为空', null);
            exit;
        }
        if (is_numeric(input('id'))) {
            try {
                $arr_d = Banner::get(function ($query) {
                    $query->where('id', 'eq', input('id'))->field('path');
                })->toArray();
                if (!empty($arr_d['path'])) {
                    foreach ($arr_d['path'] as $va) {
                        deleteOneFile(getImgUrl($va));
                    }
                }
                Banner::destroy(input('id'));
                echo messageJson(200, '数据已清空', null);
            } catch (Exception $e) {
                exit;
            };
        } else {
            echo messageJson(400, '查询条件不符', null);
            exit;
        }
    }

    //批量删除
    public function a_d()
    {isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, '没有任何选项', null);
            exit;
        }
        $ids = submit_input(input('id'));
        $arr_id = explode(',',$ids);
        try {
            if (!empty($arr_id)) {
                foreach ($arr_id as $id) {
                    $arr_d = Banner::install()->where('id', 'eq', $id)->field('path')->find()->toArray();
                    if (!empty($arr_d['path'])) {
                        foreach ($arr_d['path'] as $va) {
                            deleteOneFile(getImgUrl($va));
                        }
                    }else{
                        echo messageJson(400, '查询条件不符', null);
                        exit;
                    }
                }

            }
            Banner::destroy($ids);
            echo messageJson(200, '全部清空', null);
        } catch (Exception $e) {
            exit;
        }
        exit;
    }


}