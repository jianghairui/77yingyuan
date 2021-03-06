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
        $order= [
            'f.sort'=>'ASC',
            'f.create_time'=>'DESC'
        ];

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
                ->order($order)
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function messageList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['m.content','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_message')->alias('m')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_message')->alias('m')
                ->join("mp_user u","m.uid=u.id","left")
                ->field("m.*,u.nickname,u.avatar")
                ->order(['m.create_time'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function messageDel() {
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            Db::table('mp_message')->where($where)->delete();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function filmAdd() {
        return $this->fetch();
    }

    public function filmAddPost() {
        $val['title'] = input('post.title');
        $val['status'] = input('post.status');
        $val['up_time'] = input('post.up_time');
        checkInput($val);
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
        $val['up_time'] = input('post.up_time');
        $val['id'] = input('post.id');
        checkInput($val);
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
    //隐藏影片
    public function filmHide() {
        $val['id'] = input('post.id');
        checkInput($val);
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
    //显示影片
    public function filmShow() {
        $val['id'] = input('post.id');
        checkInput($val);
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
    //删除影片
    public function filmDel() {
        $val['id'] = input('post.id');
        checkInput($val);
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

    //轮播图排序
    public function sortFilm() {
        $val['id'] = input('post.id');
        $val['sort'] = input('post.sort');
        checkInput($val);
        try {
            Db::table('mp_film')->update($val);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($val);
    }

    public function activityList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [
            ['f.del','=',0]
        ];
        if($param['search']) {
            $where[] = ['f.title','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_activity')->alias('f')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_activity')->alias('f')
                ->join("mp_admin a","f.admin_id=a.id","left")
                ->field("f.*,a.realname")
                ->order(['f.create_time'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function activityAdd() {
        return $this->fetch();
    }

    public function activityAddPost() {
        $val['title'] = input('post.title');
        $val['origin_price'] = input('post.origin_price');
        $val['price'] = input('post.price');
        $val['title'] = input('post.title');
        $val['start_time'] = input('post.start_time');
        $val['end_time'] = input('post.end_time');
        $val['explain'] = input('post.explain');
        $val['desc'] = input('post.desc');
        $val['status'] = input('post.status');
        checkInput($val);
        $val['admin_id'] = session('admin_id');
        $val['create_time'] = time();
        $val['start_time'] = strtotime($val['start_time']);
        $val['end_time'] = strtotime($val['end_time']);
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
            Db::table('mp_activity')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([]);
    }

    public function activityDetail() {
        $id = input('param.id');
        try {
            $where = [
                ['id','=',$id]
            ];
            $info = Db::table('mp_activity')->where($where)->find();
            if(!$info) {
                die('非法参数');
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$info);
        return $this->fetch();
    }

    public function activityModPost() {
        $val['title'] = input('post.title');
        $val['origin_price'] = input('post.origin_price');
        $val['price'] = input('post.price');
        $val['title'] = input('post.title');
        $val['start_time'] = input('post.start_time');
        $val['end_time'] = input('post.end_time');
        $val['explain'] = input('post.explain');
        $val['desc'] = input('post.desc');
        $val['status'] = input('post.status');
        $val['id'] = input('post.id');
        checkInput($val);
        $val['admin_id'] = session('admin_id');
        $val['start_time'] = strtotime($val['start_time']);
        $val['end_time'] = strtotime($val['end_time']);
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
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_activity')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            Db::table('mp_activity')->where($where)->update($val);
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

    //隐藏活动
    public function activityHide() {
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $exist = Db::table('mp_activity')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }

            $res = Db::table('mp_activity')->where('id',$val['id'])->update(['status'=>0]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        if($res !== false) {
            return ajax([],1);
        }else {
            return ajax([],-1);
        }
    }
    //显示活动
    public function activityShow() {
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $exist = Db::table('mp_activity')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }

            $res = Db::table('mp_activity')->where('id',$val['id'])->update(['status'=>1]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        if($res !== false) {
            return ajax([],1);
        }else {
            return ajax([],-1);
        }
    }
    //删除活动
    public function activityDel() {
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $exist = Db::table('mp_activity')->where('id','=',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }
            Db::table('mp_activity')->where('id','=',$val['id'])->update(['del'=>1]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax([],1);
    }

    public function sortActivity() {
        $val['id'] = input('post.id');
        $val['sort'] = input('post.sort');
        checkInput($val);
        try {
            Db::table('mp_activity')->update($val);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($val);
    }

    public function orderList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['o.order_sn|o.tel','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_order')->alias('o')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_order')->alias('o')
                ->join("mp_activity a","o.a_id=a.id","left")
                ->field("o.*,a.title")
                ->order(['o.id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function orderContact() {
        $id = input('post.id');
        try {
            $where = [
                ['id','=',$id]
            ];
            Db::table('mp_order')->where($where)->update(['contact'=>1]);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        return ajax();
    }

    public function joinList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['name|tel','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_join')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_join')
                ->order(['id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }


    public function recommend() {
        $id = input('post.id');
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_film')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            if($exist['recommend'] == 1) {
                Db::table('mp_film')->where($where)->update(['recommend'=>0]);
                return ajax(false);
            }else {
                Db::table('mp_film')->where($where)->update(['recommend'=>1]);
                return ajax(true);
            }
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
    }

}