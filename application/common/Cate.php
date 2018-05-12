<?php

namespace app\common;
class Cate
{

    private static $cate;

    public static function install()
    {
        if (self::$cate == null) {
            self::$cate = new Cate();
        }
        return self::$cate;
    }

//获取连接
    public function getConnect()
    {
        $c = @mysqli_connect(config("database.hostname"), config("database.username"), config("database.password"));
        if ($c) {
            mysqli_select_db($c, config("database.database"));
            return $c;
        } else {
            exit('connect fail');
        }
    }

    //获取排序文件所有分类
    public function getFileCate($conn, $tableName, $where)
    {
        $sql = "select id,pid,name from $tableName where $where order by id asc";
        $res = mysqli_query($conn, $sql);
        $result = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $row['name'] = '' . $row['name'];
            $result[] = $row;
        }
        return $result;

    }

    //获取排序所有分类
    public function getAllCate($conn, $tableName, $where)
    {
        $sql = "select id,pid,name,path,level,concat(path, ',' ,id) as paths from $tableName where $where order by path asc";
        $res = mysqli_query($conn, $sql);
        $result = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $row['name'] = str_repeat("|------", $row['level']) . $row['name'];
            $result[] = $row;
        }
        return $result;

    }

    //获取查询/打开路径
    public function getPathCate($conn, $tableName, $cateId = 1)
    {
        if (!is_numeric($cateId)) {
            exit;
        }
        $sql = "select *,concat(path,',',id) paths from $tableName WHERE id=$cateId";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        $ids = trim($row['paths'], ',');
        $sql = "select * from $tableName WHERE id in ($ids) ORDER BY id ASC";
        $re = mysqli_query($conn, $sql);
        $result = array();
        while ($row = mysqli_fetch_assoc($re)) {
            $result[] = $row;
        }
        return $result;
    }

    //添加分类
    public function addAllCate($cate, $data, $url)
    {

    }
}