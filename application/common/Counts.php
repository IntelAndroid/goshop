<?php

namespace app\common;
class Counts
{
    private static $counts;


    /**
     * 初始化
     * @return Counts
     */
    public static function install()
    {
        if (self::$counts == null) {
            self::$counts = new Counts();
        }
        return self::$counts;
    }

    /**
     * 获取在线人数
     * @param $fileName
     * @return int
     */
    public function getOnlineNumber($fileName)
    {
        createFile($fileName);
        $timeout = 30;//30秒内没动作者,认为掉线
        $entries = file($fileName);
        $temp = array();
        for ($i = 0; $i < count($entries); $i++) {
            $entry = explode(",", trim($entries[$i]));
            if (($entry[0] != getenv('REMOTE_ADDR')) && ($entry[1] > time())) {
                array_push($temp, $entry[0] . "," . $entry[1] . "\n"); //取出其他浏览者的信息,并去掉超时者,保存进$temp
            }
        }
        array_push($temp, getenv('REMOTE_ADDR') . "," . (time() + ($timeout)) . "\n"); //更新浏览者的时间
        $users_online = count($temp); //计算在线人数
        $entries = implode("", $temp);
        //写入文件
        $fp = fopen($fileName, "w");
        flock($fp, LOCK_EX); //flock() 不能在NFS以及其他的一些网络文件系统中正常工作
        fputs($fp, $entries);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $users_online;
    }

    /**
     * 获取统计
     * @param $fileName
     * @return array
     */
    public function getCount($fileName)
    {
        if (!is_readable($fileName)) {
            return ['click' => 0, 'today' => 0, 'yesterday' => 0, 'lastweek' => 0, 'thisweek' => 0, 'thismonth' => 0];
        }
        //用户的点击次数
        $clickCount = 1;
        //今天访问量
        $todayCount = 1;
        //昨天
        $yesterdayCount = 1;
        //上周
        $lastWeekCount = 1;
        //本周
        $thisWeekCount = 1;
        //本月
        $thisMonthCount = 1;
        //输出信息：挡墙网页已经被第几次访问，当前用户是第几次来访问
        $str = file_get_contents($fileName);
        //获取今日开始时间戳和结束时间戳
        $today_start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $today_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        //获取昨日起始时间戳和结束时间戳
        $yesterday_start = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
        $yesterday_end = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
        //获取上周起始时间戳和结束时间戳
        $lastweek_start = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
        $lastweek_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
        //获取本周周起始时间戳和结束时间戳
        $thisweek_start = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y'));
        $thisweek_end = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y'));
        //获取本月起始时间戳和结束时间戳
        $thismonth_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $thismonth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        //判断当前有没有记录访问信息
        if ($str) {
            //有数据以行区分当前文件有多少行
            $rows = explode("\r\n", $str);
            //判断当前用户是第几次访问该网页
            foreach ($rows as $value) {
                //value代表一个访问记录
                $ip = explode("|", $value);
                //以前访问的记录与当前用户的ip相同
                $clickCount++;
                if ($ip[1] >= $today_start & $ip[1] <= $today_end) {
                    $todayCount++;
                }
                if ($ip[1] >= $yesterday_start & $ip[1] <= $yesterday_end) {
                    $yesterdayCount++;
                }
                if ($ip[1] >= $lastweek_start & $ip[1] <= $lastweek_end) {
                    $lastWeekCount++;
                }
                if ($ip[1] >= $thisweek_start & $ip[1] <= $thisweek_end) {
                    $thisWeekCount++;
                }
                if ($ip[1] >= $thismonth_start & $ip[1] <= $thismonth_end) {
                    $thisMonthCount++;
                }
            }
        }
        //输出信息
        if ($clickCount > 2000) {
            fileWrite($fileName, '');
        }
        return ['click' => $clickCount, 'today' => $todayCount, 'yesterday' => $yesterdayCount, 'lastweek' => $lastWeekCount, 'thisweek' => $thisWeekCount, 'thismonth' => $thisMonthCount];
    }

    /**
     *
     * 设置统计
     * @param $fileName
     */
    public function setCount($fileName)
    {
        createFile($fileName);
        //假设用户访问，得到IP地址
        $remote = $_SERVER['REMOTE_ADDR'];
        //拼凑要写入到文件的数据：ip|2014-8-19 10:24:15
        $write = $remote . '|' . time();
        //修改write
        if (!empty(file_get_contents($fileName))) {
            $write = "\r\n" . $write;
        }
        //写入数据
        file_put_contents($fileName, $write, FILE_APPEND);
    }

    /**
     *
     * 获取一周的统计
     * @param $fileName
     * @param $time
     * @return array
     */
    public function getWeekCount($fileName,$time)
    {
        if (!is_readable($fileName)) {
            return ['w1'=>0,'w2'=>0,'w3'=>0,'w4'=>0,'w5'=>0,'w6'=>0,'w7'=>0,'sun' =>0];
        }
        $week = $this->get_weeks($time);
        $total=[ 'w1'=>1,'w2'=>1,'w3'=>1,'w4'=>1,'w5'=>1,'w6'=>1,'w7'=>1];
        $sum=1;
        $str = file_get_contents($fileName);
        if ($str) {
            //有数据以行区分当前文件有多少行
            $rows = explode("\r\n", $str);
            //获取总数
            $sum = count($rows);
            //输出信息删除
            if ($sum > 2000) {
                fileWrite($fileName, '');
            }
            foreach ($rows as $v) {
                //v代表一个访问记录
                $ip = explode("|", $v);
                for($i=0;$i<=6;$i++){
                    if ($ip[1] >= $week[$i]['start'] & $ip[1] <= $week[$i]['end']) {
                        $total['w'.($i+1)]++;
                    }
                }
            }
        }

        return ['sum'=>$sum,'w1' => $total['w1'], 'w2' => $total['w2'], 'w3' => $total['w3'], 'w4' => $total['w4'], 'w5' => $total['w5'], 'w6' => $total['w6'],'w7'=>$total['w7']];
    }
    /**
     * 上周
     */

    /**
     * 获取一周的开始与结束时间
     * @param $time
     * @return array
     */
    public function get_weeks($time)
    {
        $data = [];
        $week = date('w', $time);
        $name = array('星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日');
        //星期日排到末位
        if (empty($week)) {
            $week = 7;
        }
        for ($i = 0; $i <= 6; $i++) {
            $data[$i]['start'] = mktime(0, 0, 0, date('m'), date('d') - ($week - ($i + 1)), date('Y'));
            $data[$i]['end'] = mktime(0, 0, 0, date('m'), date('d') - ($week - ($i + 2)), date('Y')) - 1;
            $data[$i]['week'] = $name[$i];
        }
        return $data;
    }
}