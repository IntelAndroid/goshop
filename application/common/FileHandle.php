<?php

namespace app\common;
class FileHandle
{
    private static $files;

    public static function install()
    {
        if (is_null(self::$files)) {
            self::$files = new FileHandle();
        }
        return self::$files;

    }

    /**
     * 处理图片上传错误
     * @param array $files
     * @param string $setKey
     * @return array
     */
    public function parseFile(array $files, $setKey = 'tmp_name')
    {
        if (empty($files)) {
            return false;
        }
        /* 一个图片时 */
        foreach ($files as $key => &$value) {
            if (empty($value[$setKey])) {
                continue;
            }
            $value[$setKey] = stripcslashes($value[$setKey]);
        }
        return $files;
    }

    /**
     * 读一级目录
     */
    public function readOne($path)
    {
        if (!is_dir($path) || !($dh = opendir($path))) {
            return array();
        }
        $fileArray = array();

        while (($file = readdir($dh)) !== false) {
            $fileArray[$file] = $file;
        }

        closedir($dh);

        return $fileArray;
    }

    /**
     * 功能：循环检测并创建文件夹
     *
     * @param string $path
     *            文件夹路径
     *            返回：
     * @return bool
     */
    public function createDir($path)
    {
        if (file_exists($path)) {
            return false;
        }

        $this->createDir(dirname($path));
        return mkdir($path, 0777);
    }
    /**
     *获取某个目录下所有文件
     * @param $path
     * @param bool $child 是否包含对应的目录
     * @return array|null
     */
    function getFiles($path,$child=false){
        $files=array();
        if(!$child){
            if(is_dir($path)){
                $dp = dir($path);
            }else{
                return null;
            }
            while ($file = $dp ->read()){
                if($file !="." && $file !=".." && is_file($path.$file)){
                    $files[] = $file;
                }
            }
            $dp->close();
        }else{
            $this->scanfiles($files,$path);
        }
        return $files;
    }

    /**
     * @param $files //结果
     * @param $path //路径
     * @param bool $childDir 子目录名称
     */
    function scanfiles(&$files,$path,$childDir=false){
        $dp = dir($path);
        while ($file = $dp ->read()){
            if($file !="." && $file !=".."){
                if(is_file($path.$file)){//当前为文件
                    $files[]= $file;
                }else{//当前为目录
                    $this->scanfiles($files[$file],$path.$file.DIRECTORY_SEPARATOR,$file);
                }
            }
        }
        $dp->close();
    }
}