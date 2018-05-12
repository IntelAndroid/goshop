<?php

namespace app\admin\controller;

use app\admin\model\Feedback;
use think\Controller;
use think\Exception;
use think\exception\DbException;

class Message extends Controller
{
    //查看留言内容
    public function m_g()
    {
        isNotLogin();

        $data = array();
        try {
            $arr = Feedback::all(function ($query) {
                $query->order('id desc')->field(['id', 'user_name', 'msg_status', 'msg_way', 'msg_title', 'msg_time']);
            });
            foreach ($arr as $val) {
                array_push($data, $val->toArray());
            }

        } catch (Exception $e) {
            exit;
        }
        $this->assign('data', $data);
        return $this->fetch('/message/query');
    }

    //删除单个内容
    public function o_d()
    {
        isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, 'not null', null);
            exit;
        }
        if (is_numeric(input('id'))) {
            Feedback::destroy(input('id'));
            echo messageJson(200, '已清除', null);
            exit;
        } else {
            echo messageJson(400, 'error', null);
        }
        exit;
    }

    //批量删除
    public function a_d()
    {
        isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, '没有选项', null);
            exit;
        }
        $a = Feedback::destroy(submit_input(input('id')));
        if ($a) {
            echo messageJson(200, '全部清除', null);
        } else {
            echo messageJson(400, '清除失败', null);
        }
        exit;
    }

    //添加修改
    public function a_u()
    {
        isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, '不为空', null);
            exit;
        }
        try {
            if (is_numeric(input('id'))) {
                $msg = Feedback::get(input('id'));
                $msg->msg_status = 1;
                $msg->allowField(true)->save();
            } else {
                echo messageJson(400, '错误', null);
                exit;
            }
        } catch (DbException $e) {
            echo messageJson(400, 'error', null);
            exit;
        }
        echo messageJson(200, '已处理', null);
        exit;
    }

    //添加留言
    public function s()
    {
        if (empty($_POST['name']) && empty($_POST['email'])) {
            echo messageJson(400, '不为空', null);
            exit;
        }
        if (judgeEmail(input('email'))) {
            $this->msgSave();
        } else {
            if (isPhoneCode(input('email'))) {
                $this->msgSave();
            }
            echo messageJson(400, '邮箱/手机格式错误', null);
            exit;
        }
    }

    private function msgSave()
    {
        if(!empty($_POST['email'])){
            try {
                $feed = Feedback::install();
                $feed->data([
                    'user_id' => 0,
                    'user_name' => submit_input(filterSpecialChar(input('name'))),
                    'msg_title' => submit_input(filterSpecialChar(input('content'))),
                    'msg_status' => 0,
                    'msg_way' => submit_input(input('email')),
                    'msg_content' => '0',
                    'msg_time' => date('Y-m-d h:i', time()),
                    'msg_img' => '0',
                    'msg_state' => 0
                ], true);
                $feed->allowField(true)->save();
            } catch (DbException $e) {
                echo messageJson(400, '错误!', null);
                exit;
            }
            if (empty($feed->id)) {
                echo messageJson(400, '留言失败', null);
                exit;
            } else {
                echo messageJson(200, '留言成功', null);
                exit;
            }
        }

    }
}