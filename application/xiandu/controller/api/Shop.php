<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/3/20
 * Time: 15:45
 */
namespace app\xiandu\controller\api;
use think\Db;

class Shop extends Common {
    //商城主页顶部分类
    public function topCate() {
        try {
            $map = [
                ['del','=',0],
                ['status','=',1],
                ['pid','=',0]
            ];
            $list = Db::table('mp_goods_cate')->where($map)->select();
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($list);
    }
    //商品列表
    public function goodsList() {
        $pcate_id = input('post.pcate_id',0);
        $cate_id = input('post.cate_id',0);
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $search = input('post.search','');
        $where = [
            ['status','=',1],
            ['del','=',0]
        ];
        $order = ['id'=>'DESC'];
        if($pcate_id) {
            $where[] = ['pcate_id','=',$pcate_id];
        }
        if($cate_id) {
            $where[] = ['cate_id','=',$cate_id];
        }
        if($search) {
            $where[] = ['name','like',"%{$search}%"];
        }
        try {
            $list = Db::table('mp_goods')
                ->where($where)
                ->order($order)
                ->field("id,name,origin_price,price,desc,pics,width,height")->limit(($curr_page-1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        foreach ($list as &$v) {
            $v['cover'] = unserialize($v['pics'])[0];
            unset($v['pics']);
        }
        return ajax($list);
    }
    //商品详情
    public function goodsDetail() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $info = Db::table('mp_goods')
                ->where($where)
                ->field("id,name,detail,origin_price,price,vip_price,pics,carriage,stock,sales,desc,use_attr,attr,hot,limit")
                ->find();
            if(!$info) {
                return ajax($val['id'],-4);
            }
            $attr_list = Db::table('mp_goods_attr')->where('goods_id',$val['id'])->select();
            $info['attr_list'] = $attr_list;
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $info['pics'] = unserialize($info['pics']);
        return ajax($info);
    }
    //获取分类
    public function cateList() {
        try {
            $map = [
                ['del','=',0],
                ['status','=',1]
            ];
            $list = Db::table('mp_goods_cate')->where($map)->select();
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        $list = $this->recursion($list,0);
        return ajax($list);

    }
    //加入购物车
    public function cartAdd() {
        $val['goods_id'] = input('post.goods_id');
        $val['num'] = input('post.num');
        checkPost($val);
        $val['attr_id'] = input('post.attr_id',0);
        $val['use_attr'] = 0;
        $val['uid'] = $this->myinfo['uid'];
        if(!if_int($val['num'])) {
            return ajax($val['num'],-4);
        }
        try {
            $where = [
                ['id','=',$val['goods_id']]
            ];
            $goods_exist = Db::table('mp_goods')->where($where)->find();
            if(!$goods_exist) {
                return ajax($val['goods_id'],-4);
            }
            $map = [
                ['goods_id','=',$val['goods_id']],
                ['uid','=',$this->myinfo['uid']]
            ];
            //是否使用规格
            if($val['attr_id']) {
                $val['use_attr'] = 1;
                $map_attr = [
                    ['id','=',$val['attr_id']],
                    ['goods_id','=',$val['goods_id']]
                ];
                $attr_exist = Db::table('mp_goods_attr')->where($map_attr)->find();
                if(!$attr_exist) {
                    return ajax($val['attr_id'],-4);
                }
                if($val['num'] > $attr_exist['stock']) {
                    return ajax('库存不足',39);
                }
                if($val['num'] > $goods_exist['limit']) {
                    return ajax('超出单笔限购数量',42);
                }
                $val['attr'] = $attr_exist['value'];
                $map[] = ['attr_id','=',$val['attr_id']];
                $cart_exist = Db::table('mp_cart')->where($map)->find();//购物车是否已经存在此商品
                if($cart_exist) {
                    if(($val['num'] + $cart_exist['num']) > $attr_exist['stock']) {
                        return ajax('商品+购件数(含购物车)超出库存',48);
                    }
                    if(($val['num'] + $cart_exist['num']) > $goods_exist['limit']) {
                        return ajax('超出单笔限购数量',42);
                    }
                    Db::table('mp_cart')->where($map)->setInc('num',$val['num']);
                }else {
                    $val['create_time'] = time();
                    Db::table('mp_cart')->insert($val);
                }
            }else {
                if($val['num'] > $goods_exist['stock']) {
                    return ajax('库存不足',39);
                }
                if($val['num'] > $goods_exist['limit']) {
                    return ajax('超出单笔限购数量',42);
                }
                $map[] = ['attr_id','=',0];
                $cart_exist = Db::table('mp_cart')->where($map)->find();//购物车是否已经存在此商品
                if($cart_exist) {
                    if(($val['num'] + $cart_exist['num']) > $goods_exist['stock']) {
                        return ajax('商品+购件数(含购物车)超出库存',48);
                    }
                    if(($val['num'] + $cart_exist['num']) > $goods_exist['limit']) {
                        return ajax('超出单笔限购数量',42);
                    }
                    Db::table('mp_cart')->where($map)->setInc('num',$val['num']);
                }else {
                    $val['create_time'] = time();
                    Db::table('mp_cart')->insert($val);
                }
            }

        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }
    //购物车列表
    public function cartList() {
        try {
            $where = [
                ['uid','=',$this->myinfo['uid']]
            ];
            $list = Db::table('mp_cart')->alias('c')
                ->join("mp_goods g","c.goods_id=g.id","left")
                ->join("mp_goods_attr a","c.attr_id=a.id","left")
                ->field("c.id,c.uid,c.goods_id,c.num,c.use_attr,c.attr,c.attr_id,g.name,g.pics,g.price,g.carriage,g.stock,g.limit")
                ->where($where)->select();
            foreach ($list as &$v) {
                $v['cover'] = unserialize($v['pics'])[0];
                unset($v['pics']);
                if($v['use_attr']) {
                    $map_attr = [
                        ['id','=',$v['attr_id']],
                        ['goods_id','=',$v['goods_id']]
                    ];
                    $attr_exist = Db::table('mp_goods_attr')->where($map_attr)->find();

                    $price = $attr_exist['price'];
                }else {
                    $price = $v['price'];
                }
                $v['price'] = $price;
                $v['total_price'] = $price * $v['num'];
                $v['total_price'] = sprintf ( "%1\$.2f",$v['total_price']);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }

        return ajax($list);
    }
    //购物车+++
    public function cartInc() {
        $val['cart_id'] = input('post.cart_id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['cart_id']],
                ['uid','=',$this->myinfo['uid']]
            ];
            $cart_exist = Db::table('mp_cart')->where($where)->find();
            if(!$cart_exist) {
                return ajax($val['cart_id'],-4);
            }
            $cart_exist['num'] += 1;
            $where_goods = [
                ['id','=',$cart_exist['goods_id']]
            ];
            $goods_exist = Db::table('mp_goods')->where($where_goods)->find();
            if($cart_exist['use_attr']) {
                $map_attr = [
                    ['id','=',$cart_exist['attr_id']],
                    ['goods_id','=',$cart_exist['goods_id']]
                ];
                $attr_exist = Db::table('mp_goods_attr')->where($map_attr)->find();
                if($cart_exist['num'] > $attr_exist['stock']) {
                    return ajax('此规格库存不足',41);
                }
                $price = $attr_exist['price'];
            }else {
                if($cart_exist['num'] > $goods_exist['stock']) {
                    return ajax('库存不足',39);
                }
                $price = $goods_exist['price'];
            }
            Db::table('mp_cart')->where($where)->setInc('num',1);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $data['price'] = $price;
        $data['num'] = $cart_exist['num'];
        $data['total_price'] = $price * $cart_exist['num'];
        $data['total_price'] = sprintf ( "%1\$.2f",$data['total_price']);
        return ajax($data);
    }
    //购物车---
    public function cartDec() {
        $val['cart_id'] = input('post.cart_id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['cart_id']],
                ['uid','=',$this->myinfo['uid']]
            ];
            $cart_exist = Db::table('mp_cart')->where($where)->find();
            if(!$cart_exist) {
                return ajax($val['cart_id'],-4);
            }
            $cart_exist['num'] -= 1;
            $where_goods = [
                ['id','=',$cart_exist['goods_id']]
            ];
            $goods_exist = Db::table('mp_goods')->where($where_goods)->find();
            if($cart_exist['use_attr']) {
                $map_attr = [
                    ['id','=',$cart_exist['attr_id']],
                    ['goods_id','=',$cart_exist['goods_id']]
                ];
                $attr_exist = Db::table('mp_goods_attr')->where($map_attr)->find();
                $price = $attr_exist['price'];
            }else {
                $price = $goods_exist['price'];
            }
            Db::table('mp_cart')->where($where)->setDec('num',1);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $data['price'] = $price;
        $data['num'] = $cart_exist['num'];
        $data['total_price'] = $price * $cart_exist['num'];
        $data['total_price'] = sprintf ( "%1\$.2f",$data['total_price']);
        return ajax($data);
    }
    //删除购物车
    public function cartDel() {
        $val['cart_id'] = input('post.cart_id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['cart_id']],
                ['uid','=',$this->myinfo['uid']]
            ];
            $exist = Db::table('mp_cart')->where($where)->find();
            if(!$exist) {
                return ajax($val['cart_id'],-4);
            }
            Db::table('mp_cart')->where($where)->delete();
            if($exist['attr_id']) {
                Db::table('mp_goods_attr')->where('id','=',$exist['attr_id'])->setInc('stock',$exist['num']);
            }
            Db::table('mp_goods')->where('id','=',$exist['goods_id'])->setInc('stock',$exist['num']);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }
    //购买下单
    public function purchase() {
        $data['goods_id'] = input('post.goods_id');
        $data['num'] = input('post.num');
        $data['receiver'] = input('post.receiver');
        $data['tel'] = input('post.tel');
        $data['address'] = input('post.address');
        checkPost($data);
        $data['attr_id'] = input('post.attr_id',0);
        $data['coupon_id'] = input('post.coupon_id',0);
        $data['use_attr'] = 0;
        if(!if_int($data['num'])) {
            return ajax($data['num'],-4);
        }

        try {
            $time = time();
            $goods_exist = Db::table('mp_goods')->where('id', $data['goods_id'])->find();
            if (!$goods_exist) {
                return ajax('invalid goods_id', -4);
            }
            if($data['num'] > $goods_exist['stock']) {
                return ajax('库存不足',39);
            }
            if($data['attr_id']) {
                $attr_id = input('post.attr_id');
                $data['use_attr'] = 1;
                $where_attr = [
                    ['id','=',$attr_id],
                    ['goods_id','=',$data['goods_id']],
                ];
                $attr_exist = Db::table('mp_goods_attr')->where($where_attr)->find();
                if(!$attr_exist) {
                    return ajax('invalid attr_id',-4);
                }
                if($data['num'] > $attr_exist['stock']) {
                    return ajax('库存不足',39);
                }
                $unit_price = $attr_exist['price'];
                $insert_detail['use_attr'] = 1;
                $insert_detail['attr_id'] = $data['attr_id'];
                $insert_detail['attr'] = $attr_exist['value'];
            }else {
                $unit_price = $goods_exist['price'];
            }

            $insert_data['uid'] = $this->myinfo['uid'];
            $insert_data['pay_order_sn'] = create_unique_number('');
            $insert_data['total_price'] = $unit_price * $data['num'] + $goods_exist['carriage'];
            $insert_data['pay_price'] = $insert_data['total_price'];
            $insert_data['carriage'] = $goods_exist['carriage'];
            $insert_data['receiver'] = $data['receiver'];
            $insert_data['tel'] = $data['tel'];
            $insert_data['address'] = $data['address'];
            $insert_data['create_time'] = $time;
            if($data['coupon_id']) {
                $whereCoupon = [
                    ['uc.id','=',$data['coupon_id']],
                    ['uc.uid','=',$this->myinfo['uid']],
                    ['uc.use','=',0]
                ];
                $coupon_exist = Db::table('mp_user_coupon')->alias('uc')
                    ->join('mp_coupon c','uc.coupon_id=c.id','left')
                    ->field('uc.*,c.condition,c.cut_price')
                    ->where($whereCoupon)
                    ->find();
                if(!$coupon_exist) {
                    return ajax('无效的优惠券',51);
                }
                if($coupon_exist['condition'] > $insert_data['total_price']) {
                    return ajax('未达到使用条件',52);
                }else {
                    $insert_data['pay_price'] = $insert_data['total_price'] - $coupon_exist['cut_price'];
                    $insert_data['coupon_price'] = $coupon_exist['cut_price'];
                }
            }

            Db::startTrans();
            $order_id = Db::table('mp_order')->insertGetId($insert_data);

            $insert_detail['order_id'] = $order_id;
            $insert_detail['goods_id'] = $goods_exist['id'];
            $insert_detail['goods_name'] = $goods_exist['name'];
            $insert_detail['num'] = $data['num'];
            $insert_detail['unit_price'] = $unit_price;
            $insert_detail['total_price'] = $unit_price * $data['num'] + $goods_exist['carriage'];
            $insert_detail['carriage'] = $goods_exist['carriage'];
            $insert_detail['weight'] = $goods_exist['weight'];
            $insert_detail['create_time'] = $time;

            Db::table('mp_order_detail')->insert($insert_detail);
            Db::table('mp_goods')->where('id', $data['goods_id'])->setDec('stock',$data['num']);
            if($data['attr_id']) {
                Db::table('mp_goods_attr')->where($where_attr)->setDec('stock',$data['num']);
            }
            if($data['coupon_id']) {
                Db::table('mp_user_coupon')->alias('uc')->where($whereCoupon)->update(['use'=>1]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return ajax($e->getMessage(), -1);
        }
        return ajax($insert_data['pay_order_sn']);
    }
    //购物车去支付
    public function cartToPurchase() {
        $cart_ids = input('post.cart_id',[]);
        $val['receiver'] = input('post.receiver');
        $val['tel'] = input('post.tel');
        $val['address'] = input('post.address');
        checkPost($val);
        $val['coupon_id'] = input('post.coupon_id',0);
        if(empty($cart_ids)) {
            return ajax('请选择要结算的商品',40);
        }
        if(array_unique($cart_ids) !== $cart_ids) {
            return ajax($cart_ids,-4);
        }
        try {
            $time = time();
            $where = [
                ['c.id','in',$cart_ids],
                ['c.uid','=',$this->myinfo['uid']]
            ];
            $cart_list = Db::table('mp_cart')->alias('c')
                ->join("mp_goods g","c.goods_id=g.id","left")
                ->join("mp_goods_attr a","c.attr_id=a.id","left")
                ->where($where)
                ->field("c.*,g.price,g.name,g.carriage,g.weight,g.stock AS total_stock,a.price AS attr_price,a.stock,a.value")
                ->select();
            if(count($cart_ids) != count($cart_list)) {
                return ajax($cart_ids,-4);
            }
            $pay_order_sn = create_unique_number('');
            $total_price = 0;
            $carriage = 0;
            $insert_detail_all = [];

            $card_delete_ids = [];
            foreach ($cart_list as $v) {
                if($v['use_attr']) {
                    $where_attr = [
                        ['id','=',$v['attr_id']],
                        ['goods_id','=',$v['goods_id']],
                    ];
                    $attr_exist = Db::table('mp_goods_attr')->where($where_attr)->find();
                    if(!$attr_exist) {
                        return ajax('invalid attr_id',-4);
                    }
                    if($v['num'] > $attr_exist['stock']) {
                        $card_delete_ids[] = $v['id'];
                    }
                    $unit_price = $attr_exist['price'];
                    $insert_detail['use_attr'] = 1;
                    $insert_detail['attr_id'] = $v['attr_id'];
                    $insert_detail['attr'] = $attr_exist['value'];
                }else {
                    if($v['num'] > $v['total_stock']) {
                        $card_delete_ids[] = $v['id'];
                    }
                    $unit_price = $v['price'];
                    $insert_detail['use_attr'] = 0;
                    $insert_detail['attr_id'] = 0;
                    $insert_detail['attr'] = '默认';
                }
                $total_price += ($unit_price * $v['num'] + $v['carriage']);
                $carriage += $v['carriage'];

                $insert_detail['goods_id'] = $v['goods_id'];
                $insert_detail['goods_name'] = $v['name'];
                $insert_detail['num'] = $v['num'];
                $insert_detail['unit_price'] = $unit_price;
                $insert_detail['total_price'] = $unit_price * $v['num'] + $v['carriage'];
                $insert_detail['carriage'] = $v['carriage'];
                $insert_detail['weight'] = $v['weight'];
                $insert_detail['create_time'] = $time;
                $insert_detail_all[] = $insert_detail;
            }
            if(!empty($card_delete_ids)) {
                $whereDelete = [
                    ['id','in',$card_delete_ids],
                    ['uid','=',$this->myinfo['uid']]
                ];
                Db::table('mp_cart')->where($whereDelete)->delete();
                return ajax('部分商品库存不足,请重新结算',47);
            }

            $insert_data['uid'] = $this->myinfo['uid'];
            $insert_data['pay_order_sn'] = $pay_order_sn;
            $insert_data['total_price'] = $total_price;
            $insert_data['pay_price'] = $total_price;
            $insert_data['carriage'] = $carriage;
            $insert_data['receiver'] = $val['receiver'];
            $insert_data['tel'] = $val['tel'];
            $insert_data['address'] = $val['address'];
            $insert_data['create_time'] = $time;

            if($val['coupon_id']) {
                $whereCoupon = [
                    ['uc.id','=',$val['coupon_id']],
                    ['uc.uid','=',$this->myinfo['uid']],
                    ['uc.use','=',0]
                ];
                $coupon_exist = Db::table('mp_user_coupon')->alias('uc')
                    ->join('mp_coupon c','uc.coupon_id=c.id','left')
                    ->field('uc.*,c.condition,c.cut_price')
                    ->where($whereCoupon)
                    ->find();
                if(!$coupon_exist) {
                    return ajax('无效的优惠券',51);
                }
                if($coupon_exist['condition'] > $insert_data['total_price']) {
                    return ajax('未达到使用条件',52);
                }else {
                    $insert_data['pay_price'] = $insert_data['total_price'] - $coupon_exist['cut_price'];
                    $insert_data['coupon_price'] = $coupon_exist['cut_price'];
                }
            }

            $order_id = Db::table('mp_order')->insertGetId($insert_data);
            foreach ($insert_detail_all as $k=>&$v) {
                $v['order_id'] = $order_id;
            }
            Db::table('mp_order_detail')->insertAll($insert_detail_all);

            foreach ($cart_list as $v) {
                Db::table('mp_goods')->where('id',$v['goods_id'])->setDec('stock',$v['num']);
                if($v['use_attr']) {
                    $where_attr = [
                        ['id','=',$v['attr_id']],
                        ['goods_id','=',$v['goods_id']],
                    ];
                    Db::table('mp_goods_attr')->where($where_attr)->setDec('stock',$v['num']);
                }
            }
            $whereDelete = [
                ['id','in',$cart_ids],
                ['uid','=',$this->myinfo['uid']]
            ];
            Db::table('mp_cart')->where($whereDelete)->delete();
            if($val['coupon_id']) {
                Db::table('mp_user_coupon')->alias('uc')->where($whereCoupon)->update(['use'=>1]);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($pay_order_sn);

    }

    public function getDefaultAddress() {
        $uid = $this->myinfo['uid'];
        $where = [
            ['default','=',1],
            ['uid','=',$uid]
        ];
        try {
            $info = Db::table('mp_address')->where($where)->find();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($info);
    }

    private function recursion($array,$pid=0) {
        $to_array = [];
        foreach ($array as $v) {
            if($v['pid'] == $pid) {
                $v['child'] = $this->recursion($array,$v['id']);
                $to_array[] = $v;
            }
        }
        return $to_array;
    }

}