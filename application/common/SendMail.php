<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/24 0024
 * Time: 下午 2:49
 */

namespace app\common;

use PHPMailer\PHPMailer;
use think\Exception;

class SendMail
{
    private static $s;

    public static function install()
    {
        if (self::$s == null) {
            self::$s = new SendMail();
        }
        return self::$s;
    }

    /**
     * @param $sendto
     * @param $subject
     * @param $content
     * @param $CC
     * @return bool
     * 发送邮件【注意要单独安装sendMail控件】
     */
    public function sendMessage($sendto, $subject, $content, $CC)
    {
        if ($CC != false) {
            $headers = "From: <tengyujsj@sina.com>" . "\r\n" . "Cc: $CC";
        } else {
            $headers = "From: <tengyujsj@sina.com>";
        }

        if (mail($sendto, $subject, $content, $headers)) {
            unset($headers);
            return true;
        } else {
            unset($headers);
            return $this->phpMailer($sendto, $subject, $content);
        }
    }

    /**
     * @param $sendto
     * @param $subject
     * @param $content
     * @return bool
     * 发送邮件
     */
    public function phpMailer($sendto, $subject, $content)
    {
        $mail = new PHPMailer(true);// 传递“真”允许例外
        try {
            //Server settings
//            $mail->SMTPDebug = 2;// Enable verbose debug output
            $mail->isSMTP();// Set mailer to use SMTP
            $mail->Host = 'smtp.sina.com';// 发送方的SMTP服务器地址
            $mail->SMTPAuth = true;// Enable SMTP authentication
            $mail->Username = 'tengyujsj@sina.com';// SMTP username
            $mail->Password = 'tengyuJSJ689';// SMTP password
            $mail->SMTPSecure = 'ssl';// Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465; // TCP port to connect to
            $mail->CharSet = "UTF-8";// 设置邮件编码
            //Recipients
            $mail->setFrom('tengyujsj@sina.com', $subject);
            $mail->addAddress($sendto, $sendto);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
            //$mail->addReplyTo('gs@tengyujsj.com', '腾宇计算机总部');//设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
            //$mail->addCC("aaaa@inspur.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址
            //$mail->addBCC("bbbb@163.com");// 设置秘密抄送人
            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments添加附件
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name可选的名字
            //Content
            //$mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = '本邮件由青岛腾宇计算机发送';
            $mail->Body = $content;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用
            if ($mail->send()) {
                unset($mail);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo '{"code":400,"message":"NO","data":null}';
        }
    }

    /**
     * @param $mobile
     * @param $content
     * 发送短信
     */
    public function send_sms($mobile, $content)
    {//短信验证码
        header("Content-Type: text/html; charset=UTF-8");
        $flag = 0;
        $params = '';//要post的数据
        //$verify = rand(100000, 999999);//获取随机验证码
        //以下信息自己填以下
        //$mobile='';//手机号
        $argv = array(
            'name' => 'dxwzzy',     //必填参数。用户账号
            'pwd' => 'C51E7F585764DAECF71FF62520E3',     //必填参数。（web平台：基本资料中的接口密码）
            'content' => $content,
            //'content'=>'掌中游短信验证码为：'.$verify.'，请勿将验证码提供给他人。',   //必填参数。发送内容（1-500 个汉字）UTF-8编码
            'mobile' => $mobile,   //必填参数。手机号码。多个以英文逗号隔开
            'stime' => '',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
            'sign' => 'dxwzzy',    //必填参数。用户签名。
            'type' => 'pt',  //必填参数。固定值 pt
            'extno' => ''    //可选参数，扩展码，用户定义扩展码，只能为数字
        );
        //print_r($argv);exit;
        //构造要post的字符串
        //echo $argv['content'];
        foreach ($argv as $key => $value) {
            if ($flag != 0) {
                $params .= "&";
                $flag = 1;
            }
            $params .= $key . "=";
            $params .= urlencode($value);// urlencode($value);
            $flag = 1;
        }
        $url = "http://web.duanxinwang.cc/asmx/smsservice.aspx?" . $params; //提交的url地址
        echo $url;
        $con = substr(file_get_contents($url), 0, 1);  //获取信息发送后的状态
        if ($con == '0') {
            //return true;
            echo "<script>alert('发送成功!');</script>";
        } else {
            //return false;
            echo "<script>alert('发送失败!');</script>";
        }
    }

}