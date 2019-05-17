<?php
namespace app\music\controller;
use my\Auth;
use think\Db;
class Index extends Base
{
    //首页
    public function index() {
        $auth = new Auth();
        $authlist = $auth->getAuthList(session('admin_id'));
        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        try {
            $list = Db::table('mp_advise')->alias('a')
                ->join('mp_user u','a.uid=u.id','left')
                ->field('a.*,u.nickname,u.avatar')
                ->order(['a.id'=>'DESC'])
                ->limit(($curr_page - 1)*$perpage,$perpage)->select();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('authlist',$authlist);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //上传图片限制512KB
    public function uploadImage()
    {
        if (!empty($_FILES)) {
            if (count($_FILES) > 1) {
                return ajax('最多上传一张图片', 9);
            }
            $path = ajaxUpload(array_keys($_FILES)[0]);
            return ajax(['path' => $path]);
        } else {
            return ajax('请上传图片', 3);
        }
    }

//上传图片限制2048KB
    public function uploadImage2m()
    {
        if (!empty($_FILES)) {
            if (count($_FILES) > 1) {
                return ajax('最多上传一张图片', 9);
            }
            $path = ajaxUpload(array_keys($_FILES)[0], 2048);
            return ajax(['path' => $path]);
        } else {
            return ajax('请上传图片', 3);
        }
    }






}
