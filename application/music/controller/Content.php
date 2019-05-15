<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/15
 * Time: 16:14
 */
namespace app\music\controller;

use think\Db;
class Content extends Base {

    public function instrumentList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['name','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_instrument')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_instrument')
                ->order(['id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function instrumentAdd() {
        return $this->fetch();
    }

    public function instrumentAddPost() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        checkInput($val);
        $val['create_time'] = time();
        if(isset($_FILES['file'])) {
            $info = upload('file','res/music/upload/');
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }else {
            return ajax('请上传图片',-1);
        }

        try {
            Db::table('mp_instrument')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);

    }

    public function instrumentDetail() {
        $id = input('param.id',0);
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_instrument')->where($where)->find();
            if(!$exist) {
                die('操作异常');
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function instrumentMod() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_instrument')->where($where)->find();
            if(!$exist) {
                return ajax('操作异常',-1);
            }
            if(isset($_FILES['file'])) {
                $info = upload('file','res/music/upload/');
                if($info['error'] === 0) {
                    $val['pic'] = $info['data'];
                }else {
                    return ajax($info['msg'],-1);
                }
            }
            Db::table('mp_instrument')->where($where)->update($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        if(isset($val['pic'])) {
            @unlink($exist['pic']);
        }
        return ajax([],1);
    }

    public function instrumentDel() {
        $val['id'] = input('post.id',0);
        try {
            $model = model('Instrument');
            $exist = Db::table('mp_instrument')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            $model::destroy($val['id']);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

    public function teacherList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['name','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_teacher')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_teacher')
                ->order(['id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function teacherAdd() {
        return $this->fetch();
    }

    public function teacherAddPost() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        checkInput($val);
        $val['create_time'] = time();
        if(isset($_FILES['file'])) {
            $info = upload('file','res/music/upload/');
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }else {
            return ajax('请上传图片',-1);
        }

        try {
            Db::table('mp_teacher')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

    public function teacherDetail() {
        $id = input('param.id',0);
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_teacher')->where($where)->find();
            if(!$exist) {
                die('操作异常');
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function teacherMod() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_teacher')->where($where)->find();
            if(!$exist) {
                return ajax('操作异常',-1);
            }
            if(isset($_FILES['file'])) {
                $info = upload('file','res/music/upload/');
                if($info['error'] === 0) {
                    $val['pic'] = $info['data'];
                }else {
                    return ajax($info['msg'],-1);
                }
            }
            Db::table('mp_teacher')->where($where)->update($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        if(isset($val['pic'])) {
            @unlink($exist['pic']);
        }
        return ajax([],1);
    }

    public function teacherDel() {
        $val['id'] = input('post.id',0);
        try {
            $model = model('Teacher');
            $exist = Db::table('mp_teacher')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            $model::destroy($val['id']);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

}