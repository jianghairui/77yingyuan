<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/25
 * Time: 16:12
 */
namespace app\dati\controller;
use my\Auth;
use think\Db;
use think\Controller;
use think\exception\HttpResponseException;

class Base extends Controller {

    protected $weburl = '';
    protected $cmd;
    protected $upload_base_path;
    protected $rename_base_path;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->cmd = request()->controller() . '/' . request()->action();
        $this->weburl = 'mp.wcip.net';
        $this->upload_base_path = 'res/dati/admin/';
        $this->rename_base_path = 'res/dati/api/';
        if(!$this->needSession()) {
            if(request()->isPost()) {
                throw new HttpResponseException(ajax([],-2));
            }else {
                $this->error('请登录后操作',url('Login/index'));
            }
        }

    }

    private function needSession() {
        $noNeedSession = [
            'Login/index',
            'Login/vcode',
            'Login/login',
            'Login/test'
        ];
        if (in_array(request()->controller() . '/' . request()->action(), $noNeedSession)) {
            return true;
        }else {
            if(session('username') && session('mploginstatus') && session('mploginstatus') == md5(session('username') . config('login_key'))) {
                return true;
            }else {
                return false;
            }
        }
    }

    protected function getip() {
        $unknown = 'unknown';
        if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        /*
        处理多层代理的情况
        或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
        */
        if (false !== strpos($ip, ','))
            $ip = reset(explode(',', $ip));
        return $ip;
    }

    protected function log($detail = '', $type = 0) {
        $insert['detail'] = $detail;
        $insert['admin_id'] = session('admin_id');
        $insert['create_time'] = time();
        $insert['ip'] = $this->getip();
        $insert['type'] = $type;
        Db::table('mp_syslog')->insert($insert);
    }



}