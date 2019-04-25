<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/22
 * Time: 13:53
 */
namespace app\api\controller;
use EasyWeChat\Factory;
use think\Db;

class Index extends Common {

    public function slideList() {
        try {
            $where = [
                ['status','=',1]
            ];
            $list = Db::table('mp_slideshow')->where($where)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function messageAdd() {
        $val['content'] = input('post.content');
        checkPost($val);
        $val['uid'] = $this->myinfo['uid'];
        $val['create_time'] = time();
        try {
            Db::table('mp_message')->insert($val);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function messageList() {
        try {
            $list = Db::table('mp_message')->alias('m')
                ->join("mp_user u","m.uid=u.id","left")
                ->field("m.id,m.content,m.create_time,u.nickname,u.avatar")
                ->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax($list);
    }

    public function filmList() {
        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        $count = Db::table('mp_film')->alias('f')->where($where)->count();
        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_film')->alias('f')
                ->join("mp_admin a","f.admin_id=a.id","left")
                ->field("f.id,f.pic,f.title,f.up_time")
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        return ajax($list);
    }

    public function contact() {

    }

    public function activeList() {

    }













}
