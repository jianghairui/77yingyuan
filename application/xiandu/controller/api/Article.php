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
    //获取评论列表
    public function commentList() {
        $val['article_id'] = input('post.article_id');
        checkPost($val);
        try {
            $exist = Db::table('mp_article')->where('id',$val['article_id'])->find();
            if(!$exist) {
                return ajax('invalid article_id',-4);
            }
            $list = DB::query("SELECT c.id,c.article_id,c.uid,c.to_cid,c.to_uid,c.content,c.root_cid,c.created_time,u.avatar,u.nickname,IFNULL(u2.nickname,'') AS to_nickname 
FROM mp_comment c 
LEFT JOIN mp_user u ON c.uid=u.id 
LEFT JOIN mp_user u2 ON c.to_uid=u2.id 
WHERE c.article_id=?",[$val['article_id']]);
            $list = $this->recursion($list);
            return ajax($list);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
    }
    //发表评论
    public function commentAdd() {
        $val['article_id'] = input('post.article_id');
        $val['content'] = input('post.content');
        checkPost($val);
        $val['uid'] = $this->myinfo['uid'];
        $val['to_cid'] = input('post.to_cid');

        try {
            $exist = Db::table('mp_article')->where('id',$val['article_id'])->find();
            if(!$exist) {
                return ajax('invalid article_id',-4);
            }
            if($val['to_cid']) {
                $map = [
                    ['id','=',$val['to_cid']],
                    ['article_id','=',$val['article_id']]
                ];
                $comment_exist = Db::table('mp_comment')->where($map)->find();
                if($comment_exist) {
                    $val['to_uid'] = $comment_exist['uid'];
                    if($comment_exist['to_cid'] == 0) {
                        $val['root_cid'] = $comment_exist['id'];
                    }else {
                        $val['root_cid'] = $comment_exist['root_cid'];
                    }
                }else {
                    return ajax('',-4);
                }
            }else {
                $val['to_cid'] = 0;
                $val['to_uid'] = 0;
                $val['root_cid'] = 0;
            }
            $val['created_time'] = date("Y-m-d H:i:s");
            Db::table('mp_comment')->insert($val);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax();
    }


    private function recursion($array,$to_cid=0) {
        $to_array = [];
        foreach ($array as $v) {
            if($v['root_cid'] == $to_cid) {
                $v['child'] = $this->recursion($array,$v['id']);
                $to_array[] = $v;
            }
        }
        return $to_array;
    }


}