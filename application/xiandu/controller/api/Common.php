<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/18
 * Time: 21:36
 */
namespace app\xiandu\controller\api;
use think\Controller;
use think\Db;
use think\exception\HttpResponseException;
class Common extends Controller {

    protected $cmd = '';
    protected $domain = '';
    protected $weburl = '';
    protected $mp_config = [];
    protected $myinfo = [];
    protected $rename_base_path;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->cmd = request()->module() . '/' .  request()->controller() . '/' . request()->action();
        $this->domain = 'www.caves.vip';
        $this->weburl = 'https://cave.jianghairui.com/';
        $this->rename_base_path = 'res/xiandu/api/';
        $this->mp_config = [
            'app_id' => 'wxcf0a69a03e0d6c0a',
            'secret' => 'a3a1402a7063c946ccf8ae7634bd010e',
            'mch_id'             => '1437814202',
            'key'                => 'xanaducn1234567890xanaducn123456',   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          =>  '/mnt/cave.jianghairui.com/public/cert/apiclient_cert.pem',
            'key_path'           =>  '/mnt/cave.jianghairui.com/cert/apiclient_key.pem',
            // 下面为可选项,指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => APP_PATH . '/wechat.log',
            ],
        ];
        $this->checkSession();
    }

    private function checkSession() {
        $noneed = [
            'Api.login/login',
            'Api.pay/order_notify'
        ];
        if (in_array(request()->controller() . '/' . request()->action(), $noneed)) {
            return true;
        }else {
            $token = input('post.token');
            if(!$token) {
                throw new HttpResponseException(ajax('token is empty',-5));
            }
            try {
                $exist = Db::table('mp_token')->where([
                    ['token','=',$token],
                    ['end_time','>',time()]
                ])->find();
            }catch (\Exception $e) {
                throw new HttpResponseException(ajax($e->getMessage(),-1));
            }
            if($exist) {
                $this->myinfo = unserialize($exist['value']);
                $this->myinfo['uid'] = $exist['uid'];
                return true;
            }else {
                throw new HttpResponseException(ajax('invalid token',-3));
            }
        }

    }


    //获取我的个人信息
    protected function getMyInfo() {
        $where = [
            ['id','=',$this->myinfo['uid']]
        ];
        try {
            $info = Db::table('mp_user')->where($where)->find();
        }catch (\Exception $e) {
            throw new HttpResponseException(ajax($e->getMessage(),-1));
        }
        return $info;
    }
    //Exception日志
    protected function log($cmd,$str) {
        $file= ROOT_PATH . '/exception_api.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]\ncmd:" .$cmd. "\n" .$str. "\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }
    //支付回调日志
    protected function paylog($cmd,$str) {
        $file= ROOT_PATH . '/notify.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]\ncmd:" .$cmd. "\n" .$str. "\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }


}