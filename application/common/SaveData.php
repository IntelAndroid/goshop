<?php

namespace app\common;

use think\Cache;
use think\Db;
use think\exception\DbException;

class SaveData
{
    private static $saveData;

    /**
     * 初始化
     * @return SaveData
     */
    public static function install()
    {
        if (self::$saveData == null) {
            self::$saveData = new SaveData();
        }
        return self::$saveData;
    }


    /**
     * 设置间隔点击次数缓存
     * @param $callback
     * @param $cacheName
     * @param $numName
     * @return mixed
     */
    public function setSpanCache($callback, $cacheName, $numName)
    {
        $num = Cache::inc($numName);
        if (empty(Cache::get($cacheName))) {
            Cache::set($cacheName, serialize($callback()));
            return $callback();
        } else {
            $d = unserialize(Cache::get($cacheName));
            if ($num > 30) {
                Cache::set($numName, 0);
                Cache::set($cacheName, null);
            }
            return $d;
        }
    }

    /**
     * 数据库缓存内容变化时更新缓存
     * @param $table_name
     * @param string $id_name
     * @param $cache_name //缓存数据名
     * @param $cache_max_name //比较数据库最大id缓存名
     * @param $field //数据库字段
     * @param string $order //按字段排序条件
     * @param int $where //条件
     * @param int $start_index //开始索引位置
     * @param int $length //显示几个
     * @return array data //数据
     */
    public function sqlDataCache($table_name, $cache_name, $cache_max_name, $count_name, $field, $id_name = 'id', $order = 'id desc', $where = 1, $start_index = 0, $length = 6)
    {
        $max_index = Db::name($table_name)->max($id_name);//get max id
        $count = Db::name($table_name)->count();//get max id
        if (empty(Cache::get($cache_max_name))) {
            Cache::set($cache_max_name, $max_index); //save max id
            Cache::set($count_name, $count); //set count
            try {
                $re = Db::name($table_name)->field($field)->where($where)->order($order)->limit($start_index, $length)->select();
                if (is_object($re) || is_array($re)) {
                    Cache::set($cache_name, serialize($re));
                }
                return $re;
            } catch (DbException $e) {
                exit;
            };
        } else {
            if ($max_index != Cache::get($cache_max_name)) {
                Cache::set($cache_max_name, $max_index);
                Cache::set($count_name, $count); //set count
                try {
                    $re = Db::name($table_name)->field($field)->where($where)->order($order)->limit($start_index, $length)->select();
                    if (is_object($re) || is_array($re)) {
                        Cache::set($cache_name, serialize($re));
                    }
                    return $re;
                } catch (DbException $e) {
                    exit;
                };

            } else if ($count != Cache::get($count_name)) {
                Cache::set($cache_max_name, $max_index);
                Cache::set($count_name, $count); //set count
                try {
                    $re = Db::name($table_name)->field($field)->where($where)->order($order)->limit($start_index, $length)->select();
                    if (is_object($re) || is_array($re)) {
                        Cache::set($cache_name, serialize($re));
                    }
                    return $re;
                } catch (DbException $e) {
                    exit;
                };
            } else {
                return unserialize(Cache::get($cache_name));
            }
        }

    }

    /**
     * 保存数据并返回数据
     * @param $callback
     * @param $cacheName
     * @return array
     */
    public function setSingleCache($callback, $cacheName)
    {
        $cache = Cache::get($cacheName);
        if (empty($cache)) {
            $data = $callback();
            if (is_object($data) || is_array($data)) {
                Cache::set($cacheName, serialize($data));
            }
            return $data;
        } else {
            return unserialize(Cache::get($cacheName));
        }

    }

    /**
     * 获取缓存arr数据
     * @param $cacheName
     * @return mixed
     */
    public function getSingleCache($cacheName)
    {
        return unserialize(Cache::get($cacheName));
    }
}