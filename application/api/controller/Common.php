<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/18
 * Time: 21:36
 */
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\exception\HttpResponseException;
use EasyWeChat\Factory;
class Common extends Controller {

    protected $cmd = '';
    protected $domain = '';
    protected $weburl = '';
    protected $mp_config = [];
    protected $myinfo = [];

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->cmd = request()->controller() . '/' . request()->action();
        $this->domain = 'mp.wcip.net';
        $this->weburl = 'https://mp.wcip.net/';
        $this->mp_config = [
            'app_id' => 'wx0f4378399f17c930',
            'secret' => 'd99d876b78a3e46397b44c1f9715eb0c',
            'mch_id'             => '',
            'key'                => '',   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          =>  '/mnt/xxxxxx/public/cert/apiclient_cert.pem',
            'key_path'           =>  '/mnt/xxxxxx/public/cert/apiclient_key.pem',
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
            'Login/login'
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

    protected function checkPost($postArray) {
        if(empty($postArray)) {
            throw new HttpResponseException(ajax($postArray,-2));
        }
        foreach ($postArray as $value) {
            if (is_null($value) || $value === '') {
                throw new HttpResponseException(ajax($postArray,-2));
            }
        }
        return true;
    }

    protected function upload($k,$maxsize=512) {
        $allowType = array(
            "image/gif",
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/pjpeg",
            "image/bmp"
        );
        if($_FILES[$k]["type"] == '') {
            throw new HttpResponseException(ajax('图片存在中文名或超过2M',20));
        }
        if(!in_array($_FILES[$k]["type"],$allowType)) {
            throw new HttpResponseException(ajax('文件类型不符' . $_FILES[$k]["type"],21));
        }
        if($_FILES[$k]["size"] > $maxsize*1024) {
            throw new HttpResponseException(ajax('图片大小不超过'.$maxsize.'Kb',22));
        }
        if ($_FILES[$k]["error"] > 0) {
            throw new HttpResponseException(ajax("error: " . $_FILES[$k]["error"],-1));
        }

        $filename_array = explode('.',$_FILES[$k]['name']);
        $ext = array_pop($filename_array);

        $path =  'static/tmp/';
        is_dir($path) or mkdir($path,0755,true);
        //转移临时文件
        $newname = create_unique_number() . '.' . $ext;
        move_uploaded_file($_FILES[$k]["tmp_name"], $path . $newname);
        $filepath = $path . $newname;
        return $filepath;
    }

    protected function rename_file($tmp,$path = '') {
        $filename = substr(strrchr($tmp,"/"),1);
        $path = $path ? $path : 'upload/';
        $path.= date('Y-m-d') . '/';
        is_dir($path) or mkdir($path,0755,true);
        @rename($tmp, $path . $filename);
        return $path . $filename;
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
    protected function excep($cmd,$str) {
        $file= ROOT_PATH . '/exception.txt';
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

    //小程序验证文本内容是否违规
    protected function msgSecCheck($msg) {
        $content = $msg;
        $app = Factory::payment($this->mp_config);
        $access_token = $app->access_token;
        $token = $access_token->getToken();
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token=' . $token['access_token'];
        $res = curl_post_data($url, '{ "content":"'.$content.'" }');

        $result = json_decode($res,true);
        try {
            $audit = true;
            if($result['errcode'] !== 0) {
                $this->excep($this->cmd,$this->myinfo['uid'] .' : '. $content .' : '. var_export($result,true));
                switch ($result['errcode']) {
                    case 87014: $audit = false;break;
                    case 40001:
                        $audit = false;break;
                    default:$audit = false;;
                }
            }
        } catch (\Exception $e) {
            throw new HttpResponseException(ajax($e->getMessage(),-1));
        }
        return $audit;
    }


}