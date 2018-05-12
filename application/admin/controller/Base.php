<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/12 0012
 * Time: 下午 1:34
 */
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if (!isset($_SESSION['user_login']) && empty($_SESSION['user_login'])){
            $this->redirect(url('/admin/login/in_v'));
        }


    }
    public function _initialize()
    {
        $controller = request()->controller();
        $action = request()->action();
        $auth = new Auth();
        if(!$auth->check($controller . '-' . $action, session('uid'))){
            $this->error('你没有权限访问');
        }
    }
}
