<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/15
 * Time: 16:14
 */
namespace app\estate\controller;

use think\Db;
class Content extends Base {

    public function resourceList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['name','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_resource')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_resource')
                ->order(['id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function resourceAdd() {
        try {
            $where = [
                ['pcode','=','1201']
            ];
            $citylist = Db::table('mp_city')->where($where)->select();
            $tag = Db::table('mp_tag')->select();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('citylist',$citylist);
        $this->assign('tag',$tag);
        return $this->fetch();
    }

    public function resourceAddPost() {
        $val['name'] = input('post.name');
        $val['avr_price'] = input('post.avr_price',0);
        $val['min_area'] = input('post.min_area');
        $val['max_area'] = input('post.max_area');
        $val['type'] = input('post.type');
        $val['structure'] = input('post.structure');
        $val['apartment'] = input('post.apartment');
        $val['deliver'] = input('post.deliver',date('Y-m-d'));
        $val['open_date'] = input('post.open_date',date('Y-m-d'));
        $val['name'] = input('post.name');
        $val['region'] = input('post.region');
        $val['address'] = input('post.address');
        $val['saler_address'] = input('post.saler_address');
        $val['developer'] = input('post.developer');
        $val['building_type'] = input('post.building_type');
        $val['property'] = input('post.property');
        $val['trim_std'] = input('post.trim_std');
        $val['trim_price'] = input('post.trim_price',0);
        $val['trim_desc'] = input('post.trim_desc');
        $val['area_covered'] = input('post.area_covered');
        $val['floorage'] = input('post.floorage');
        $val['area_rate'] = input('post.area_rate');
        $val['green_rate'] = input('post.green_rate');
        $val['house_num'] = input('post.house_num');
        $val['building_num'] = input('post.building_num');
        $val['parking_num'] = input('post.parking_num');
        $val['realty_type'] = input('post.realty_type');
        $val['realty_comp'] = input('post.realty_comp');
        $val['realty_price'] = input('post.realty_price',0);
        $val['heating_type'] = input('post.heating_type');
        $val['water_type'] = input('post.water_type');
        $val['power_type'] = input('post.power_type');
        $val['tags'] = input('post.tags',[]);
        $val['status'] = input('post.status',1);
        $val['desc'] = input('post.desc');
        checkInput($val);
        $val['create_time'] = time();
        if(!empty($val['tags']) && is_array($val['tags'])) {
            $val['tags'] = implode(',',$val['tags']);
        }else {
            $val['tags'] = '';
        }
        if(isset($_FILES['file'])) {
            $info = upload('file','res/estate/upload/');
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }else {
            return ajax('请上传图片',-1);
        }

        try {
            Db::table('mp_resource')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);

    }

    public function resourceDetail() {
        $id = input('param.id',0);
        try {
            $resWhere = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_resource')->where($resWhere)->find();
            if(!$exist) {
                die('操作异常');
            }
            $cityWhere = [
                ['pcode','=','1201']
            ];
            $citylist = Db::table('mp_city')->where($cityWhere)->select();
            $this->assign('citylist',$citylist);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function resourceMod() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_resource')->where($where)->find();
            if(!$exist) {
                return ajax('操作异常',-1);
            }
            if(isset($_FILES['file'])) {
                $info = upload('file','res/estate/upload/');
                if($info['error'] === 0) {
                    $val['pic'] = $info['data'];
                }else {
                    return ajax($info['msg'],-1);
                }
            }
            Db::table('mp_resource')->where($where)->update($val);
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

    public function resourceDel() {
        $val['id'] = input('post.id',0);
        try {
            $model = model('resource');
            $exist = Db::table('mp_resource')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            $model::destroy($val['id']);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

    public function apartmentList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['name','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_apartment')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_apartment')
                ->order(['id'=>'DESC'])
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function apartmentAdd() {
        return $this->fetch();
    }

    public function apartmentAddPost() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        checkInput($val);
        $val['create_time'] = time();
        if(isset($_FILES['file'])) {
            $info = upload('file','res/estate/upload/');
            if($info['error'] === 0) {
                $val['pic'] = $info['data'];
            }else {
                return ajax($info['msg'],-1);
            }
        }else {
            return ajax('请上传图片',-1);
        }

        try {
            Db::table('mp_apartment')->insert($val);
        }catch (\Exception $e) {
            if(isset($val['pic'])) {
                @unlink($val['pic']);
            }
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }

    public function apartmentDetail() {
        $id = input('param.id',0);
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('mp_apartment')->where($where)->find();
            if(!$exist) {
                die('操作异常');
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function apartmentMod() {
        $val['name'] = input('post.name');
        $val['desc'] = input('post.desc');
        $val['id'] = input('post.id');
        checkInput($val);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_apartment')->where($where)->find();
            if(!$exist) {
                return ajax('操作异常',-1);
            }
            if(isset($_FILES['file'])) {
                $info = upload('file','res/estate/upload/');
                if($info['error'] === 0) {
                    $val['pic'] = $info['data'];
                }else {
                    return ajax($info['msg'],-1);
                }
            }
            Db::table('mp_apartment')->where($where)->update($val);
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

    public function apartmentDel() {
        $val['id'] = input('post.id',0);
        try {
            $model = model('apartment');
            $exist = Db::table('mp_apartment')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            $model::destroy($val['id']);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax([],1);
    }



    private function sortMerge($node,$pid=0)
    {
        $arr = array();
        foreach($node as $key=>$v)
        {
            if($v['pid'] == $pid)
            {
                $v['child'] = $this->sortMerge($node,$v['id']);
                $arr[] = $v;
            }
        }
        return $arr;
    }

}