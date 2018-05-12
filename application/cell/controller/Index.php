<?php

namespace app\cell\controller;

use think\Controller;

class Index extends Controller
{
    //首页
    public function index()
    {
        return $this->fetch('/indexs');
    }

//上传
    public function upload()
    {
        $file = $_FILES['file'];
        //判断是http post 上传
        if (is_uploaded_file($file['tmp_name'])) {
            //获取文件名
            $fname = getRandomNnm() . stristr($file['name'], '.');
            $path = realpath('../') . DS . 'public\excel';
            if (is_dir($path)) {
                move_uploaded_file($file['tmp_name'], $path . DS . $fname);
                @unlink($file['tmp_name']);
            } else {
                mkdir($path);
                move_uploaded_file($file['tmp_name'], $path . DS . $fname);
                @unlink($file['tmp_name']);
            }

        } else {
            echo 'error';
        }

    }

//下载
    public function download()
    {
       $count=0;
        $paths = realpath('../') . DS . 'public\tmp';
        if(!file_exists($paths . DS . '0_000.vcf')){
            echo "<script>alert('文件没有上传')</script>";
            echo "<script>window.location='/cell/index/index';</script>";
        }
        $path = realpath('../') . DS . 'public\excel';
        $file = scandir($path)[2];
        $arr = $this->getExcel($path . DS . $file);
        $content = "";
        foreach ($arr as $key => $val) {
            $count = $key;
            $content .= $this->print_str($val['A'], $val['B']);
        }
        $content = substr($content, 0, strlen($content) - 1);
        if (!file_exists($paths)) {
            mkdir($paths);
            file_put_contents($paths . DS . '0_000.vcf', $content);
        } else {
            file_put_contents($paths . DS . '0_000.vcf', $content);
        }
        if (file_exists($paths . DS . '0_000.vcf')) {
            echo $this->downFile($paths . DS . '0_000.vcf');
            unlink($paths . DS . '0_000.vcf');
            $this->deleteDirAndFile($path);
        } else {
            $a='转化完成：'.(round($count / count($arr)) * 100) . "%";
            echo "<script>alert('{$a}')</script>";
            echo "<script>window.location='/cell/index/index';</script>";
        }
        exit;
    }

    private function print_str($name, $phone)
    {
        $str = "BEGIN:VCARD" . "\n";
        $str .= "VERSION:3.0" . "\n";
        $str .= "N;CHARSET=UTF-8:" . $name . "\n";
        $str .= "FN;CHARSET=UTF-8:" . $name . "\n";
        $str .= "TEL;TYPE=HOME:" . $this->format_phone($phone) . "\n";
        $str .= "END:VCARD" . "\n";
        return $str;
    }

    private function format_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $temp = "";
        if (strlen($phone) == 11) {
            $temp .= $phone[0] . "-";
            for ($v = 1; $v < 4; $v++) {
                $temp .= $phone[$v];

            }
            $temp .= "-";
            for ($v = 4; $v < 7; $v++) {
                $temp .= $phone[$v];

            }
            $temp .= "-";
            for ($v = 7; $v < 11; $v++) {
                $temp .= $phone[$v];

            }
            return $temp;
        } else {
            return $phone;
        }


    }

    private function getRandomNnm()
    {
        return str_pad(mt_rand(0, 99999999), 8, "0", STR_PAD_BOTH);
    }

    private function getExcel($file = '', $sheet = 0)
    {
        $file = iconv("utf-8", "gb2312", $file);   //转码
        if (empty($file) OR !file_exists($file)) {
            die('file not exists!');
        }
        vendor('phpexcel.PHPExcel'); //引入PHP EXCEL类
        try {
            $objRead = new \PHPExcel_Reader_Excel2007();   //建立reader对象
            if (!$objRead->canRead($file)) {
                $objRead = new \PHPExcel_Reader_Excel5();
                if (!$objRead->canRead($file)) {
                    die('No Excel!');
                }
            }
            $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
            $obj = $objRead->load($file);  //建立excel对象
            $currSheet = $obj->getSheet($sheet);   //获取指定的sheet表
            $columnH = $currSheet->getHighestColumn();   //取得最大的列号
            $columnCnt = array_search($columnH, $cellName);
            $rowCnt = $currSheet->getHighestRow();   //获取总行数

            $data = array();
            for ($_row = 1; $_row <= $rowCnt; $_row++) {  //读取内容
                for ($_column = 0; $_column <= $columnCnt; $_column++) {
                    $cellId = $cellName[$_column] . $_row;
                    $cellValue = $currSheet->getCell($cellId)->getValue();
                    //$cellValue = $currSheet->getCell($cellId)->getCalculatedValue();  #获取公式计算的值
                    if ($cellValue instanceof PHPExcel_RichText) {   //富文本转换字符串
                        $cellValue = $cellValue->__toString();
                    }

                    $data[$_row][$cellName[$_column]] = $cellValue;
                }
            }
            return $data;
        } catch (\PHPExcel_Exception $e) {
            exit('fail');
        }
    }

    private function deleteDirAndFile($path, $delDir = false)
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

    private function downFile($file_path)
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
}