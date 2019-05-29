<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/8
 * Time: 11:11
 */

namespace app\xiandu\controller\api;

use think\Db;
use think\Exception;

class Api extends Common
{
//获取轮播图列表
    public function slideList()
    {
        try {
            $list = Db::table('mp_slideshow')->where([
                ['status', '=', 1]
            ])->order(['sort' => 'ASC'])->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }
//获取个人信息
    public function userinfo() {
        $info = $this->getMyInfo();
        return ajax($info);
    }
//首页优惠券列表
    public function couponList() {
        try {
            $where = [
                ['del','=',0]
            ];
            $list = Db::table('mp_coupon')
                ->where($where)
                ->field('coupon_name,condition,cut_price,create_time')
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }
//首页推荐商品
    public function recommendGoods() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $where = [
            ['status','=',1],
            ['recommend','=',1],
            ['del','=',0]
        ];

        try {
            $list = Db::table('mp_goods')->where($where)->field("id,name,origin_price,price,pics,width,height")->limit(($curr_page-1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        foreach ($list as &$v) {
            $v['cover'] = unserialize($v['pics'])[0];
            unset($v['pics']);
        }
        return ajax($list);
    }
//首页视频
    public function homeVideo() {
        $info['video'] = $this->weburl . 'upload/video/20190517/1558083883.mp4';
        $info['poster'] = $this->weburl . 'res/music/upload/2019-05-17/155808330186542800206.jpg';
        return ajax($info);
    }
//首页商品列表
    public function homeGoodsList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $where = [
            ['status','=',1],
            ['del','=',0]
        ];
        $order = ['id'=>'DESC'];
        try {
            $list = Db::table('mp_goods')
                ->where($where)
                ->order($order)
                ->field("id,name,origin_price,price,pics,width,height")->limit(($curr_page-1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        foreach ($list as &$v) {
            $v['cover'] = unserialize($v['pics'])[0];
            unset($v['pics']);
        }
        return ajax($list);
    }

    public function getCoupon() {
        $val['coupon_id'] = input('post.coupon_id');
        checkPost($val);
        try {

        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
    }
//上传图片限制512KB
    public function uploadImage()
    {
        if (!empty($_FILES)) {
            if (count($_FILES) > 1) {
                return ajax('最多上传一张图片', 9);
            }
            $path = upload(array_keys($_FILES)[0]);
            return ajax(['path' => $path]);
        } else {
            return ajax('请上传图片', 3);
        }
    }
//上传图片限制2048KB
    public function uploadImage2m()
    {
        if (!empty($_FILES)) {
            if (count($_FILES) > 1) {
                return ajax('最多上传一张图片', 9);
            }
            $path = upload(array_keys($_FILES)[0], 2048);
            return ajax(['path' => $path]);
        } else {
            return ajax('请上传图片', 3);
        }
    }

}