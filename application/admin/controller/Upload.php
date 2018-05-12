<?php

namespace app\admin\controller;

use app\common\UploadHandle;

class Upload
{
    //获取商品图片
    public function g_g_img()
    {isNotLogin();
        UploadHandle::install()->setImgName('goods_img_1');
        exit;
    }
    //获取文章图片
    public function art_img(){
        isNotLogin();
        UploadHandle::install()->setImgName('article_img_2');
        exit;
    }
    //获取banner图片
    public function ban_img(){
        isNotLogin();
        UploadHandle::install()->setImgName('banner_img_3');
        exit;
    }
}