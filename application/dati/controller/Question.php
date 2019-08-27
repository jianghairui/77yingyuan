<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/28
 * Time: 10:37
 */
namespace app\dati\controller;
use think\Db;
class Question extends Base {

    public function chapterList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        $count = Db::table('mp_chapter')->alias('c')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::query("SELECT c.*,c2.`count` FROM mp_chapter c 
LEFT JOIN (SELECT c_id,COUNT(c_id) AS `count` FROM mp_question GROUP BY c_id) c2 
ON c.id=c2.c_id");

//            $list = Db::table('mp_chapter')->alias('c')
//                ->join('mp_admin ad','c.admin_id=ad.id','left')
//                ->field('c.*,ad.realname')
//                ->where($where)
//                ->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }

        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function chapterAdd() {
        if(request()->isPost()) {
            $val['title'] = input('post.title');
            checkInput($val);
            $val['admin_id'] = session('admin_id');
            try {
                Db::table('mp_chapter')->insert($val);
            }catch (\Exception $e) {
                if(isset($val['pic'])) {
                    @unlink($val['pic']);
                }
                return ajax($e->getMessage(),-1);
            }
            return ajax([]);
        }
        return $this->fetch();
    }

    public function chapterDetail() {
        $question_id = input('param.id');
        try {
            $exist = Db::table('mp_chapter')->where('id',$question_id)->find();
            if(!$exist) {
                $this->error('非法操作');
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function chapterMod() {
        $val['title'] = input('post.title');
        $val['id'] = input('post.id');
        checkInput($val);
        $val['admin_id'] = session('admin_id');
        try {
            Db::table('mp_chapter')->update($val);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax();
    }

    public function chapterDel() {
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $exist = Db::table('mp_question')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }
            Db::table('mp_question')->where('id',$val['id'])->delete();
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        @unlink($exist['pic']);
        return ajax([],1);
    }


    //题目列表
    public function questionList()
    {
        $param['search'] = input('param.search','');
        $param['c_id'] = input('param.c_id','');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['title','like',"%{$param['search']}%"];
        }
        if($param['c_id']) {
            $where[] = ['c_id','=',$param['c_id']];
        }
        $count = Db::table('mp_question')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_question')
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
            $chapterlist = Db::table('mp_chapter')->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }

        $this->assign('list',$list);
        $this->assign('chapterlist',$chapterlist);
        $this->assign('page',$page);
        $this->assign('param',$param);
        return $this->fetch();
    }
    //添加题目页面
    public function questionAdd() {
        if(request()->isPost()) {
            $val['title'] = input('post.title');
            $val['c_id'] = input('post.c_id');
            $val['num'] = input('post.num');
            $val['key'] = input('post.key');
            switch ($val['num']) {
                case '2':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    ;break;
                case '3':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    $val['option_c'] = input('post.option_c');
                    ;break;
                case '4':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    $val['option_c'] = input('post.option_c');
                    $val['option_d'] = input('post.option_d');
                    ;break;
                case '5':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    $val['option_c'] = input('post.option_c');
                    $val['option_d'] = input('post.option_d');
                    $val['option_e'] = input('post.option_e');
                    ;break;
            }
            checkInput($val);
            $val['excerpt'] = input('post.excerpt');
            $val['admin_id'] = session('admin_id');
            try {
                Db::table('mp_question')->insert($val);
                Db::table('mp_chapter')->where('id','=',$val['c_id'])->setInc('q_num',1);
            }catch (\Exception $e) {
                return ajax($e->getMessage(),-1);
            }
            return ajax([]);
        }
        try {
            $list = Db::table('mp_chapter')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $param['c_id'] = input('param.c_id','');
        $this->assign('list',$list);
        $this->assign('param',$param);
        return $this->fetch();
    }
    //修改题目页面
    public function questionDetail() {
        $question_id = input('param.id');
        try {
            $exist = Db::table('mp_question')->where('id',$question_id)->find();
            if(!$exist) {
                $this->error('非法操作');
            }
            $list = Db::table('mp_chapter')->select();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$exist);
        $this->assign('list',$list);
        return $this->fetch();
    }
    //修改题目提交
    public function questionMod() {
        if(request()->isPost()) {
            $val['title'] = input('post.title');
            $val['c_id'] = input('post.c_id');
            $val['num'] = input('post.num');
            $val['key'] = input('post.key');
            $val['id'] = input('post.id');
            switch ($val['num']) {
                case '2':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    ;break;
                case '3':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    $val['option_c'] = input('post.option_c');
                    ;break;
                case '4':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    $val['option_c'] = input('post.option_c');
                    $val['option_d'] = input('post.option_d');
                    ;break;
                case '5':
                    $val['option_a'] = input('post.option_a');
                    $val['option_b'] = input('post.option_b');
                    $val['option_c'] = input('post.option_c');
                    $val['option_d'] = input('post.option_d');
                    $val['option_e'] = input('post.option_e');
                    ;break;
            }
            checkInput($val);
            $val['excerpt'] = input('post.excerpt');
            $val['admin_id'] = session('admin_id');
            try {
                Db::table('mp_question')->update($val);
            }catch (\Exception $e) {
                return ajax($e->getMessage(),-1);
            }
            return ajax([]);
        }
    }
    //删除题目
    public function questionDel() {
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $exist = Db::table('mp_question')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法操作',-1);
            }
            Db::table('mp_question')->where('id',$val['id'])->delete();
            Db::table('mp_chapter')->where('id','=',$exist['c_id'])->setDec('q_num',1);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        @unlink($exist['pic']);
        return ajax([],1);
    }



}