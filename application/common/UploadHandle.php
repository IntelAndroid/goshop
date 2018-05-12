<?php

namespace app\common;

use think\Cache;
use think\Request;

class UploadHandle
{
    private static $uimg;

    public static function install()
    {
        if (is_null(self::$uimg)) {
            self::$uimg = new UploadHandle();
        }
        return self::$uimg;
    }

    /**
     * 获取上传图片
     * @param $cacheName
     * @param $postName
     */
    public function setImgName($cacheName, $postName='images')
    {
        $file = Request::instance()->file($postName)->validate(['size' => 128748, 'ext' => 'jpg,png,gif,jpeg']);
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload/image');
        if ($info) {
            if(!file_exists(ROOT_PATH . 'public' . DS . 'upload/image/')){
                mkdir(ROOT_PATH . 'public' . DS . 'upload/image/',0777,true);
            }
            fileAppendWrite(ROOT_PATH . 'public' . DS . 'upload/image/' . md5($cacheName) . '.log', '\\upload\\image\\' . $info->getSaveName() . '&');
            echo messageJson(200, '上传成功', null);
            exit;
        } else {
            echo messageJson(400, $file->getError(), null);
            exit;
        }
    }

    /**
     * 获取上传文件
     * @param $cacheName
     * @param $postName
     */
    public function setFileName($cacheName, $postName)
    {
        $file = Request::instance()->file($postName)->validate(['size' => 128748, 'ext' => 'xls,xlsx']);
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload/file');
        if ($info) {
            if(!file_exists(ROOT_PATH . 'public' . DS . 'upload/file/')){
                mkdir(ROOT_PATH . 'public' . DS . 'upload/file/',0777,true);
            }
            fileAppendWrite(ROOT_PATH . 'public' . DS . 'upload/file/' . md5($cacheName) . '.log', '\\upload\\file\\' . $info->getSaveName() . '&');
            echo messageJson(200, '上传成功', null);
            exit;
        } else {
            echo messageJson(400, $file->getError(), null);
            exit;
        }
    }

    /**
     * 获取图片地址
     * @param $cacheName
     * @return bool
     */
    public function getImgName($cacheName)
    {
        $result = fileRead(ROOT_PATH . 'public' . DS . 'upload/image/' . md5($cacheName) . '.log');
        if (strlen($result) == 1) {
            return null;
        } else if ($result == 'null') {
            return null;
        } else {
            return explode('&', substr($result, 1, strlen($result) - 2));
        }

    }

    /**
     * 删除图片名字
     * @param $cacheName
     */
    public function delImgName($cacheName)
    {
        fileWrite(ROOT_PATH . 'public' . DS . 'upload/image/' . md5($cacheName) . '.log', '&');
    }

    /**
     * 获取图片地址
     * @param $cacheName
     * @return array
     */
    public function getFileName($cacheName)
    {
        $result = fileRead(ROOT_PATH . 'public' . DS . 'upload/file/' . md5($cacheName) . '.log');
        if (strlen($result) == 1) {
            return null;
        } else if ($result == "null") {
            return null;
        } else {
            return explode('&', substr($result, 1, strlen($result) - 2));
        }

    }

    /**
     * 删除图片名字
     * @param $cacheName
     */
    public function delFileName($cacheName)
    {
        fileWrite(ROOT_PATH . 'public' . DS . 'upload/file/' . md5($cacheName) . '.log', '&');
    }
}