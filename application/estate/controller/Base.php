<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/18
 * Time: 18:17
 */
namespace app\estate\controller;
use think\Controller;
use my\Auth;
use think\exception\HttpResponseException;
class Base extends Controller {

    protected $cmd;

    public function initialize() {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->cmd = request()->controller() . '/' . request()->action();

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
            'Login/test',
//            'Banner/bifileupload',
        ];
        if (in_array(request()->controller() . '/' . request()->action(), $noNeedSession)) {
            return true;
        }else {
            if(session('username') && session('loginstatus') && session('loginstatus') == md5(session('username') . config('login_key'))) {
                if(session('username') !== config('superman')) {
                    $auth = new Auth();
                    $bool = $auth->check($this->cmd,session('admin_id'));
                    if(!$bool) {
                        if(request()->isPost()) {
                            throw new HttpResponseException(ajax('没有权限',-1));
                        }else {
                            exit($this->fetch('public/noAuth'));
                        }
                    }
                }
                return true;
            }else {
                return false;
            }
        }
    }

    protected function excep($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/exception.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }


}