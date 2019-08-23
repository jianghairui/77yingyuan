<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/25
 * Time: 16:09
 */
namespace app\estate\controller;
use think\Db;

class Member extends Base {
    //会员列表
    public function memberlist() {
        $param['logmin'] = input('param.logmin');
        $param['logmax'] = input('param.logmax');
        $param['search'] = input('param.search');

        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);

        $where = [];
        if($param['logmin']) {
            $where[] = ['u.create_time','>=',strtotime(date('Y-m-d 00:00:00',strtotime($param['logmin'])))];
        }

        if($param['logmax']) {
            $where[] = ['u.create_time','<=',strtotime(date('Y-m-d 23:59:59',strtotime($param['logmax'])))];
        }

        if($param['search']) {
            $where[] = ['u.nickname|u.tel','like',"%{$param['search']}%"];
        }
        $order = ['u.id'=>'DESC'];
        try {
            $count = Db::table('mp_user')->alias('u')->where($where)->count();
            $page['count'] = $count;
            $page['curr'] = $curr_page;
            $page['totalPage'] = ceil($count/$perpage);
            $list = Db::table('mp_user')->alias('u')
                ->join('mp_user u2','u.inviter_id=u2.id','left')
                ->where($where)
                ->order($order)
                ->field('u.*,u2.nickname AS nickname2,u2.avatar AS avatar2')
                ->limit(($curr_page - 1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }

        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }
    //会员推荐
    public function appointList() {
        $param['logmin'] = input('param.logmin');
        $param['logmax'] = input('param.logmax');
        $param['search'] = input('param.search');

        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);

        $where = [
            ['r.del','=',0]
        ];
        if($param['logmin']) {
            $where[] = ['a.meeting_date','>=',date('Y-m-d 00:00:00',strtotime($param['logmin']))];
        }

        if($param['logmax']) {
            $where[] = ['a.meeting_date','<=',date('Y-m-d 23:59:59',strtotime($param['logmax']))];
        }

        if($param['search']) {
            $where[] = ['a.name|a.tel','like',"%{$param['search']}%"];
        }
        try {
            $count = Db::table('mp_appoint')->alias('a')
                ->join("mp_resource r","a.res_id=r.id","left")
                ->where($where)->count();
            $page['count'] = $count;
            $page['curr'] = $curr_page;
            $page['totalPage'] = ceil($count/$perpage);
            $list = Db::table('mp_appoint')->alias('a')
                ->join("mp_resource r","a.res_id=r.id","left")
                ->join("mp_user u","a.inviter_id=u.id","left")
                ->where($where)
                ->field("a.*,r.name AS res_name,u.nickname AS rec_name,u.tel AS rec_tel")
                ->order(['a.id'=>'DESC'])->limit(($curr_page - 1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }

        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function contact() {
        $id = input('post.id');
        try {
            $where = [
                ['id','=',$id]
            ];
            Db::table('mp_appoint')->where($where)->update(['status'=>1]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function deal() {
        $id = input('post.id');
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_appoint')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-4);
            }
            $update_data = [
                'deal_time' => time(),
                'status' => 2
            ];
            Db::table('mp_appoint')->where($where)->update($update_data);
            Db::table('mp_user')->where('id','=',$exist['inviter_id'])->setInc('deal_num',1);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function settle() {
        $id = input('post.id');
        try {
            $where = [
                ['id','=',$id]
            ];
            Db::table('mp_appoint')->where($where)->update(['status'=>3]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function inviterMod() {
        if(request()->isPost()) {
            $id = input('post.id','');
            $inviter_id = input('post.inviter_id','');
            try {
                $where = [
                    ['id','in',[$id,$inviter_id]]
                ];
                $exist = Db::table('mp_user')->where($where)->select();
                if(count($exist) != 2) {
                    return ajax('非法操作',-1);
                }
                Db::table('mp_user')->where('id','=',$id)->update(['inviter_id'=>$inviter_id]);
            } catch (\Exception $e) {
                return ajax($e->getMessage(), -1);
            }
            return ajax();
        }
        $id = input('param.id','');
        try {
            $info = Db::table('mp_user')->where('id','=',$id)->find();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$info);
        return $this->fetch();
    }


}