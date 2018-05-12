<?php

namespace app\common;

use \COM;


class ServerInfo
{
    private static $server;

    public static function install()
    {
        if (self::$server == null) {
            self::$server = new ServerInfo();
        }
        return self::$server;
    }

    /**
     * @return bool|mixed
     * 根据不同系统取得CPU相关信息
     */
    public function getSystemInfo()
    {
        switch (PHP_OS) {
            case "Linux":
                $info = $this->sys_linux();
                break;
            case "FreeBSD":
                $info = $this->sys_freebsd();
                break;
            case "WINNT":
                $info = $this->sys_windows();
                break;
            default:
                break;
        }
        return $info;
    }

    public function GetWMI($wmi, $strClass, $strValue = array())
    {
        $value = '';
        $arrData = array();
        $objWEBM = $wmi->Get($strClass);
        $arrProp = $objWEBM->Properties_;
        $arrWEBMCol = $objWEBM->Instances_();
        foreach ($arrWEBMCol as $objItem) {
            @reset($arrProp);
            $arrInstance = array();
            foreach ($arrProp as $propItem) {
                eval('$value=$objItem->' . $propItem->Name . ";");
                if (empty($strValue)) {
                    $arrInstance[$propItem->Name] = trim($value);
                } else {
                    if (in_array($propItem->Name, $strValue)) {
                        $arrInstance[$propItem->Name] = trim($value);
                    }
                }
            }
            $arrData[] = $arrInstance;
        }
        return $arrData;
    }

    //windows系统探测

    function sys_windows()
    {
        $objLocator = new COM("WbemScripting.SWbemLocator");
        $wmi = $objLocator->ConnectServer();
//        $prop = $wmi->get("Win32_PnPEntity");
        //CPU
        $cpuinfo = $this->GetWMI($wmi, "Win32_Processor", array("Name", "L2CacheSize", "NumberOfCores"));
        $res['cpu']['num'] = $cpuinfo[0]['NumberOfCores'];
        if (null == $res['cpu']['num']) {
            $res['cpu']['num'] = 1;
        }
        for ($i = 0; $i < $res['cpu']['num']; $i++) {
            $res['cpu']['model'] = $cpuinfo[0]['Name'] . "<br>";
            $res['cpu']['cache'] = $cpuinfo[0]['L2CacheSize'] . "<br>";
        }
        // SYSINFO
        $sysinfo = $this->GetWMI($wmi, "Win32_OperatingSystem", array('LastBootUpTime', 'TotalVisibleMemorySize', 'FreePhysicalMemory', 'Caption', 'CSDVersion', 'SerialNumber', 'InstallDate'));
        $res['win_n'] = $sysinfo[0]['Caption'] . " " . $sysinfo[0]['CSDVersion'] . " <span>序列号</span>:{$sysinfo[0]['SerialNumber']} 于" . date('Y年m月d日H:i:s', strtotime(substr($sysinfo[0]['InstallDate'], 0, 14))) . "安装";
        //UPTIME
        $res['uptime'] = $sysinfo[0]['LastBootUpTime'];
        $sys_ticks = 3600 * 8 + time() - strtotime(substr($res['uptime'], 0, 14));
        $min = $sys_ticks / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));
        if ($days !== 0) $res['uptime'] = $days . "天";
        if ($hours !== 0) $res['uptime'] .= $hours . "小时";
        $res['uptime'] .= $min . "分钟";
        //MEMORY
        $res['memTotal'] = $sysinfo[0]['TotalVisibleMemorySize'];
        $res['memFree'] = $sysinfo[0]['FreePhysicalMemory'];
        $res['memUsed'] = $res['memTotal'] - $res['memFree'];
        $res['memPercent'] = round($res['memUsed'] / $res['memTotal'] * 100, 2);
        $swapinfo = $this->GetWMI($wmi, "Win32_PageFileUsage", array('AllocatedBaseSize', 'CurrentUsage'));
        //swp区获取
        $res['swapTotal'] = $swapinfo[0]['AllocatedBaseSize'];
        $res['swapUsed'] = $swapinfo[0]['CurrentUsage'];
        $res['swapFree'] = $res['swapTotal'] - $res['swapUsed'];
        $res['swapPercent'] = (floatval($res['swapTotal']) != 0) ? round($res['swapUsed'] / $res['swapTotal'] * 100, 2) : 0;
        // LoadPercentage
        $loadinfo = $this->GetWMI($wmi, "Win32_Processor", array("LoadPercentage"));
        $res['loadAvg'] = $loadinfo[0]['LoadPercentage'];
        return $res;

    }

    /**
     * @return bool
     * linux系统探测
     */
    function sys_linux()
    {
        // CPU
        if (false === ($str = @file("/proc/cpuinfo"))) return false;
        $str = implode("", $str);

        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);

        @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);

        @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);

        @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);

        if (false !== is_array($model[1])) {
            $res['cpu']['num'] = sizeof($model[1]);
            for($i = 0; $i < $res['cpu']['num']; $i++)
            {

                $res['cpu']['model'][] = $model[1][$i].'&nbsp;('.$mhz[1][$i].')';

                $res['cpu']['mhz'][] = $mhz[1][$i];

                $res['cpu']['cache'][] = $cache[1][$i];

                $res['cpu']['bogomips'][] = $bogomips[1][$i];

            }
            if ($res['cpu']['num'] == 1)
                $x1 = '';
            else
                $x1 = ' ×' . $res['cpu']['num'];
            $mhz[1][0] = ' | 频率:' . $mhz[1][0];
            $cache[1][0] = ' | 二级缓存:' . $cache[1][0];
            $bogomips[1][0] = ' | Bogomips:' . $bogomips[1][0];
            $res['cpu']['model'][] = $model[1][0] . $mhz[1][0] . $cache[1][0] . $bogomips[1][0] . $x1;

            if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);

            if (false !== is_array($res['cpu']['mhz'])) $res['cpu']['mhz'] = implode("<br />", $res['cpu']['mhz']);

            if (false !== is_array($res['cpu']['cache'])) $res['cpu']['cache'] = implode("<br />", $res['cpu']['cache']);

            if (false !== is_array($res['cpu']['bogomips'])) $res['cpu']['bogomips'] = implode("<br />", $res['cpu']['bogomips']);

        }

        // NETWORK
        // UPTIME
        if (false === ($str = @file("/proc/uptime"))) return false;

        $str = explode(" ", implode("", $str));

        $str = trim($str[0]);

        $min = $str / 60;

        $hours = $min / 60;

        $days = floor($hours / 24);

        $hours = floor($hours - ($days * 24));

        $min = floor($min - ($days * 60 * 24) - ($hours * 60));

        if ($days !== 0) $res['uptime'] = $days . "天";

        if ($hours !== 0) $res['uptime'] .= $hours . "小时";

        $res['uptime'] .= $min . "分钟";
        // MEMORY
        if (false === ($str = @file("/proc/meminfo"))) return false;
        $str = implode("", $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);
        $res['memTotal'] = round($buf[1][0] / 1024, 2);
        $res['memFree'] = round($buf[2][0] / 1024, 2);
        $res['memBuffers'] = round($buffers[1][0] / 1024, 2);
        $res['memCached'] = round($buf[3][0] / 1024, 2);
        $res['memUsed'] = $res['memTotal'] - $res['memFree'];
        $res['memPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memUsed'] / $res['memTotal'] * 100, 2) : 0;
        $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
        $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0; //真实内存使用率

        $res['memCachedPercent'] = (floatval($res['memCached']) != 0) ? round($res['memCached'] / $res['memTotal'] * 100, 2) : 0; //Cached内存使用率

        $res['swapTotal'] = round($buf[4][0] / 1024, 2);

        $res['swapFree'] = round($buf[5][0] / 1024, 2);

        $res['swapUsed'] = round($res['swapTotal'] - $res['swapFree'], 2);

        $res['swapPercent'] = (floatval($res['swapTotal']) != 0) ? round($res['swapUsed'] / $res['swapTotal'] * 100, 2) : 0;
        // LOAD AVG
        if (false === ($str = @file("/proc/loadavg"))) return false;

        $str = explode(" ", implode("", $str));

        $str = array_chunk($str, 4);

        $res['loadAvg'] = implode(" ", $str[0]);


        return $res;

    }

    /**
     * @return bool
     * FreeBSD系统探测
     */
    function sys_freeBsd()
    {

        //CPU

        if (false === ($res['cpu']['num'] = get_key("hw.ncpu"))) return false;

        $res['cpu']['model'] = get_key("hw.model");

        //LOAD AVG

        if (false === ($res['loadAvg'] = get_key("vm.loadavg"))) return false;

        //UPTIME

        if (false === ($buf = get_key("kern.boottime"))) return false;

        $buf = explode(' ', $buf);

        $sys_ticks = time() - intval($buf[3]);

        $min = $sys_ticks / 60;

        $hours = $min / 60;

        $days = floor($hours / 24);

        $hours = floor($hours - ($days * 24));

        $min = floor($min - ($days * 60 * 24) - ($hours * 60));

        if ($days !== 0) $res['uptime'] = $days . "天";

        if ($hours !== 0) $res['uptime'] .= $hours . "小时";

        $res['uptime'] .= $min . "分钟";

        //MEMORY

        if (false === ($buf = get_key("hw.physmem"))) return false;

        $res['memTotal'] = round($buf / 1024 / 1024, 2);


        $str = get_key("vm.vmtotal");

        preg_match_all("/\nVirtual Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buff, PREG_SET_ORDER);

        preg_match_all("/\nReal Memory[\:\s]*\(Total[\:\s]*([\d]+)K[\,\s]*Active[\:\s]*([\d]+)K\)\n/i", $str, $buf, PREG_SET_ORDER);


        $res['memRealUsed'] = round($buf[0][2] / 1024, 2);

        $res['memCached'] = round($buff[0][2] / 1024, 2);

        $res['memUsed'] = round($buf[0][1] / 1024, 2) + $res['memCached'];

        $res['memFree'] = $res['memTotal'] - $res['memUsed'];

        $res['memPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memUsed'] / $res['memTotal'] * 100, 2) : 0;


        $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0;


        return $res;

    }

    /**
     * @return string
     * 获得访客浏览器类型
     */
    function GetBrowser()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $br)) {
                $br = 'MSIE';
            } elseif (preg_match('/Firefox/i', $br)) {
                $br = 'Firefox';
            } elseif (preg_match('/Chrome/i', $br)) {
                $br = 'Chrome';
            } elseif (preg_match('/Safari/i', $br)) {
                $br = 'Safari';
            } elseif (preg_match('/Opera/i', $br)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
            return $br;
        } else {
            return "获取浏览器信息失败！";
        }
    }

    /**
     * @return bool|string
     * 获得访客浏览器语言
     */
    function GetLang()
    {
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang, 0, 5);
            if (preg_match("/zh-cn/i", $lang)) {
                $lang = "简体中文";
            } elseif (preg_match("/zh/i", $lang)) {
                $lang = "繁体中文";
            } else {
                $lang = "English";
            }
            return $lang;

        } else {
            return "获取浏览器语言失败！";
        }
    }

    /**
     * @return string
     * 获取访客操作系统
     */
    public function GetOs()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $OS = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i', $OS)) {
                $OS = 'Windows';
            } elseif (preg_match('/mac/i', $OS)) {
                $OS = 'MAC';
            } elseif (preg_match('/linux/i', $OS)) {
                $OS = 'Linux';
            } elseif (preg_match('/unix/i', $OS)) {
                $OS = 'Unix';
            } elseif (preg_match('/bsd/i', $OS)) {
                $OS = 'BSD';
            } else {
                $OS = 'Other';
            }
            return $OS;
        } else {
            return "获取访客操作系统信息失败！";
        }
    }

    /**
     * @param string $ip
     * @return string
     * 根据ip获得访客所在地地名
     */
    public function GetAddress($ip = '')
    {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $ipadd = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=" . $ip);//根据新浪api接口获取
        if ($ipadd) {
            $charset = iconv("gbk", "utf-8", $ipadd);
            preg_match_all("/[\x{4e00}-\x{9fa5}]+/u", $charset, $ipadds);

            return $ipadds;   //返回一个二维数组
        } else {
            return "addree is none";
        }
    }

    /**
     * @return string
     * 以编译模块
     */
    public function phpCompile()
    {
        $able = get_loaded_extensions();
        $str = '';
        foreach ($able as $key => $value) {
            if ($key != 0 && $key % 13 == 0) {
                $str .= '<br />';
            }
            $str .= "$value&nbsp;&nbsp;";
        }
        return $str;
    }

    /**
     * @param $varName
     * @return string
     * 检测PHP设置参数
     */
    public function show($varName)
    {
        switch ($result = get_cfg_var($varName)) {
            case 0:
                return '<i style="color: red">×</i>';
                break;
            case 1:
                return '<i style="color: green">√</i>';
                break;
            default:
                return $result;
                break;
        }
    }

    /**
     * @param string $funName
     * @param int $j
     * @return string
     * 检测函数支持
     */
    public function isFun($funName = '', $j = 0)
    {
        if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return '错误';
        if (!$j) {
            return (function_exists($funName) !== false) ? '<i style="color: green">√</i>' : '<i style="color: red">×</i>';
        } else {
            return (function_exists($funName) !== false) ? '√' : '×';
        }
    }

    /**
     * @return array
     * 获取zend的信息
     */
    public function GetZendInfo()
    {
        $zendInfo = array();
        $zendInfo['ver'] = zend_version() ? zend_version() : '<i style="color: red">×</i>';
        $phpv = substr(PHP_VERSION, 2, 1);
        $zendInfo['loader'] = $phpv > 2 ? 'ZendGuardLoader[启用]' : 'Zend Optimizer';
        if ($phpv > 2) {
            $zendInfo['html'] = get_cfg_var("zend_loader.enable") ? '<i style="color: green">√</i>' : '<i style="color: red">×</i>';
        } elseif (function_exists('zend_optimizer_version')) {
            $zendInfo['html'] = zend_optimizer_version();
        } else {
            $zendInfo['html'] = (get_cfg_var("zend_optimizer.optimization_level") ||
                get_cfg_var("zend_extension_manager.optimizer_ts") ||
                get_cfg_var("zend.ze1_compatibility_mode") ||
                get_cfg_var("zend_extension_ts")) ? '<i style="color: green">√</i>' : '<i style="color: red">×</i>';
        }
        return $zendInfo;
    }

    /**
     * @param $bit
     * @return string
     * 文件单位转换
     */
    public function unitConversion($bit)
    {
        $type = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $bit >= 1024; $i++)//单位每增大1024，则单位数组向后移动一位表示相应的单位
        {
            $bit /= 1024;
        }
        return (floor($bit * 100) / 100) . $type[$i];//floor是取整函数，为了防止出现一串的小数，这里取了两位小数
    }

    /**
     * 获取硬盘的内存使用情况
     */
    public function getDiskMemory()
    {
        $disk = array();
        $tm=@disk_total_space(".");
        $fm=@disk_free_space(".");
        $disk['total'] = $this->unitConversion($tm);
        $disk['free'] = $this->unitConversion($fm);
        $disk['proportion'] = ceil(($fm/$tm)*100);
        return $disk;
    }
}