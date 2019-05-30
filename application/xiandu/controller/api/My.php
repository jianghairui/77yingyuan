<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/3/11
 * Time: 16:00
 */
namespace app\xiandu\controller\api;
use think\Db;
class My extends Common {
    //点击头像编辑个人资料
    public function modMyInfo() {
        $val['realname'] = input('post.realname');
        $val['nickname'] = input('post.nickname');
        $val['sex'] = input('post.sex');
        $val['age'] = input('post.age');
        $val['tel'] = input('post.tel');
        checkPost($val);

        $val['desc'] = input('post.desc','');
        $val['id'] = $this->myinfo['uid'];
        if(!is_tel($val['tel'])) {
            return ajax('无效的手机号',6);
        }
        $user = $this->getMyInfo();
        try {
            $avatar = input('post.avatar');
            if($avatar) {
                if (substr($avatar,0,4) == 'http') {
                    $val['avatar'] = $avatar;
                }else {
                    $val['avatar'] = rename_file($avatar,$this->rename_base_path);
                }
            }else {
                return ajax('',3);
            }
            Db::table('mp_user')->where('id',$val['id'])->update($val);
        } catch (\Exception $e) {
            if ($val['avatar'] != $user['avatar']) {
                @unlink($val['avatar']);
            }
            return ajax($e->getMessage(), -1);
        }
        if ($val['avatar'] != $user['avatar']) {
            @unlink($user['avatar']);
        }
        return ajax();

    }

    public function orderList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $status = input('post.status','');
        $where = "uid=".$this->myinfo['uid'];
        $where .= " AND `status` IN ('0','1','2','3') AND `del`=0 AND `refund_apply`=0";
        $order = " ORDER BY `id` DESC";
        $orderby = " ORDER BY `d`.`id` DESC";
        if($status !== '') {
            $where .= " AND status=" . $status;
        }
        try {
            $list = Db::query("SELECT 
`o`.`id`,`o`.`pay_order_sn`,`o`.`pay_price`,`o`.`total_price`,`o`.`carriage`,`o`.`create_time`,`o`.`refund_apply`,`o`.`status`,`o`.`refund_apply`,`d`.`order_id`,`d`.`goods_id`,`d`.`num`,`d`.`unit_price`,`d`.`goods_name`,`d`.`attr`,`g`.`pics` 
FROM (SELECT * FROM mp_order WHERE " . $where . $order ." LIMIT ".($curr_page-1)*$perpage.",".$perpage.") `o` 
LEFT JOIN `mp_order_detail` `d` ON `o`.`id`=`d`.`order_id`
LEFT JOIN `mp_goods` `g` ON `d`.`goods_id`=`g`.`id`
" . $orderby);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $order_id = [];
        $newlist = [];
        foreach ($list as $v) {
            $order_id[] = $v['id'];
        }
        $uniq_order_id = array_unique($order_id);
        foreach ($uniq_order_id as $v) {
            $child = [];
            foreach ($list as $li) {
                if($li['order_id'] == $v) {
                    $data['id'] = $li['id'];
                    $data['pay_order_sn'] = $li['pay_order_sn'];
                    $data['total_price'] = $li['total_price'];
                    $data['carriage'] = $li['carriage'];
                    $data['status'] = $li['status'];
                    $data['refund_apply'] = $li['refund_apply'];
                    $data['create_time'] = date('Y-m-d H:i',$li['create_time']);
                    $data_child['goods_id'] = $li['goods_id'];
                    $data_child['cover'] = unserialize($li['pics'])[0];
                    $data_child['goods_name'] = $li['goods_name'];
                    $data_child['num'] = $li['num'];
                    $data_child['unit_price'] = $li['unit_price'];
                    $data_child['total_price'] = sprintf ( "%1\$.2f",($li['unit_price'] * $li['num']));
                    $data_child['attr'] = $li['attr'];
                    $child[] = $data_child;
                }
            }
            $data['child'] = $child;
            $newlist[] = $data;
        }
        return ajax($newlist);
    }

    public function refundList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $type = input('post.type',1);
        if(!in_array($type,[1,2,3])) {
            return ajax($type,-4);
        }
        $where = "uid=".$this->myinfo['uid'];
        $order = " ORDER BY `id` DESC";
        $orderby = " ORDER BY `d`.`id` DESC";
        if($type == 1) {
            $where .= " AND refund_apply=1";
        }else if($type == 2){
            $where .= " AND refund_apply=2";
        }else {
            $where .= " AND refund_apply IN (1,2)";
        }
        try {
            $list = Db::query("SELECT 
`o`.`id`,`o`.`pay_order_sn`,`o`.`pay_price`,`o`.`total_price`,`o`.`carriage`,`o`.`create_time`,`o`.`refund_apply`,`o`.`status`,`o`.`refund_apply`,`d`.`order_id`,`d`.`goods_id`,`d`.`num`,`d`.`unit_price`,`d`.`goods_name`,`d`.`attr`,`g`.`pics` 
FROM (SELECT * FROM mp_order WHERE " . $where . $order . " LIMIT ".($curr_page-1)*$perpage.",".$perpage.") `o` 
LEFT JOIN `mp_order_detail` `d` ON `o`.`id`=`d`.`order_id`
LEFT JOIN `mp_goods` `g` ON `d`.`goods_id`=`g`.`id`
".$orderby);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $order_id = [];
        $newlist = [];
        foreach ($list as $v) {
            $order_id[] = $v['id'];
        }
        $uniq_order_id = array_unique($order_id);
        foreach ($uniq_order_id as $v) {
            $child = [];
            foreach ($list as $li) {
                if($li['order_id'] == $v) {
                    $data['id'] = $li['id'];
                    $data['pay_order_sn'] = $li['pay_order_sn'];
                    $data['total_price'] = $li['total_price'];
                    $data['carriage'] = $li['carriage'];
                    $data['status'] = $li['status'];
                    $data['refund_apply'] = $li['refund_apply'];
                    $data['create_time'] = date('Y-m-d H:i',$li['create_time']);
                    $data_child['goods_id'] = $li['goods_id'];
                    $data_child['cover'] = unserialize($li['pics'])[0];
                    $data_child['goods_name'] = $li['goods_name'];
                    $data_child['num'] = $li['num'];
                    $data_child['unit_price'] = $li['unit_price'];
                    $data_child['total_price'] = sprintf ( "%1\$.2f",($li['unit_price'] * $li['num']));
                    $data_child['attr'] = $li['attr'];
                    $child[] = $data_child;
                }
            }
            $data['child'] = $child;
            $newlist[] = $data;
        }
        return ajax($newlist);
    }
    //查看订单详情
    public function orderDetail() {
        $val['id'] = input('post.id');
        checkPost($val);
        $where = [
            ['o.id','=',$val['id']],
            ['o.uid','=',$this->myinfo['uid']]
        ];
        try {
            $list = Db::table('mp_order')->alias('o')
                ->join("mp_order_detail d","o.id=d.order_id","left")
                ->join("mp_goods g","d.goods_id=g.id","left")
                ->where($where)
                ->field("o.id,o.pay_order_sn,o.pay_price,o.total_price,o.carriage,o.receiver,o.tel,o.address,o.create_time,o.refund_apply,o.status,d.order_id,d.num,d.unit_price,d.goods_name,d.attr,g.pics")->select();
            if(!$list) {
                return ajax($val['id'],-4);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $data = [];
        $child = [];
        foreach ($list as $li) {
            $data['pay_order_sn'] = $li['pay_order_sn'];
            $data['receiver'] = $li['receiver'];
            $data['tel'] = $li['tel'];
            $data['address'] = $li['address'];
            $data['total_price'] = $li['total_price'];
            $data['carriage'] = $li['carriage'];
            $data['amount'] = $li['total_price'] - $data['carriage'];
            $data['create_time'] = date('Y-m-d H:i',$li['create_time']);
            $data['refund_apply'] = $li['refund_apply'];
            $data['status'] = $li['status'];
            $data_child['cover'] = unserialize($li['pics'])[0];
            $data_child['goods_name'] = $li['goods_name'];
            $data_child['num'] = $li['num'];
            $data_child['unit_price'] = $li['unit_price'];
            $data_child['total_price'] = sprintf ( "%1\$.2f",($li['unit_price'] * $li['num']));
            $data_child['attr'] = $li['attr'];
            $data_child['cover'] = unserialize($li['pics'])[0];
            $child[] = $data_child;
        }
        $data['child'] = $child;
        return ajax($data);

    }
    //申请退款
    public function refundApply() {
        $val['pay_order_sn'] = input('post.pay_order_sn');
        $val['reason'] = input('post.reason');
        checkPost($val);
        try {
            $where = [
                ['pay_order_sn','=',$val['pay_order_sn']],
                ['uid','=',$this->myinfo['uid']],
                ['status','in',[1,2,3]]
            ];
            $exist = Db::table('mp_order')->alias('o')->where($where)->find();
            if(!$exist) {
                return ajax( 'invalid pay_order_sn',44);
            }
            $update_data = [
                'refund_apply' => 1,
                'reason' => $val['reason']
            ];
            Db::table('mp_order')->where($where)->update($update_data);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }
    //确认收货
    public function orderConfirm() {
        $val['pay_order_sn'] = input('post.pay_order_sn');
        checkPost($val);
        try {
            $where = [
                ['pay_order_sn','=',$val['pay_order_sn']],
                ['uid','=',$this->myinfo['uid']],
                ['status','=',2]
            ];
            $exist = Db::table('mp_order')->alias('o')->where($where)->find();
            if(!$exist) {
                return ajax( 'invalid pay_order_sn',44);
            }
            $update_data = [
                'status' => 3,
                'finish_time' => time()
            ];
            Db::table('mp_order')->where($where)->update($update_data);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }
    //取消订单
    public function orderCancel() {
        $val['pay_order_sn'] = input('post.pay_order_sn');
        checkPost($val);
        try {
            $where = [
                ['pay_order_sn','=',$val['pay_order_sn']],
                ['uid','=',$this->myinfo['uid']],
                ['status','=',0],
                ['del','=',0]
            ];
            $exist = Db::table('mp_order')->alias('o')->where($where)->find();
            if(!$exist) {
                return ajax( 'invalid pay_order_sn',44);
            }
            $update_data = [
                'del' => 1
            ];
            Db::table('mp_order')->where($where)->update($update_data);
            $detail_list = Db::table('mp_order_detail')->where('order_id','=',$exist['id'])->select();
            foreach ($detail_list as $v) {
                if($v['use_attr'] == 1) {
                    Db::table('mp_goods_attr')->where('id','=',$v['attr_id'])->setInc('stock',$v['num']);
                }
                Db::table('mp_goods')->where('id','=',$v['goods_id'])->setInc('stock',$v['num']);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function addressList() {
        $uid = $this->myinfo['uid'];
        try {
            $where = [
                ['uid','=',$uid]
            ];
            $list = Db::table('mp_address')->where($where)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function addressAdd() {
        $val['uid'] = $this->myinfo['uid'];
        $val['provincename'] = input('post.provincename');
        $val['cityname'] = input('post.cityname');
        $val['countyname'] = input('post.countyname');
        $val['detail'] = input('post.detail');
        $val['postalcode'] = input('post.postalcode');
        $val['tel'] = input('post.tel');
        $val['username'] = input('post.username');
        $val['default'] = input('post.default',0);
        checkPost($val);
        if(!is_tel($val['tel'])) {
            return ajax('',6);
        }
        try {
            $id = Db::table('mp_address')->insertGetId($val);
            if($val['default']) {
                $whereDefault = [
                    ['id','<>',$id],
                    ['uid','=',$val['uid']]
                ];
                Db::table('mp_address')->where($whereDefault)->update(['default'=>0]);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function addressDetail() {
        $val['id'] = input('post.id');
        checkPost($val);
        $uid = $this->myinfo['uid'];
        $where = [
            ['id','=',$val['id']],
            ['uid','=',$uid]
        ];
        try {
            $info = Db::table('mp_address')->where($where)->find();
            if(!$info) {
                return ajax('',-4);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($info);
    }

    public function addressMod() {
        $val['id'] = input('post.id');
        $uid = $this->myinfo['uid'];
        $val['provincename'] = input('post.provincename');
        $val['cityname'] = input('post.cityname');
        $val['countyname'] = input('post.countyname');
        $val['detail'] = input('post.detail');
        $val['postalcode'] = input('post.postalcode');
        $val['tel'] = input('post.tel');
        $val['username'] = input('post.username');
        $val['default'] = input('post.default',0);
        checkPost($val);
        if(!is_tel($val['tel'])) {
            return ajax('',6);
        }
        $where = [
            ['id','=',$val['id']],
            ['uid','=',$uid]
        ];
        try {
            $info = Db::table('mp_address')->where($where)->find();
            if(!$info) {
                return ajax('',-4);
            }
            Db::table('mp_address')->where($where)->update($val);
            if($val['default']) {
                $whereDefault = [
                    ['id','<>',$val['id']],
                    ['uid','=',$uid]
                ];
                Db::table('mp_address')->where($whereDefault)->update(['default'=>0]);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function myCouponList() {
        $val['use'] = input('post.use','');
        try {
            $whereCoupon = [
                ['uc.uid','=',$this->myinfo['uid']],
            ];
            if($val['use'] == '1') {
                $whereCoupon[] = ['uc.use','=',0];
            }
            $list = Db::table('mp_user_coupon')->alias('uc')
                ->join('mp_coupon c','uc.coupon_id=c.id','left')
                ->field('uc.*,c.coupon_name,c.condition,c.cut_price')
                ->where($whereCoupon)
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }






}