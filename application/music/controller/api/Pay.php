<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/4
 * Time: 10:50
 */
namespace app\music\controller\api;
use EasyWeChat\Factory;
use think\Db;
class Pay extends Common {

    public function pay() {
        $val['order_sn'] = input('post.order_sn');
        checkPost($val);
        $where = [
            ['order_sn','=',$val['order_sn']],
            ['status','=',0]
        ];

        $app = Factory::payment($this->mp_config);
        try {
            $order_exist = Db::table('mp_order')->where($where)->find();
            if(!$order_exist) {
                return ajax('',4);
            }
            $result = $app->order->unify([
                'body' => '音尚教育',
                'out_trade_no' => $val['order_sn'],
                'total_fee' => 1,
//                'total_fee' => floatval($order_exist['price'])*100,
                'notify_url' => $this->weburl . 'music/Api.pay/notify',
                'trade_type' => 'JSAPI',
                'openid' => $order_exist['openid'],
            ]);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        if($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            return ajax($result['err_code_des'],-1);
        }
        try {
            $sign['appId'] = $result['appid'];
            $sign['timeStamp'] = strval(time());
            $sign['nonceStr'] = $result['nonce_str'];
            $sign['signType'] = 'MD5';
            $sign['package'] = 'prepay_id=' . $result['prepay_id'];
            $sign['paySign'] = getSign($sign);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($sign);
    }
    //支付回调接口
    public function notify() {
        //将返回的XML格式的参数转换成php数组格式
        $xml = file_get_contents('php://input');
        $data = xml2array($xml);
        $this->paylog($this->cmd,var_export($data,true));
        if($data) {
            if($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
                try {
                    $map = [
                        ['order_sn','=',$data['out_trade_no']],
                        ['status','=',0],
                    ];
                    $exist = Db::table('mp_order')->where($map)->find();
                    if($exist) {
                        $update_data = [
                            'status' => 1,
                            'trans_id' => $data['transaction_id'],
                            'pay_time' => time(),
                        ];
                        Db::table('mp_order')->where('order_sn','=',$data['out_trade_no'])->update($update_data);
                    }
                } catch (\Exception $e) {
                    $this->log($this->cmd,$e->getMessage());
                }
            }

        }
        exit(array2xml(['return_code'=>'SUCCESS','return_msg'=>'OK']));

    }






}