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
        $data['id'] = $info['id'];
        $data['nickname'] = $info['nickname'];
        $data['realname'] = $info['realname'];
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
                ->field("id,pic,name,avr_price,min_area,max_area,commission,tags")
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
        $exist['pics0'] = unserialize($exist['pics0']);
        $exist['pics1'] = unserialize($exist['pics1']);
        $exist['pics2'] = unserialize($exist['pics2']);
        $exist['pics3'] = unserialize($exist['pics3']);
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
        $val['type'] = input('post.type',1);
        checkPost($val);
        $val['desc'] = input('post.desc');
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
//            $whereAppoint = [
//                ['res_id','=',$val['res_id']],
//                ['tel','=',$val['tel']]
//            ];
//            $appoint_exist = Db::table('mp_appoint')->where($whereAppoint)->find();
//            if($appoint_exist) {
//                return ajax('此手机号已预约',47);
//            }
            $val['commission'] = $res_exist['commission'];
            $id = Db::table('mp_appoint')->insertGetId($val);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->asyn_smtp(['id'=>$id]);
        return ajax();
    }

    public function aboutUs() {
        try {
            $info = Db::table('mp_company')->field('id,name,intro,tel,address,linkman,logo,lon,lat,desc')->find();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($info);
    }

    public function appointList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $order = ['id'=>'DESC'];
        try {
            $where = [
                ['uid','=',$this->myinfo['uid']],
                ['type','=',1]
            ];
            $list = Db::table('mp_appoint')
                ->where($where)
                ->limit(($curr_page-1)*$perpage,$perpage)->order($order)->select();
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

    public function getTreaty() {
        $type = input('post.type',1);
        switch ($type) {
            case 1:
                $field='treaty1';break;
            case 2:
                $field='treaty2';break;
            case 3:
                $field='treaty3';break;
        }
        try {
            $info = Db::table('mp_company')->where('id','=',1)->value($field);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($info);
    }

    public function getInviteList() {
        $where = [
            ['inviter_id','=',$this->myinfo['uid']]
        ];
        $order = ['id'=>'DESC'];
        try {
            $data['count'] = Db::table('mp_user')->where($where)->count();
            $data['list'] = Db::table('mp_user')->where($where)->field('id,nickname,tel,avatar,deal_num')->order($order)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($data);
    }

    //检测用户是否手机授权
    public function checkUserPhoneAuth() {
        $uid = $this->myinfo['uid'];
        try {
            $tel = Db::table('mp_user')->where('id',$uid)->value('tel');
            if($tel) {
                return ajax(true);
            }else {
                return ajax(false);
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
    }

    //检测用户是否填写真实姓名
    public function checkUserRealname() {
        $uid = $this->myinfo['uid'];
        try {
            $realname = Db::table('mp_user')->where('id',$uid)->value('realname');
            if($realname) {
                return ajax(true);
            }else {
                return ajax(false);
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
    }

    public function realname() {
        $val['realname'] = input('post.realname');
        checkPost($val);
        try {
            Db::table('mp_user')->where('id','=',$this->myinfo['uid'])->update($val);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }


    public function getCommission() {
        $uid = $this->myinfo['uid'];
        try {
            $where = [
                ['uid','=',$uid],
                ['status','=',3]
            ];
            $data['commission_settled'] = Db::table('mp_appoint')->where($where)->sum('commission');
            $where = [
                ['uid','=',$uid],
                ['status','=',2]
            ];
            $data['commission_unsettled'] = Db::table('mp_appoint')->where($where)->sum('commission');
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($data);
    }


    public function getCommissionList() {
        $uid = $this->myinfo['uid'];
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        try {
            $where = [
                ['a.uid','=',$uid],
                ['a.status','in',[2,3]]
            ];
            $list = Db::table('mp_appoint')->alias('a')
                ->join('mp_resource r','a.res_id=r.id','left')
                ->where($where)->field('a.id,a.name,a.tel,a.create_time,a.deal_time,a.status,a.commission,r.name AS res_name')->limit(($curr_page-1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

//    public function test() {
//        $this->asyn_smtp(['id'=>29]);
//        return ajax(987);
//    }

    protected function asyn_smtp($data) {
        $param = http_build_query($data);
        $fp = fsockopen('ssl://' . $this->domain, 443, $errno, $errstr, 20);
        if (!$fp){
            echo 'error fsockopen';
        }else{
            stream_set_blocking($fp,0);
            $http = "GET /estate/api.email/sendSmtp?".$param." HTTP/1.1\r\n";
            $http .= "Host: ".$this->domain."\r\n";
            $http .= "Connection: Close\r\n\r\n";
            fwrite($fp,$http);
            usleep(1000);
            fclose($fp);
        }
    }





}