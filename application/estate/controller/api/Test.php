<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/7/5
 * Time: 11:54
 */
namespace app\estate\controller\api;

use think\Controller;
use think\Db;
use my\smtp;
class Test extends Controller {


    private function test() {
        $id = input('param.id',20);
        try {
            $exist = Db::table('mp_appoint')->where('id','=',$id)->find();
            if(!$exist) {
                die();
            }
            $resource = Db::table('mp_resource')->where('id','=',$exist['res_id'])->find();
            $user = Db::table('mp_user')->where('id','=',$exist['uid'])->find();
            $res_name = $resource['name'];

            $data['tel'] = $user['tel'];
            $data['name'] = $user['realname'];
            $data['to_tel'] = $exist['tel'];
            $data['to_name'] = $exist['name'];
            $data['title'] = $res_name.'案场报备';
            $data['email'] = $resource['email'];
            $data['meeting_date'] = $exist['meeting_date'];

            //使用163邮箱服务器
            $smtpserver = "smtp.163.com";
//163邮箱服务器端口
            $smtpserverport = 465;
//你的163服务器邮箱账号
            $smtpusermail = "git_smtp@163.com";
//收件人邮箱
            $smtpemailto = $data['email'];

//你的邮箱账号(去掉@163.com)
            $smtpuser = "git_smtp";//你的163邮箱去掉后面的163.com
//你的邮箱密码
            $smtppass = "jiang22513822"; //你的163邮箱SMTP的授权码，千万不要填密码！！！

//邮件主题
            $mailsubject = $data['title'];
//邮件内容
            $mailbody = '报备案场: ' . $data['title'] . '<br>';
            $mailbody .= '报备ID: ' . $exist['id'] . '<br>';
            $mailbody .= '报备人手机号: ' . $data['tel'] . '<br>';
            $mailbody .= '报备人姓名: ' . $data['name'] . '<br>';
            $mailbody .= '客户手机号: ' . $data['to_tel'] . '<br>';
            $mailbody .= '客户姓名: ' . $data['to_name'] . '<br>';
            $mailbody .= '预约时间: ' . $data['meeting_date'] . '<br>';
            $mailbody .= '客户备注: ' . $exist['desc'] . '<br>';
        } catch(\Exception $e) {
            die($e->getMessage());
        }

//邮件格式（HTML/TXT）,TXT为文本邮件
        $mailtype = "HTML";
//这里面的一个true是表示使用身份验证,否则不使用身份验证.
        $smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
//是否显示发送的调试信息
        $smtp->debug = false;
//发送邮件
        $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);

    }


}
