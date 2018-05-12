<?php

namespace app\common;
class StringHandle
{
    private static $ins;

    public static function install()
    {
        if (is_null(self::$ins)) {
            self::$ins = new StringHandle();
        }
        return self::$ins;
    }

    /**
     * 截取字符串无乱码
     * @param $str
     * @param $len
     * @return string
     */
    public function utf8sub($str, $len)
    {
        if ($len <= 0) {
            return '';
        }
        $length = strlen($str); //待截取的字符串字节数
        // 先取字符串的第一个字节,substr是按字节来的
        $offset = 0; // 这是截取高位字节时的偏移量
        $chars = 0; // 这是截取到的字符数
        $res = ''; // 这是截取的字符串
        while ($chars < $len && $offset < $length) { //只要还没有截取到$len的长度,就继续进行
            $high = decbin(ord(substr($str, $offset, 1))); // 重要突破,已经能够判断高位字节
            if (strlen($high) < 8) {
                // 截取1个字节
                $count = 1;
            } else if (substr($high, 0, 3) == '110') {
                // 截取2个字节
                $count = 2;
            } else if (substr($high, 0, 4) == '1110') {
                // 截取3个字节
                $count = 3;
            } else if (substr($high, 0, 5) == '11110') {
                // 截取4个字节
                $count = 4;
            } else if (substr($high, 0, 6) == '111110') {
                // 截取5个字节
                $count = 5;
            } else if (substr($high, 0, 7) == '1111110') {
                // 截取6个字节
                $count = 6;
            }
            $res .= substr($str, $offset, $count);
            $chars += 1;
            $offset += $count;
        }
        return $res;
    }

    /**
     * @param $user_name
     * @return string
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     */
    public function cut_str($user_name)
    {
        $len = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        return $len == 3 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $len - 3) . $lastStr;
    }

    /**
     * @desc 获取汉字的首拼音字母
     * @param string $str
     * @return string|NULL
     */
    public function getFirstEnglish($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});

        $gb2312String = iconv('UTF-8', 'gb2312', $str);

        $UTF_8 = iconv('gb2312', 'UTF-8', $gb2312String);

        $string = $UTF_8 == $str ? $gb2312String : $str;

        $ascII = ord($string{0}) * 256 + ord($string{1}) - 65536;
        if ($ascII >= -20319 && $ascII <= -20284) return 'A';
        if ($ascII >= -20283 && $ascII <= -19776) return 'B';
        if ($ascII >= -19775 && $ascII <= -19219) return 'C';
        if ($ascII >= -19218 && $ascII <= -18711) return 'D';
        if ($ascII >= -18710 && $ascII <= -18527) return 'E';
        if ($ascII >= -18526 && $ascII <= -18240) return 'F';
        if ($ascII >= -18239 && $ascII <= -17923) return 'G';
        if ($ascII >= -17922 && $ascII <= -17418) return 'H';
        if ($ascII >= -17417 && $ascII <= -16475) return 'J';
        if ($ascII >= -16474 && $ascII <= -16213) return 'K';
        if ($ascII >= -16212 && $ascII <= -15641) return 'L';
        if ($ascII >= -15640 && $ascII <= -15166) return 'M';
        if ($ascII >= -15165 && $ascII <= -14923) return 'N';
        if ($ascII >= -14922 && $ascII <= -14915) return 'O';
        if ($ascII >= -14914 && $ascII <= -14631) return 'P';
        if ($ascII >= -14630 && $ascII <= -14150) return 'Q';
        if ($ascII >= -14149 && $ascII <= -14091) return 'R';
        if ($ascII >= -14090 && $ascII <= -13319) return 'S';
        if ($ascII >= -13318 && $ascII <= -12839) return 'T';
        if ($ascII >= -12838 && $ascII <= -12557) return 'W';
        if ($ascII >= -12556 && $ascII <= -11848) return 'X';
        if ($ascII >= -11847 && $ascII <= -11056) return 'Y';
        if ($ascII >= -11055 && $ascII <= -10247) return 'Z';
        return null;
    }



    /**
     * 获取关键词
     * @return array
     */
    public static function search_word_from()
    {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (strstr($referer, 'baidu.com')) { //百度
            preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'baidu';
        } elseif (strstr($referer, 'google.com') or strstr($referer, 'google.cn')) { //谷歌
            preg_match("|google.+q=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'google';
        } elseif (strstr($referer, 'so.com')) { //360搜索
            preg_match("|so.+q=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '360';
        } elseif (strstr($referer, 'sogou.com')) { //搜狗
            preg_match("|sogou.com.+query=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'sogou';
        } elseif (strstr($referer, 'soso.com')) { //搜搜
            preg_match("|soso.com.+w=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'soso';
        } else {
            $keyword = '';
            $from = '';
        }
        return array('keyword' => $keyword, 'from' => $from);
    }

    /**
     * 从数组删除数据
     * @param array $data
     * @param $unKey //要删除的键
     * @return array
     */
    public function unsetDataByKey(array &$data, array $unKey)
    {
        if (empty($data) || empty($unKey) || !is_array($data) || !is_array($unKey)) {
            return array();
        }

        foreach ($unKey as $key => $value) {
            if (!array_key_exists($value, $data)) {
                continue;
            }
            unset($data[$value]);
        }
        return $data;
    }
}