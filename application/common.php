<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 是否登录
 */
function isNotLogin()
{
    if (!isset($_SESSION['user_login']) && empty($_SESSION['user_login'])) {
        alertMes('你还没有登入请登入', '/admin/login/in_v');
    }
}

/**
 * @param $data
 * @return bool
 * 判断是否序列化
 */
function isSerialized($data)
{
    $data = trim($data);
    if ('N;' == $data)
        return true;
    if (!preg_match('/^([adObis]):/', $data, $badions))
        return false;
    switch ($badions[1]) {
        case 'a' :
        case 'O' :
        case 's' :
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                return true;
            break;
    }
    return false;
}

/**
 * 格式化时间
 */
function formatTime($time)
{
    return date('Y-m-d H:i:s', time());
}

/**
 * 获取前几天时间数组
 * @param int $number 前几天
 * @return array
 */
function getTime($number)
{

    if (!is_int($number)) {
        return null;
    }
    $height = $number;
    $dateStr = null;
    $dateArray = array();
    for ($i = $height, $i >= 0; $i--;) {//因为是从零开始的
        $dateStr = date("Y-m-d", strtotime("-" . $i . " day"));
        $dateArray[] = $dateStr;
    }
    return $dateArray;
}


/**
 * @param $mes
 * @param $url
 * 提示框
 */
function alertMes($mes, $url)
{
    echo "<script>alert('{$mes}')</script>";
    if($url==null){
        echo "<script>window.history.go(-1);</script>";
    }else{
        echo "<script>window.location='{$url}';</script>";
    }

}

/**
 * @param $message
 * @param $url
 * Success提示框
 */
function dialogSuccess($message, $url)
{
    alertMes($message, $url);
}

/**
 * @param $message
 * @param $url
 * Fail提示框
 */
function dialogFail($message, $url)
{
    alertMes($message, $url);
}

/**
 * 随机产生8位数字
 */
function getRandomNnm()
{
    return str_pad(mt_rand(0, 99999999), 8, "0", STR_PAD_BOTH);
}


/**
 * 随机产生8位字符
 * @param int $len
 * @return string
 */
function random_string($len = 6)
{
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

/**
 * @return string
 * 生成唯一字符串
 */
function getUniName()
{
    return md5(uniqid(microtime(true), true));
}

/** 生成验证码
 * @param int $type
 * @param int $length
 * @return string
 */
function buildRandomString($type = 1, $length = 4)
{
    if ($type == 1) {
        $chars = join("", range(0, 9));
    } elseif ($type == 2) {
        $chars = join("", array_merge(range("a", "z"), range("A", "Z")));
    } elseif ($type == 3) {
        $chars = join("", array_merge(range("a", "z"), range("A", "Z"), range(0, 9)));
    }
    if ($length > strlen($chars)) {
        exit ("字符串长度不够");
    }
    $chars = str_shuffle($chars);
    return substr($chars, 0, $length);
}

/**
 * @param $time //微秒
 * @param $callback
 * 设置间隔时间执行函数
 * @param bool $bool
 */
function setTimeInterval($time, $callback, $bool = true)
{
    ignore_user_abort(true);//设置与客户机断开是否会终止脚本的执行。
    set_time_limit(0); //设置脚本超时时间，为0时不受时间限制
    ob_end_clean();//清空缓存
    ob_start();//开始缓冲数据
    while ($bool) {
        isset($callback) ? $callback() : null;
        ob_flush();//将缓存中的数据压入队列
        flush();//输出缓存队列中的数据
        usleep($time);//延迟一秒（暂停一秒）
    }
}

/**
 * @param $code
 * @param string $message
 * @param array $data
 * @return string
 * 返回json
 */
function messageJson($code, $message = '', $data = array())
{
    if (!is_numeric($code)) {
        return '';
    }
    $result = array(
        'code' => $code,
        'message' => $message,
        'data' => $data
    );
    return json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * @param $data
 * @return bool|string
 * 拼接字符串如 $arr=[name=>hello] 输出 name=hello&...
 */
function buildQueryString($data)
{
    $query_string = '';
    if (is_array($data)) {
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $val2) {
                    $query_string .= urlencode($key) . '=' . urlencode($val2) . '&';
                }
            } else {
                $query_string .= urlencode($key) . '=' . urlencode($val) . '&';
            }
        }
        $query_string = substr($query_string, 0, -1);
    } else {
        $query_string = $data;
    }
    return $query_string;
}

/**
 * @param $var
 * 打印测试信息
 */
function p($var)
{
    if (is_bool($var)) {
        var_dump($var);
    } else if (is_null($var)) {
        var_dump(NULL);
    } else {
        echo "<pre style='position: relative;z-index: 1000;padding: 10px;border-radius: 5px;background: #f5f5f5;border: 1px solid #aaa;font-size: 14px;line-height: 18px;opacity: 0.9;'>" . print_r($var, true) . "</pre>";
    }
}

/**
 * 获取Ip地址
 */
function getIpAddress()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (!empty($_SERVER["REMOTE_ADDR"])) {
        $cip = $_SERVER["REMOTE_ADDR"];
    } else {
        $cip = "无法获取！";
    }
    return $cip;
}

/**
 * 获取上次登录时间
 */
function getLastLoginTime()
{
    if (!empty($_COOKIE[md5('time')])) {
        $loginTime = $_COOKIE[md5('time')];
        setcookie(md5('time'), date('Y-m-d H:i:s'), time() + 24 * 3600 * 90, '/', substr($_SERVER['HTTP_HOST'], 3, strlen($_SERVER['HTTP_HOST'])), false, true);
    } else {
        setcookie(md5('time'), date('Y-m-d H:i:s'), time() + 24 * 3600 * 90, '/', substr($_SERVER['HTTP_HOST'], 3, strlen($_SERVER['HTTP_HOST'])), false, true);
        $loginTime = $_COOKIE[md5('time')] = date('Y-m-d H:i:s', time());
    }
    return $loginTime;
}

/**
 * @param $data
 * @return null|string|string[]
 * 防sql注入过滤
 */
function submit_input($data)
{
    if (empty($data)) {
        $data = 'null';
    }
    $data = trim($data);
    $data = preg_replace("/cookie/si", "COOKIE", $data); //过滤COOKIE标签
    $data = preg_replace("/<(\/?meta.*?)>/si", "", $data); //过滤meta标签
    $data = preg_replace("/<(\/?script.*?)>/si", "", $data); //过滤script标签
    $data = preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si", "", $data); //过滤script标签
    if (!@get_magic_quotes_gpc()) // 判断magic_quotes_gpc是否为打开
    {
        $data = addslashes($data); // 进行magic_quotes_gpc没有打开的情况对提交数据的过滤
    }
    $data = str_replace("%", "'%", $data); // 把' % '过滤掉
    $data = filterSqlChar($data);
    $data = nl2br($data); // 回车转换
    $data = htmlspecialchars($data); // html标记转换
    return $data;
}

/**
 * 过滤特殊符号
 * @param $data
 * @return null|string|string[]
 */
function filterSpecialChar($data)
{
    $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
    return preg_replace($regex, "", $data);
}

/**
 * 过滤SQL语句
 * @param $data
 * @return string|string[]
 */
function filterSqlChar($data)
{
    $k = 'create|select|insert|update|delete|`|*|and|union|replace|table|order|or|into|load_file|outfile';
    $result = str_ireplace( explode( '|', $k ), '', $data );
    return $result;
}

/**
 * @param $data
 * @return string
 * 解除sql注入
 */
function decompile_html($data)
{
    return html_entity_decode(stripslashes($data), ENT_NOQUOTES);
}


/**
 * @param $filename
 * @return string
 * 获取文件的扩展名
 */
function getExt($filename)
{
    return strtolower(end(explode(".", $filename)));
}

/**
 * @param $json
 * @return array
 * 解析ajax传过来的json
 */
function spilt_post($json)
{
    $spilt = explode('&', $json);
    $result = [];
    foreach ($spilt as $val) {
        $item = explode('=', $val);
        $result[$item[0]] = $item[1];
    }
    return $result;
}

/**
 * @param $data //富文本传来的数据
 * @return string
 * 过滤危害标签与函数
 */
function removeXSS($data)
{
    $obj = null;
    vendor('htmlpurifier.HTMLPurifier#auto');
    // 生成配置对象
    $cfg = HTMLPurifier_Config::createDefault();
    // 以下就是配置：
    $cfg->set('Core.Encoding', 'UTF-8');
    // 设置允许使用的HTML标签
    $cfg->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
    // 设置允许出现的CSS样式属性
    $cfg->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
    // 设置a标签上是否允许使用target="_blank"
    $cfg->set('HTML.TargetBlank', TRUE);
    // 使用配置生成过滤用的对象
    if ($obj == null) {
        $obj = new HTMLPurifier($cfg);
    }
    // 过滤字符串
    return $obj->purify($data);
}

/**
 * IO创建文件
 * @param $fileName
 */
function createFile($fileName)
{
    if (!is_readable($fileName)) {
        $op = fopen($fileName, 'wd');
        fclose($op);
    }
}

/**
 * 读取本地文件
 * @param $filePath
 * @return bool|string
 */
function fileRead($filePath)
{
    if (file_exists($filePath)) {
        $handle = fopen($filePath, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
        //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
        $contents = fread($handle, filesize($filePath));
        fclose($handle);
        return $contents;
    } else {
        return 'null';
    }

}

/**
 * 远程文件读入
 * @param $filePath
 * @return string
 *
 */
function fileLongRead($filePath)
{
    $handle = fopen($filePath, 'r');
    $content = '';
    while (!feof($handle)) {
        $content .= fread($handle, 8080);
    }
    fclose($handle);
    return $content;
}

/**
 * 追加内容创建并写入文件
 * @param $file_path
 * @param $content
 *
 */
function fileAppendWrite($file_path, $content)
{
    header("Content-Type:text/html;Charset=utf-8");
    //如果是追加内容，则使用a+ append
    //如果是全新的写入到文件 ，则使用 w+ write
    $fp = fopen($file_path, "a+");
    fwrite($fp, $content);
    fclose($fp);
}

/**
 * 全新的写入创建并写入文件
 * @param $file_path
 * @param $content
 *
 */
function fileWrite($file_path, $content)
{
    header("Content-Type:text/html;Charset=utf-8");
    //如果是追加内容，则使用a+ append
    //如果是全新的写入到文件 ，则使用 w+ write
    if (!file_exists(dirname($file_path))) {
        mkdir(dirname($file_path), 0777, true);
    }
    $fp = fopen($file_path, "w+");
    fwrite($fp, $content);
    fclose($fp);
}

/**
 * 删除目录及目录下所有文件或删除指定文件
 * @param string $path 待删除目录路径
 * @param bool|int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 * @return bool 返回删除状态
 */
function deleteDirAndFile($path, $delDir = false)
{
    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? deleteDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

/**
 * 递归删除文件夹
 * @param $dir
 * @param string $file_type
 */
function delFile($dir, $file_type = '')
{
    if (is_dir($dir)) {
        $files = scandir($dir);
        //打开目录 //列出目录中的所有文件并去掉 . 和 ..
        foreach ($files as $filename) {
            if ($filename != '.' && $filename != '..') {
                if (!is_dir($dir . '/' . $filename)) {
                    if (empty($file_type)) {
                        unlink($dir . '/' . $filename);
                    } else {
                        if (is_array($file_type)) {
                            //正则匹配指定文件
                            if (preg_match($file_type[0], $filename)) {
                                unlink($dir . '/' . $filename);
                            }
                        } else {
                            //指定包含某些字符串的文件
                            if (false != stristr($filename, $file_type)) {
                                unlink($dir . '/' . $filename);
                            }
                        }
                    }
                } else {
                    delFile($dir . '/' . $filename);
                    rmdir($dir . '/' . $filename);
                }
            }
        }
    } else {
        if (file_exists($dir)) unlink($dir);
    }
}

/**
 * 获取删除图片的地址
 * @param  $url
 * @return string
 * 数据库图片地址 $url
 */
function getImgUrl($url)
{
    return realpath('../') . '\\public' . $url;
}

/**
 * @param $str
 * 获取文本标签里的所有图片地址
 * @return array
 */
function getAllImgSrc($str)
{
    preg_match_all('/<img(.*?)>/', $str, $m);
    foreach ($m[0] as $k => $v) {
        preg_match('<img.*? src=\"?(.*?\\.(jpg|gif|bmp|bnp|png))\".*?/>', $v, $r);
        $arr[$k]['path'] = $r[1];
        $arr[$k]['format'] = $r[2];
    }
    if (!empty($arr)) {
        return $arr;
    }
}

/**
 *删除一个指定文件
 * @param $path
 * @return bool 判断文件是否存在
 */
function deleteOneFile($path)
{
    if (is_readable($path) == false) {
        return false;
    } else {
        unlink($path);
        return true;
    }
}

/**
 * 远程下载文件
 * @param string|$file_path
 * @return string
 */
function downFile($file_path)
{
    $file_name = basename($file_path);
    $buffer = 512;
    if (!file_exists($file_path)) {
        echo "<script type='text/javascript'> alert('对不起！该文件不存在或已被删除！'); </script>";
        exit;
    }
    $fp = fopen($file_path, "r");
    $file_size = filesize($file_path);
    $file_data = '';
    while (!feof($fp)) {
        $file_data .= fread($fp, $buffer);
    }
    fclose($fp);
    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-type:application/octet-stream;");
    header("Accept-Ranges:bytes");
    header("Accept-Length:{$file_size}");
    header("Content-Disposition:attachment; filename={$file_name}");
    header("Content-Transfer-Encoding: binary");
    return $file_data;
}

/**
 * 移动端判断
 */

function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientKey = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-',
            'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb',
            'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientKey) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 判断是不是邮箱
 * @param $email
 * @return bool
 */
function judgeEmail($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是不是手机号
 * @param $code
 * @return false|int
 */
function isPhoneCode($code){
   return preg_match("/^1[34578]{1}\d{9}$/",$code);
}