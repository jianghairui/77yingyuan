<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/28
 * Time: 10:37
 */
namespace app\xiandu\controller;
use think\Db;
class Article extends Common {

    //资讯列表
    public function articleList()
    {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['a.title|a.desc','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_article')->alias('a')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_article')->alias('a')
                ->join('mp_admin ad','a.admin_id=ad.id','left')
                ->field('a.*,ad.realname')
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }

        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }
    //添加资讯页面
    public function articleAdd() {
        return $this->fetch();
    }
    //添加资讯提交
    public function articleAddPost() {
        $val['title'] = input('post.title');
        $val['desc'] = input('post.desc');
        $val['status'] = input('post.status');
        checkPost($val);
        $val['content'] = input('post.content');
        $val['admin_id'] = session('admin_id');

        if(isset($_FILES['file'])) {
            $info = upload(array_keys($_FILES)[0],$this->upload_base_path);
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }
        try {
            Db::table('mp_article')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([]);

    }
    //修改资讯页面
    public function articleDetail() {
        $article_id = input('param.id');
        try {
            $exist = Db::table('mp_article')->where('id',$article_id)->find();
            if(!$exist) {
                $this->error('非法操作');
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }
    //修改资讯提交
    public function articleModPost() {
        $val['title'] = input('post.title');
        $val['desc'] = input('post.desc');
        $val['status'] = input('post.status');
        $val['id'] = input('post.id');
        checkPost($val);
        $val['content'] = input('post.content');
        $val['admin_id'] = session('admin_id');

        if(isset($_FILES['file'])) {
            $info = upload('file',$this->upload_base_path);
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }
        try {
            $exist = Db::table('mp_article')->where('id',$val['id'])->find();
            Db::table('mp_article')->update($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        if(isset($val['pic'])) {
                @unlink($exist['pic']);
        }
        return ajax();

    }
    //删除资讯
    public function articleDel() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_article')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }
            Db::table('mp_article')->where('id',$val['id'])->delete();
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        @unlink($exist['pic']);
        return ajax([],1);
    }
    //停用资讯
    public function articleHide()
    {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_article')->where('id', $val['id'])->find();
            if (!$exist) {
                return ajax('非法操作', -1);
            }
            Db::table('mp_article')->where('id', $val['id'])->update(['status' => 0]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    //启用资讯
    public function articleShow() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $exist = Db::table('mp_article')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }
            Db::table('mp_article')->where('id',$val['id'])->update(['status'=>1]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

}