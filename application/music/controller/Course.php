<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/16
 * Time: 9:27
 */
namespace app\music\controller;
use think\Db;
class Course extends Base {
    //分类列表
    public function cateList() {
        $where = [
            ['del','=',0]
        ];
        try {
            $list = Db::table('mp_course_cate')->where($where)->select();
        }catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
//添加分类
    public function cateAdd() {
        return $this->fetch();
    }
//添加分类POST
    public function cateAddPost() {
        $val['cate_name'] = input('post.cate_name');
        checkInput($val);
        $val['create_time'] = time();
        if(isset($_FILES['file'])) {
            $info = upload('file','res/music/upload/');
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }
        try {
            Db::table('mp_course_cate')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([]);
    }
//分类详情
    public function cateDetail() {
        $id = input('param.id');
        try {
            $info = Db::table('mp_course_cate')->where('id',$id)->find();
        }catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$info);
        return $this->fetch();
    }
//修改分类POST
    public function cateModPost() {
        $val['cate_name'] = input('post.cate_name');
        $val['id'] = input('post.id',0);
        checkInput($val);
        try {
            $exist = Db::table('mp_course_cate')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            if(isset($_FILES['file'])) {
                $info = upload('file','res/music/upload/');
                if($info['error'] === 0) {
                    $val['pic'] = $info['data'];
                }else {
                    return ajax($info['msg'],-1);
                }
            }
            Db::table('mp_course_cate')->where('id',$val['id'])->update($val);
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
//删除分类
    public function cateDel() {
        $id = input('post.id');
        try {
            $exist = Db::table('mp_course_cate')->where('id',$id)->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            Db::table('mp_course_cate')->where('id',$id)->update(['del'=>1]);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax();
    }

    public function courseList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [
            ['c.del','=',0]
        ];
        if($param['search']) {
            $where[] = ['c.title','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_course')->alias('c')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_course')->alias('c')
                ->join('mp_course_cate cate','c.cate_id=cate.id','left')
                ->field('c.*,cate.cate_name')
                ->order(['c.id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function courseAdd() {
        try {
            $where = [
                ['del','=',0]
            ];
            $list = Db::table('mp_course_cate')->where($where)->select();
        }catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function courseAddPost() {
        $val['title'] = input('post.title');
        $val['desc'] = input('post.desc');
        $val['price'] = input('post.price');
        $val['cate_id'] = input('post.cate_id');
        checkInput($val);
        $val['content'] = input('post.content');
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
            Db::table('mp_course')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

    public function courseDetail() {
        $id = input('param.id',0);
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_course')->where($where)->find();
            if(!$exist) {
                die('操作异常');
            }
            $where = [
                ['del','=',0]
            ];
            $list = Db::table('mp_course_cate')->where($where)->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->assign('info',$exist);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function courseMod() {
        $val['title'] = input('post.title');
        $val['desc'] = input('post.desc');
        $val['price'] = input('post.price');
        $val['cate_id'] = input('post.cate_id');
        $val['id'] = input('post.id');
        checkInput($val);
        $val['content'] = input('post.content');
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_course')->where($where)->find();
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
            Db::table('mp_course')->where($where)->update($val);
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

    public function courseDel() {
        $val['id'] = input('post.id',0);
        try {
            $exist = Db::table('mp_course')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            Db::table('mp_course')->where('id',$val['id'])->update(['del'=>1]);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

    public function orderList() {
        $param['logmin'] = input('param.logmin');
        $param['logmax'] = input('param.logmax');
        $param['search'] = input('param.search');
        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];

        $page['query'] = http_build_query(input('param.'));

        if($param['search']) {
            $where[] = ['c.title','like',"%{$param['search']}%"];
        }
        if($param['logmin']) {
            $where[] = ['o.create_time','>=',strtotime(date('Y-m-d 00:00:00',strtotime($param['logmin'])))];
        }
        if($param['logmax']) {
            $where[] = ['o.create_time','<=',strtotime(date('Y-m-d 23:59:59',strtotime($param['logmax'])))];
        }

        try {
            $count = Db::table('mp_order')->alias('o')->join("mp_course c","o.course_id=c.id","left")->where($where)->count();
            $page['count'] = $count;
            $page['curr'] = $curr_page;
            $page['totalPage'] = ceil($count/$perpage);
            $list = Db::table('mp_order')->alias('o')
                ->join("mp_course c","o.course_id=c.id","left")
                ->join("mp_user u","o.uid=u.id","left")
                ->where($where)
                ->order(['o.id'=>'DESC'])
                ->field("o.*,c.title,u.tel")
                ->limit(($curr_page-1)*$perpage,$perpage)
                ->select();
        }catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

}