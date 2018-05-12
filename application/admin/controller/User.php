<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\Pages;
use think\Controller;
use think\Exception;
use think\exception\DbException;

class User extends Controller
{
    //[展示页]展示用户
    public function u_l()
    {isNotLogin();
        $a_data = array();
        try {
            $ads = Admin::all();
            foreach ($ads as $val) {
                array_push($a_data, $val->toArray());
            }
        } catch (Exception $e) {
            exit;
        }
        $page=new Pages($a_data);
        $this->assign('page', $page);
        return $this->fetch('/user/user_list');
    }

    //[展示页]添加用户
    public function u_a()
    {isNotLogin();
        return $this->fetch('/user/user_add');
    }

    //[保存数据]保存到数据库
    public function u_s()
    {isNotLogin();
        if (empty($_POST)) {
            echo messageJson(400, '提交数据不为空', null);
            exit;
        }
        $user = Admin::install();
        $user->data([
            'user_name' => submit_input(input('user_name')),
            'email' => submit_input(input('email')),
            'password' => strtoupper(md5(trim(submit_input(input('password2'))) . 'TY')),
            'ec_salt' => submit_input(input('ec_salt')),
            'add_time' => time(),
            'last_ip' => getIpAddress(),
            'nav_list' => '管理后台/添加图文',
            'role_id' => (is_numeric(input('role_id'))) ? input('role_id') : 1
        ]);
        $user->allowField(true)->save();
        if (empty($user->id)) {
            echo messageJson(400, '保存失败请重新添加！', null);
            exit;
        } else {
            echo messageJson(200, '添加成功', null);
        }
        exit;
    }

    //[删除数据]
    public function u_d()
    {isNotLogin();
        if (empty($_GET['id'])) {
            echo messageJson(400, '不为空!', null);
            exit;
        }
        if (is_numeric(input('id'))) {
            if (Admin::install()->count('id') == 1) {
                echo messageJson(400, '最低保留一个管理员!', null);
                exit;
            } else {
                Admin::destroy(input('id'));
                echo messageJson(200, '管理员删除成功', null);
            }
            exit;
        }

    }

}