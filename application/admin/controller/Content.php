<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/24
 * Time: 15:59
 */
namespace app\admin\controller;
use think\Db;
class Content extends Base {

    public function filmList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['f.title','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_film')->alias('f')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_film')->alias('f')
                ->join("mp_admin a","f.admin_id=a.id","left")
                ->field("f.*,a.realname")
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function messageList() {

    }

    public function filmAdd() {
        return $this->fetch();
    }

    public function filmAddPost() {
        $val['title'] = input('post.title');
        $val['status'] = input('post.status');
        checkPost($val);
        $val['content'] = input('post.content');
        $val['admin_id'] = session('admin_id');
        $val['create_time'] = time();
        foreach ($_FILES as $k=>$v) {
            if($v['name'] == '') {
                unset($_FILES[$k]);
            }
        }
        if(!empty($_FILES)) {
            $info = upload(array_keys($_FILES)[0]);
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }
        try {
            Db::table('mp_film')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([]);
    }

    public function filmMod() {
        $article_id = input('param.id');
        try {
            $exist = Db::table('mp_film')->where('id',$article_id)->find();
            if(!$exist) {
                die('非法操作');
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function filmModPost() {
        $val['title'] = input('post.title');
        $val['status'] = input('post.status');
        $val['id'] = input('post.id');
        checkPost($val);
        $val['content'] = input('post.content');
        foreach ($_FILES as $k=>$v) {
            if($v['name'] == '') {
                unset($_FILES[$k]);
            }
        }
        if(!empty($_FILES)) {
            $info = upload(array_keys($_FILES)[0]);
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }
        try {
            $exist = Db::table('mp_film')->where('id','=',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            Db::table('mp_film')->where('id','=',$val['id'])->update($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        if(isset($val['pic'])) {
            @unlink($exist['pic']);
        }
        return ajax([]);
    }

    //停用资讯
    public function filmHide() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_film')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }

            $res = Db::table('mp_film')->where('id',$val['id'])->update(['status'=>0]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        if($res !== false) {
            return ajax([],1);
        }else {
            return ajax([],-1);
        }
    }
    //启用资讯
    public function filmShow() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_film')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }

            $res = Db::table('mp_film')->where('id',$val['id'])->update(['status'=>1]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        if($res !== false) {
            return ajax([],1);
        }else {
            return ajax([],-1);
        }
    }

    //删除资讯
    public function filmDel() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_film')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }
            $model = model('Film');
            try {
                $model::destroy($val['id']);
            }catch (\Exception $e) {
                return ajax($e->getMessage(),-1);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax([],1);
    }



}