<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/8
 * Time: 11:11
 */
namespace app\estate\controller\api;
use think\Db;

class Api extends Common {

    public function getSlideList() {
        try {
            $list = Db::table('mp_slideshow')->where('status',1)->order(['sort'=>'ASC'])->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function userInfo()
    {
        $info = $this->getMyInfo();
        $data['nickname'] = $info['nickname'];
        $data['avatar'] = $info['avatar'];
        $data['sex'] = $info['sex'];
        $data['tel'] = $info['tel'];
        return ajax($data);
    }

    public function getCityList() {
        try {
            $where = [
                ['pcode','=','1201']
            ];
            $citylist = Db::table('mp_city')->where($where)->field("id,name")->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($citylist);
    }

    public function resourceList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $val['min_price'] = input('post.min_price',0);
        $val['max_price'] = input('post.max_price',0);
        $val['region'] = input('post.region','');
        $val['sort'] = input('post.sort','');
        $where = [];
        if($val['min_price']) {
            $where[] = ['avr_total_price','>=',$val['min_price']*10000];
        }
        if($val['max_price']) {
            $where[] = ['avr_total_price','<=',$val['max_price']*10000];
        }
        if($val['region']) {
            $where[] = ['region','=',$val['region']];
        }
        $order = [];
        if($val['sort']) {
            switch ($val['sort']) {
                case '1':
                    $order = ['avr_price'=>'ASC'];break;
                case '2':
                    $order = ['avr_price'=>'DESC'];break;
                case '3':
                    $order = ['avr_total_price'=>'ASC'];break;
                case '4':
                    $order = ['avr_total_price'=>'DESC'];break;
            }
        }
        try {
            $where[] = ['del','=',0];
            $list = Db::table('mp_resource')
                ->where($where)
                ->limit(($curr_page-1)*$perpage,$perpage)
                ->order($order)
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function apartmentList() {
        $val['res_id'] = input('post.res_id','');
        checkPost($val);
        $where = [
            ['res_id','=',$val['res_id']]
        ];
        try {
            $list = Db::table('mp_apartment')
                ->where($where)
                ->field('id,title,tags,price,area,orientation,pic,status')
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        foreach ($list as &$v) {
            $v['cover'] = unserialize($v['pic'])[0];
        }
        return ajax($list);
    }


}