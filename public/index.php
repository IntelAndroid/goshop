<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
header('Content-type:text/html;charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods:GET,POST");
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
header('X-Frame-Options:ALLOWALL');
header("Set-Cookie: hidden=value; httpOnly");
//设置php5.1以上httpOnly支持在代码中来开启session_set_cookie_params(0, NULL, NULL, NULL, TRUE);
if (phpversion() == '5.2.0'){
    ini_set("session.cookie_httponly", 1);
}
//设置session_start();
session_start();
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('EXTEND_PATH', __DIR__ .'/../extend/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';

