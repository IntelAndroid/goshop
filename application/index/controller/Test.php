<?php
namespace app\index\controller;
use think\Controller;

class Test extends Controller{
    public function a(){
        p($_SESSION['v']=11);
        p(session_id());
        p($_COOKIE);


    }
}