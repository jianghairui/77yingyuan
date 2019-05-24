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
        $val['search'] = input('post.search','');
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
        if($val['search']) {
            $where[] = ['name','like',"%{$val['search']}%"];
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
                ->field("id,pic,name,avr_price,min_area,max_area,min_bro,max_bro,tags")
                ->limit(($curr_page-1)*$perpage,$perpage)
                ->order($order)
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function resourceDetail() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_resource')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-4);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($exist);
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

    public function apartmentDetail() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $where = [
                ['a.id','=',$val['id']]
            ];
            $exist = Db::table('mp_apartment')->alias('a')
                ->join("mp_resource r","a.res_id=r.id","left")
                ->where($where)
                ->field("a.*,r.name")
                ->find();
            if(!$exist) {
                return ajax('非法参数',-4);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $exist['pic'] = unserialize($exist['pic']);
        return ajax($exist);
    }

    public function appointment() {
        $val['res_id'] = input('post.res_id');
        $val['name'] = input('post.name');
        $val['tel'] = input('post.tel');
        $val['meeting_date'] = input('post.meeting_date');
        $val['uid'] = $this->myinfo['uid'];
        checkPost($val);
        $val['create_time'] = time();
        if(!is_tel($val['tel'])) {
            return ajax('无效的手机号',6);
        }
        if(!is_date($val['meeting_date'])) {
            return ajax('无效的日期',48);
        }
        try {
            $whereRes = [
                ['id','=',$val['res_id']]
            ];
            $res_exist = Db::table('mp_resource')->where($whereRes)->find();
            if(!$res_exist) {
                return ajax('非法参数',-4);
            }
            $whereAppoint = [
                ['res_id','=',$val['res_id']],
                ['tel','=',$val['tel']]
            ];
            $appoint_exist = Db::table('mp_appoint')->where($whereAppoint)->find();
            if($appoint_exist) {
                return ajax('此手机号已预约',47);
            }
            Db::table('mp_appoint')->insert($val);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function aboutUs() {
        try {
            $info = Db::table('mp_company')->find();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($info);
    }

    public function appointList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        try {
            $where = [
                ['a.uid','=',$this->myinfo['uid']]
            ];
            $list = Db::table('mp_appoint')->alias('a')
                ->join("mp_resource r","a.res_id=r.id","left")
                ->field("a.*,r.name AS res_name,r.min_bro,r.max_bro,r.pic,r.avr_price")
                ->where($where)
                ->limit(($curr_page-1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function appointCancel() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['id']],
                ['uid','=',$this->myinfo['uid']]
            ];
            $exist = Db::table('mp_appoint')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-4);
            }
            if($exist['status'] == 1) {
                return ajax('无法取消,请刷新重试',49);
            }
            Db::table('mp_appoint')->where($where)->delete();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

}