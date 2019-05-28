<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/3/11
 * Time: 13:09
 */
namespace app\xiandu\controller\api;
use think\Db;

class Article extends Common {

    public function articleList() {
        $page = input('post.page',1);
        $perpage = input('post.perpage',10);
        $where = [
            ['a.status','=',1]
        ];
        try {
            $count = Db::table('mp_article')->alias('a')->where($where)->count();
            $list = Db::table('mp_article')->alias('a')
                ->join('mp_admin ad','a.admin_id=ad.id','left')
                ->where($where)
                ->order(['a.create_time'=>'DESC'])
                ->field('a.id,a.title,a.desc,a.pic,a.create_time,ad.realname')
                ->limit(($page-1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $data['count'] = $count;
        $data['totalPage'] = ceil($count/10);
        $data['list'] = $list;
        return ajax($data);
    }
    //获取资讯详情
    public function articleDetail() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_article')->alias('a')
                ->join('mp_admin ad','a.admin_id=ad.id','left')
                ->field('a.*,ad.realname')
                ->where('a.id','=',$val['id'])->find();
            if(!$exist) {
                return ajax('',-4);
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($exist);
    }


}