<?php
// 应用入口文件
define('FILE', _dir_path(realpath('../../')));
define('SITEDIR', _dir_path(substr(dirname(__FILE__), 0, -8)));
if (extension_loaded('zlib')) {
    ob_end_clean();
    ob_start('ob_gzhandler');
}
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.4.0', '<')) die('require PHP > 5.4.0 !');
//error_reporting(E_ALL ^ E_NOTICE);//显示除去 E_NOTICE 之外的所有错误信息
error_reporting(E_ERROR | E_WARNING | E_PARSE);//报告运行时错误

//检测是否已安装TYQ系统
if (!file_exists(__DIR__."/install.lock")) {
    if ($_SERVER['PHP_SELF'] != '/install/index.php') {
        header("Content-type: text/html; charset=utf-8");
        exit("请在域名根目录下安装,如:<br/> www.xxx.com/index.php 正确 <br/>  www.xxx.com/www/index.php 错误,域名后面不能圈套目录, 但项目没有根目录存放限制,可以放在任意目录,apache虚拟主机配置一下即可");
    }
    install();
    exit();
} else {
    header('Location:' . json_decode(fileRead(FILE . md5('email') . '.log'))->url);
}

function install()
{
    if (file_exists('./install.lock')) {
        echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        	你已经安装过该系统，如果想重新安装，请先删除站点install目录下的 install.lock 文件，然后再安装。
        </body>
        </html>';
        exit;
    }
    @set_time_limit(1000);
    if (phpversion() <= '5.4.0')
        ini_set("magic_quotes_runtime", 0);
    if ('5.4.0' > phpversion()) {
        header("Content-type:text/html;charset=utf-8");
        exit('您的php版本过低，不能安装本软件，请升级到5.4.0或更高版本再安装，谢谢！');
    }

    define("TP_SHOP_VERSION", '20170101');
    date_default_timezone_set('PRC');
    error_reporting(E_ALL & ~E_NOTICE);
    header('Content-Type: text/html; charset=UTF-8');

//数据库
    $sqlFile = 'tpshop.sql';
    $configFile = 'config.php';
    if (!file_exists(SITEDIR . 'install/' . $sqlFile) || !file_exists(SITEDIR . 'install/' . $configFile)) {
        echo '缺少必要的安装文件!';
        exit;
    }
    $Title = "腾宇安装向导";
    $Powered = "Powered by TPshop";
    $steps = array(
        '1' => '安装许可协议',
        '2' => '运行环境检测',
        '3' => '安装参数设置',
        '4' => '安装详细过程',
        '5' => '安装完成',
    );
    $step = isset($_GET['step']) ? $_GET['step'] : 1;

//地址
    $scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
    $rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
    $domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
    if ((int)$_SERVER['SERVER_PORT'] != 80) {
        $domain .= ":" . $_SERVER['SERVER_PORT'];
    }
    $domain = $domain . $rootpath;

    switch ($step) {

        case '1':
            include_once("./templates/step1.php");
            exit();

        case '2':
            if (phpversion() < 5) {
                die('本系统需要PHP5+MYSQL >=5.0环境，当前PHP版本为：' . phpversion());
            }
            $phpv = @phpversion();
            $os = PHP_OS;
            //$os = php_uname();
            $tmp = function_exists('gd_info') ? gd_info() : array();
            $server = $_SERVER["SERVER_SOFTWARE"];
            $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
            $name = $_SERVER["SERVER_NAME"];
            $max_execution_time = ini_get('max_execution_time');
            $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<span style="color: green">[√]On</span>' : '<span style="color: red">[×]Off</span>');
            $allow_url_fopen = (ini_get('allow_url_fopen') ? '<span style="color: green">[√]On</span>' : '<span style="color: red">[×]Off</span>');
            $safe_mode = (ini_get('safe_mode') ? '<span style="color: red">[×]Off</span>' : '<span style="color: green">[√]On</span>');

            $err = 0;
            if (empty($tmp['GD Version'])) {
                $gd = '<span style="color: red">[×]Off</span>';
                $err++;
            } else {
                $gd = '<span style="color: green;">[√]On</span>' . $tmp['GD Version'];
            }
            if (function_exists('mysqli_connect')) {
                $mysql = '<span class="correct_span">&radic;</span> 已安装';
            } else {
                $mysql = '<span class="correct_span error_span">&radic;</span> 请安装mysqli扩展';
                $err++;
            }
            if (ini_get('file_uploads')) {
                $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
            } else {
                $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
            }
            if (function_exists('session_start')) {
                $session = '<span class="correct_span">&radic;</span> 支持';
            } else {
                $session = '<span class="correct_span error_span">&radic;</span> 不支持';
                $err++;
            }
            if (function_exists('curl_init')) {
                $curl = '<span style="color: green">[√]支持</span> ';
            } else {
                $curl = '<span style="color: red">[×]不支持</span>';
                $err++;
            }
            if (function_exists('file_put_contents')) {
                $file_put_contents = '<span style="color: green">[√]支持</span> ';
            } else {
                $file_put_contents = '<span style="color: red">[×]不支持</span>';
                $err++;
            }

            $folder = array(
                'public/install',
                'public/upload',
                'runtime/cache',
                'runtime/log',
                'runtime/temp',
            );
            include_once("./templates/step2.php");
            exit();

        case '3':
            $dbName = strtolower(trim($_POST['dbName']));
            $_POST['dbport'] = $_POST['dbport'] ? $_POST['dbport'] : '3306';
            if ($_GET['testdbpwd']) {
                $dbHost = $_POST['dbHost'];
                $conn = @mysqli_connect($dbHost, $_POST['dbUser'], $_POST['dbPwd'], NULL, $_POST['dbport']);
                if (mysqli_connect_errno()) {
                    die(json_encode(0));
                } else {
                    $result = mysqli_query($conn, "SELECT @@global.sql_mode");
                    $result = $result->fetch_array();
                    $version = mysqli_get_server_info($conn);
                    if ($version >= 5.7) {
                        if (strstr($result[0], 'STRICT_TRANS_TABLES') || strstr($result[0], 'STRICT_ALL_TABLES') || strstr($result[0], 'TRADITIONAL') || strstr($result[0], 'ANSI'))
                            exit(json_encode(-1));
                    }
                    $result = mysqli_query($conn, "select count(table_name) as c from information_schema.`TABLES` where table_schema='$dbName'");
                    $result = $result->fetch_array();
                    if ($result['c'] > 0)
                        exit(json_encode(-2));

                    exit(json_encode(1));
                }
            }
            include_once("./templates/step3.php");
            exit();
        case '4':
            if (intval($_GET['install'])) {
                $n = intval($_GET['n']);
                $site_url = trim($_POST['siteurl']);
                $arr = array();
                $dbHost = trim($_POST['dbhost']);
                $_POST['dbport'] = $_POST['dbport'] ? $_POST['dbport'] : '3306';
                $dbName = strtolower(trim($_POST['dbname']));
                $dbUser = trim($_POST['dbuser']);
                $dbPwd = trim($_POST['dbpw']);
                $dbPrefix = empty($_POST['dbprefix']) ? 'ty_' : trim($_POST['dbprefix']);
                $username = trim($_POST['manager']);
                $password = trim($_POST['manager_pwd']);
                $email = trim($_POST['manager_email']);
                fileWrite(FILE . md5('email') . '.log', json_encode(array('username' => $username, 'password' => $password, 'email' => $email, 'url' => $site_url)));
                if (!function_exists('mysqli_connect')) {
                    $arr['msg'] = "请安装 mysqli 扩展!";
                    echo json_encode($arr);
                    exit;
                }

                $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, NULL, $_POST['dbport']);
                if (mysqli_connect_errno()) {
                    $arr['msg'] = "连接数据库失败!" . mysqli_connect_error();
                    echo json_encode($arr);
                    exit;
                }
                mysqli_set_charset($conn, "utf8"); //,character_set_client=binary,sql_mode='';
                $version = mysqli_get_server_info($conn);
                if ($version < 5.5) {
                    $arr['msg'] = '数据库版本太低! 必须5.5以上';
                    echo json_encode($arr);
                    exit;
                }

                if (!mysqli_select_db($conn, $dbName)) {
                    //创建数据时同时设置编码
                    if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;")) {
                        $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！';
                        echo json_encode($arr);
                        exit;
                    }
                    if ($n == -1) {
                        $arr['n'] = 0;
                        $arr['msg'] = "成功创建数据库:{$dbName}<br>";
                        echo json_encode($arr);
                        exit;
                    }
                    mysqli_select_db($conn, $dbName);
                }

                //读取数据文件
                $sqldata = file_get_contents(SITEDIR . 'install/' . $sqlFile);

                $sqlFormat = sql_split($sqldata, $dbPrefix);
                //创建写入sql数据库文件到库中 结束
                /**
                 * 执行SQL语句
                 */
                $counts = count($sqlFormat);
                for ($i = $n; $i < $counts; $i++) {
                    $sql = trim($sqlFormat[$i]);
                    if (strstr($sql, 'CREATE TABLE')) {
                        preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                        mysqli_query($conn, "DROP TABLE IF EXISTS `$matches[1]`");
                        $ret = mysqli_query($conn, $sql);
                        if ($ret) {
                            $message = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成!<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li> ';
                        } else {
                            $message = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败!<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li>';
                        }
                        $i++;
                        $arr = array('n' => $i, 'msg' => $message);
                        echo json_encode($arr);
                        exit;
                    } else {
                        if (trim($sql) == '') {
                            continue;
                        }
                        $ret = mysqli_query($conn, $sql);
                        if ($ret) {
                            $message = '<li><span class="correct_span">&radic;</span>插入数据表' . $i . '，完成!<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li> ';
                        } else {
                            $message = '<li><span class="correct_span error_span">&radic;</span>插入数据表' . $i . '，失败!<span style="float: right;">' . date('Y-m-d H:i:s') . '</span></li>';
                        }
                        $i++;
                        $arr = array('n' => $i, 'msg' => $message);
                        echo json_encode($arr);
                        exit;
                    }
                }

                if ($i == 999999)
                    exit;
                //读取配置文件，并替换真实配置数据1
                $strConfig = file_get_contents(SITEDIR . 'install/' . $configFile);
                $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
                $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
                $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
                $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
                $strConfig = str_replace('#DB_PORT#', $_POST['dbport'], $strConfig);
                $strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
                $strConfig = str_replace('#DB_CHARSET#', 'utf8', $strConfig);
                $strConfig = str_replace('#DB_DEBUG#', false, $strConfig);
                @chmod(dirname(SITEDIR) . '/application/database.php', 0777); //数据库配置文件的地址
                @file_put_contents(dirname(SITEDIR) . '/application/database.php', $strConfig); //数据库配置文件的地址
                //更新网站配置信息2
                /*
                $site_name = trim($_POST['sitename']);
                $site_url = trim($_POST['siteurl']);
                $site_keywords = trim($_POST['sitekeywords']);
                $site_desc = trim($_POST['siteinfo']);
                $sql = "INSERT INTO `{$dbPrefix}config` (name,value,inc_type) VALUES ('site_name','$site_name','shop_info'),('site_url','$site_url','shop_info'),('site_keywords','$site_keywords','shop_info'),('site_desc','$site_desc','shop_info')";
                mysql_query($sql);
                */
                //插入管理员表字段tp_admin表
                $time = time();
                $ip = get_client_ip();
                $ip = empty($ip) ? "127.0.0.1" : $ip;
                $password = strtoupper(md5(trim($password) . 'TY'));
                if (mysqli_query($conn, "select * from `{$dbPrefix}admin` where user_name='admin'")) {
                    mysqli_query($conn, "delete from `{$dbPrefix}admin` where user_name ='admin'");
                }
                mysqli_query($conn, " insert  into `{$dbPrefix}admin` (`user_name`,`email`,`password`,`ec_salt`,`add_time`,`last_ip`,`nav_list`,`role_id`) values ('$username','$email','$password','男','$time','$ip','管理员','1')");
                $message = '<span class="correct_span">&radic;</span>成功添加管理员<br /><span class="correct_span">&radic;</span>成功写入配置文件<br/><span style="color: #00B83F;font-size: 15px">-----安装完成</span>';
                $arr = array('n' => 999999, 'msg' => $message);
                echo json_encode($arr);
                exit;
            }
            include_once("./templates/step4.php");
            exit();
        case '5':
            $url = json_decode(fileRead(FILE . md5('email') . '.log'))->url;
            include_once("./templates/step5.php");
            @touch('./install.lock');
            exit();
    }
}

function fileRead($filePath)
{
    $handle = fopen($filePath, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
    //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
    $contents = fread($handle, filesize($filePath));
    fclose($handle);
    return $contents;
}

function fileWrite($file_path, $content)
{
    header("Content-Type:text/html;Charset=utf-8");
    $fp = fopen($file_path, "w+");
    fwrite($fp, $content);
    fclose($fp);
}

function testwrite($d)
{
    $tfile = "_test.txt";
    $fp = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}


function sql_split($sql, $tablepre)
{

    if ($tablepre != "tp_")
        $sql = str_replace("tp_", $tablepre, $sql);

    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-' && $str1 != '/')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}

function _dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

// 获取客户端IP地址
function get_client_ip()
{
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '127.0.0.1';
    return $ip;
}

function dir_create($path, $mode = 0777)
{
    if (is_dir($path))
        return TRUE;
    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function sp_password($pw, $pre)
{
    $decor = md5($pre);
    $mi = md5($pw);
    return substr($decor, 0, 12) . $mi . substr($decor, -4, 4);
}

function sp_random_string($len = 8)
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

