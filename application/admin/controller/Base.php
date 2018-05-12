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



    }
}
