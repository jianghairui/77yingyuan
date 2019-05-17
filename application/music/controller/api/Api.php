<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/8
 * Time: 11:11
 */
namespace app\music\controller\api;
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

    public function aboutUs() {
        try {
            $where = [
                ['id','=',1]
            ];
            $info = Db::table('mp_company')->where($where)->find();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $data['logo'] = $info['logo'];
        $data['desc'] = mb_substr(strip_tags($info['intro']),0,50,'utf8');
        $data['video'] = 'https://cave.jianghairui.com/20190517/1558059442.mp4';
        $data['poster'] = 'https://cave.jianghairui.com/tmp.jpg';
        return ajax($data);
    }

    public function instrumentList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',3);
        try {
            $where = [];
            $list = Db::table('mp_instrument')
                ->where($where)
                ->field('id,name,desc,pic')
                ->limit(($curr_page - 1)*$perpage,$perpage)
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function teacherList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        try {
            $where = [];
            $list = Db::table('mp_teacher')
                ->where($where)
                ->field('id,name,desc,pic')
                ->limit(($curr_page - 1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function courseCateList() {
        try {
            $where = [
                ['del','=',0]
            ];
            $list = Db::table('mp_course_cate')
                ->where($where)
                ->field('id,cate_name,pic')
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function courseList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $cate_id = input('post.cate_id',0);
        $where = [
            ['c.del','=',0]
        ];
        if($cate_id) {
            $where[] = ['c.cate_id','=',$cate_id];
        }
        try {
            $list = Db::table('mp_course')->alias('c')
                ->join('mp_course_cate cate','c.cate_id=cate.id','left')
                ->field('c.id,c.title,c.pic,c.desc,cate.cate_name')
                ->order(['c.id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        return ajax($list);
    }

    public function courseDetail() {
        $val['course_id'] = input('post.course_id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['course_id']]
            ];
            $exist = Db::table('mp_course')
                ->where($where)
                ->field('id,title,price,desc,content,pic')
                ->find();
            if(!$exist) {
                return ajax('',-4);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($exist);
    }

    public function courseOrder() {
        $val['course_id'] = input('post.course_id');
        $val['class_time'] = input('post.class_time');
        checkPost($val);
        $val['class_time'] = strtotime($val['class_time']);
        try {
            $where = [
                ['id','=',$val['course_id']]
            ];
            $exist = Db::table('mp_course')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-4);
            }
            $val['order_sn'] = create_unique_number('');
            $val['uid'] = $this->myinfo['uid'];
            $val['unit_price'] = $exist['price'];
            $val['total_price'] = $exist['price'];
            $val['num'] = 1;
            $val['openid'] = $this->myinfo['openid'];
            $val['create_time'] = time();
            Db::table('mp_order')->insert($val);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($val['order_sn']);
    }

    public function orderList() {
        $curr_page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $status = input('post.status','');
        $where = [
            ['o.uid','=',$this->myinfo['uid']]
        ];
        if($status !== '') {
            $where[] = ['o.status','=',$status];
        }
        try {
            $list = Db::table('mp_order')->alias('o')
                ->join('mp_course c','o.course_id=c.id','left')
                ->where($where)
                ->field('o.order_sn,o.total_price,o.pay_time,o.status,o.class_time,o.num,c.title,c.pic')
                ->limit(($curr_page-1)*$perpage,$perpage)
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $list = [];
        return ajax($list);
    }

    public function advise() {
        $val['content'] = input('post.content');
        checkPost($val);
        $val['uid'] = $this->myinfo['uid'];
        $val['create_time'] = time();
        try {
            Db::table('mp_advise')->insert($val);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }



}