<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/20
 * Time: 16:36
 */
namespace app\lucky\controller\api;
use EasyWeChat\Factory;
use think\Db;

class Login extends Common {

    //小程序登录
    public function login()
    {
        $code = input('post.code');
        checkPost(['code'=>$code]);
        try {
            $app = Factory::miniProgram($this->mp_config);
            $info = $app->auth->session($code);
            if(isset($info['errcode']) && $info['errcode'] !== 0) {
                return ajax($info,-1);
            }
            $ret['openid'] = $info['openid'];
            $ret['session_key'] = $info['session_key'];
            $exist = Db::table('mp_user')->where('openid',$ret['openid'])->find();
            if($exist) {
                $uid = $exist['id'];
                Db::table('mp_user')->where('openid',$ret['openid'])->update(['last_login_time'=>time()]);
            }else {
                $insert = [
                    'create_time'=>time(),
                    'last_login_time'=>time(),
                    'openid'=>$ret['openid']
                ];
                Db::table('mp_user')->insert($insert);
                $uid = Db::table('mp_user')->getLastInsID();
            }
            $third_session = exec('/usr/bin/head -n 80 /dev/urandom | tr -dc A-Za-z0-9 | head -c 168');
            $token_exist = Db::table('mp_token')->where('uid',$uid)->find();
            //把3rd_session存入mysql
            if(!$token_exist) {
                Db::table('mp_token')->insert([
                    'token' => $third_session,
                    'uid' => $uid,
                    'value' => serialize($ret),
                    'end_time' =>time() + 3600*24*7
                ]);
            }else {
                Db::table('mp_token')->where('uid',$uid)->update([
                    'token' => $third_session,
                    'uid' => $uid,
                    'value' => serialize($ret),
                    'end_time' =>time() + 3600*24*7
                ]);
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        $json['token'] = $third_session;
        $json['uid'] = $uid;
        return ajax($json);
    }

    //保存用户信息
    public function userAuth() {
        $iv = input('post.iv');
        $encryptData = input('post.encryptedData');
        checkPost([
            'iv' => $iv,
            'encryptedData' => $encryptData
        ]);
        if(!$iv || !$encryptData) {
            return ajax([],-2);
        }
        $app = Factory::miniProgram($this->mp_config);
        try {
            $decryptedData = $app->encryptor->decryptData($this->myinfo['session_key'], $iv, $encryptData);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        $user = $this->getMyInfo();
        try {
            if(!$user['user_auth']) {
                $data['nickname'] = $decryptedData['nickName'];
                $data['avatar'] = $decryptedData['avatarUrl'];
                $data['sex'] = $decryptedData['gender'];
//                $data['unionid'] = $decryptedData['unionId'];
                $data['user_auth'] = 1;
                Db::table('mp_user')->where('id','=',$this->myinfo['uid'])->update($data);
            }else {
                return ajax();
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        @unlink($user['avatar']);
        return ajax('保存成功',1);
    }

    //检测用户是否授权
    public function checkUserAuth() {
        $uid = $this->myinfo['uid'];
        try {
            $userauth = Db::table('mp_user')->where('id',$uid)->value('user_auth');
            if($userauth == 1) {
                return ajax(true);
            }else {
                return ajax(false);
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
    }

    //保存手机号
    public function getPhoneNumber() {
        $iv = input('post.iv');
        $encryptData = input('post.encryptedData');
        checkPost([
            'iv' => $iv,
            'encryptedData' => $encryptData
        ]);
        if(!$iv || !$encryptData) {
            return ajax([],-2);
        }
        $app = Factory::miniProgram($this->mp_config);
        try {
            $decryptedData = $app->encryptor->decryptData($this->myinfo['session_key'], $iv, $encryptData);
            $data['tel'] = $decryptedData['phoneNumber'];
            Db::table('mp_user')->where('id','=',$this->myinfo['uid'])->update($data);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($decryptedData,1);
    }

}