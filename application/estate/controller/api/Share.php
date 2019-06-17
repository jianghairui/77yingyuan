<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/6/17
 * Time: 13:29
 */
namespace app\estate\controller\api;
use think\Db;
use EasyWeChat\Factory;
use think\Exception;

class Share extends Common {

    public function getQrcode()
    {
        $uid = $this->myinfo['uid'];
        $app = Factory::miniProgram($this->mp_config);
        $response = $app->app_code->getUnlimit($uid, [
            'page' => 'pages/auth/auth',
            'width' => '300'
        ]);
        $png = $uid . '.png';
        $save_path = 'res/estate/appcode/';
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $filename = $response->saveAs($save_path, $png);
        } else {
            return ajax($response, -1);
        }
        return ajax($save_path . $png);
    }


}