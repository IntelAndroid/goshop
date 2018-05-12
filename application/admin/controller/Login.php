<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\common\SendMail;
use think\Cache;
use think\Controller;
use think\Db;
use think\Exception;
use think\exception\DbException;

class Login extends Controller
{
    private $admin;

    /**
     * 登录页显示
     */
    public function in_v()
    {
        $this->assign('hosts', '//' . $_SERVER['HTTP_HOST']);
        return $this->fetch('/login/login');
    }

    /**
     * 登录页验证
     *
     */
    public function in_ver()
    {
        $obj = json_decode(input('data'));
        if (empty($obj)) {
            alertMes('没有登陆', '/admin/login/in_v');
            exit;
        }

        if (!empty(submit_input($obj->ver))) {
            try {
                //判断用户名是不是邮箱登入
                if (judgeEmail($obj->name)) {
                    //是邮箱
                    $result = Db::name('admin')->field('email')->where(['email' => submit_input($obj->name)])->find();
                    if (empty($result)) {
                        echo messageJson(300, '用户名不存在', null);
                        exit;
                    } else {
                        $user = Admin::get(['email' => submit_input($obj->name)]);
                    }
                } else {
                    //不是邮箱
                    $result = Db::name('admin')->field('user_name')->where(['user_name' => submit_input($obj->name)])->find();
                    if (empty($result)) {
                        echo messageJson(300, '用户名不存在', null);
                        exit;
                    } else {
                        $user = Admin::get(['user_name' => submit_input($obj->name)]);
                    }
                }
            } catch (DbException $e) {
                exit($e->getMessage());
            }
            if (submit_input($obj->sha1) == $user->password) {
                $_SESSION['user_login'] = submit_input($obj->name);
                echo messageJson(200, 'OK', null);
                exit;
            } else {
                echo messageJson(400, '密码错误', null);
                exit;
            }
        } else {
            echo messageJson(500, '验证码为空', null);
            exit;
        }

    }

    /**
     * 注册页
     */
    public function at_reg()
    {
        $this->assign('hosts', '//' . $_SERVER['HTTP_HOST']);
        return $this->fetch('/login/register');
    }

    /**
     * 注册页验证
     */
    public function at_ver()
    {
        $obj = json_decode(input('data'));
        if (empty($obj)) {
            alertMes('没有登陆', '/admin/login/in_v');
            exit;
        }
        if (empty($obj->ver)) {
            echo messageJson(500, '验证码为空', null);
            exit;
        } else {
            try {
                if (judgeEmail($obj->name)) {
                    // is email
                    $result = Db::name('admin')->field('email')->where(['email' => submit_input($obj->name)])->select();
                    if (!empty($result)) {
                        echo messageJson(400, '此用户名用邮箱已注册', null);
                        exit;
                    }
                } else {
                    //not is email
                    $result = Db::name('admin')->field('user_name')->where(['user_name' => submit_input($obj->name)])->select();
                    if (!empty($result)) {
                        echo messageJson(400, '此用户名已注册', null);
                        exit;
                    }
                }
            } catch (DbException $e) {
                exit;
            };
            if ($this->admin==null) {
                $this->admin = new Admin();
            }
//            $this->admin->data([
//                'user_name' => submit_input($obj->name),
//                'email' => submit_input($obj->emil),
//                'password' => strtoupper(submit_input($obj->sha1)),
//                'ec_salt' => 'client',
//                'add_time' => time(),
//                'last_login' => time(),
//                'last_ip' => getIpAddress(),
//                'nav_list' => '客户端',
//                'agency_id' => 1,
//                'todolist' => '登入客户端',
//                'role_id' => 1
//            ]);
//            $this->admin->allowField(true)->save();
            echo messageJson(200, '权限不够高级管理员可添加！', null);
            exit;
        }


    }

    /**
     * 忘记密码
     */
    public function fend_pass()
    {
        $this->assign('hosts', '//' . $_SERVER['HTTP_HOST']);
        return $this->fetch('/login/pass');
    }

    /**
     * 接收email消息
     */
    public function email_msg()
    {
        if (empty(input('code'))) {
            alertMes('没有登陆', '/admin/login/in_v');
            exit;
        }
        $json = json_decode(input('data'));
        if (empty($json->ver)) {
            exit;
        }
        if (empty($json->email)) {
            exit;
        } else {
            Cache::set('send_email_mes', $json->email);
            echo messageJson(200, 'OK', null);
        }

    }

    /**
     * 接收code
     */
    public function re_code()
    {

        if (strtoupper(Cache::get('send_code_num')) == strtoupper(input('num'))) {
            echo messageJson(200, 'OK', null);
        } else {
            echo messageJson(300, 'NO', null);
            exit;
        }
    }

    /**
     * 发送邮件
     */
    public function send_em()
    {
        $randN = random_string();
        $rest = SendMail::install()->phpMailer(Cache::get('send_email_mes'), '腾宇计算机', '尊敬的客户验证码:' . $randN);
        if ($rest) {
            echo messageJson(200, 'OK', null);
            Cache::set('send_code_num', $randN);
        }

    }

    /**
     * 修改密码
     */
    public function modify_pss()
    {
        if ($this->admin == null) {
            $this->admin = new Admin();
        }
        if (empty(input('sha1'))) {
            echo messageJson(300, 'NULL', null);
            exit;
        } else {
            $result = $this->admin->save([
                'password' => strtoupper(submit_input(input('sha1')))
            ], ['email' => Cache::get('send_email_mes')]);
            if ($result >= 0) {
                echo messageJson(200, 'ok', null);
            } else {
                echo messageJson(300, 'ON', null);
                exit;
            }
        }


    }

    /**
     * 退出登入
     */
    public function out_in()
    {
        unset($_SESSION['user_login']);
        isNotLogin();
    }


}