<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/5/15
 * Time: 16:14
 */
namespace app\lucky\controller;

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
        $val['min_bro'] = input('post.min_bro');
        $val['max_bro'] = input('post.max_bro');
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
        $val['status'] = input('post.status',1);
        $val['tel'] = input('post.tel',1);
        checkInput($val);
        $val['desc'] = input('post.desc');
        if(!is_tel($val['tel'])) {
            return ajax('无效的手机号',-1);
        }
        $val['avr_total_price'] = $val['avr_price']*($val['min_area']+$val['max_area'])/2;
        $val['tags'] = input('post.tags',[]);
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
            $tag = Db::table('mp_tag')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $tags = explode(',',$exist['tags']);
        $this->assign('citylist',$citylist);
        $this->assign('info',$exist);
        $this->assign('tags',$tags);
        $this->assign('tag',$tag);
        return $this->fetch();
    }

    public function resourceMod() {
        $val['name'] = input('post.name');
        $val['avr_price'] = input('post.avr_price',0);
        $val['min_area'] = input('post.min_area');
        $val['max_area'] = input('post.max_area');
        $val['min_bro'] = input('post.min_bro');
        $val['max_bro'] = input('post.max_bro');
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
        $val['status'] = input('post.status',1);
        $val['tel'] = input('post.tel');
        $val['id'] = input('post.id');
        checkInput($val);
        $val['desc'] = input('post.desc');
        $val['tags'] = input('post.tags',[]);
        $val['avr_total_price'] = $val['avr_price']*($val['min_area']+$val['max_area'])/2;
        $val['create_time'] = time();
        if(!is_tel($val['tel'])) {
            return ajax('无效的手机号',-1);
        }
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
        }

        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_resource')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-1);
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
            $exist = Db::table('mp_resource')->where('id',$val['id'])->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            Db::table('mp_resource')->where('id',$val['id'])->delete();
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        @unlink($exist['pic']);
        return ajax([],1);
    }

    public function apartmentList() {
        $param['search'] = input('param.search');
        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',10);
        $where = [];
        if($param['search']) {
            $where[] = ['a.title','like',"%{$param['search']}%"];
        }
        $count = Db::table('mp_apartment')->alias('a')->where($where)->count();

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        try {
            $list = Db::table('mp_apartment')->alias('a')
                ->join("mp_resource r","a.res_id=r.id","left")
                ->order(['a.id'=>'DESC'])
                ->field("a.*,r.name")
                ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
        }catch (\Exception $e) {
            die('SQL错误: ' . $e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function apartmentAdd() {
        try {
            $list = Db::table('mp_resource')->field('id,name')->select();
            $tag = Db::table('mp_apartment_tag')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $this->assign('list',$list);
        $this->assign('tag',$tag);
        return $this->fetch();
    }

    public function apartmentAddPost() {
        $val['res_id'] = input('post.res_id');
        $val['title'] = input('post.title');
        $val['price'] = input('post.price');
        $val['area'] = input('post.area');
        $val['orientation'] = input('post.orientation');
        $val['structure'] = input('post.structure');
        $val['realty_type'] = input('post.realty_type');
        $val['status'] = input('post.status');
        checkInput($val);
        $val['create_time'] = time();
        $val['tags'] = input('post.tags',[]);
        if(!empty($val['tags']) && is_array($val['tags'])) {
            $val['tags'] = implode(',',$val['tags']);
        }else {
            $val['tags'] = '';
        }
        $image = input('post.pic_url',[]);
        $image_array = [];
        if(is_array($image) && !empty($image)) {
            if(count($image) > 9) {
                return ajax('最多上传9张图片',-1);
            }
            foreach ($image as $v) {
                if(!file_exists($v)) {
                    return ajax('无效的图片路径,请重新上传图片',-1);
                }
            }
            foreach ($image as $v) {
                $image_array[] = rename_file($v,'res/estate/upload/');
            }
        }else {
            return ajax('请上传图片',-1);
        }
        $val['pic'] = serialize($image_array);
        try {
            Db::table('mp_apartment')->insert($val);
        }catch (\Exception $e) {
            foreach ($image_array as $v) {
                @unlink($v);
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
            $list = Db::table('mp_resource')->field('id,name')->select();
            $tag = Db::table('mp_apartment_tag')->select();
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $tags = explode(',',$exist['tags']);
        $this->assign('info',$exist);
        $this->assign('list',$list);
        $this->assign('tag',$tag);
        $this->assign('tags',$tags);
        return $this->fetch();
    }

    public function apartmentMod() {
        $val['res_id'] = input('post.res_id');
        $val['title'] = input('post.title');
        $val['price'] = input('post.price');
        $val['area'] = input('post.area');
        $val['orientation'] = input('post.orientation');
        $val['structure'] = input('post.structure');
        $val['realty_type'] = input('post.realty_type');
        $val['status'] = input('post.status');
        $val['id'] = input('post.id');
        checkInput($val);
        $val['create_time'] = time();
        $val['tags'] = input('post.tags',[]);
        if(!empty($val['tags']) && is_array($val['tags'])) {
            $val['tags'] = implode(',',$val['tags']);
        }else {
            $val['tags'] = '';
        }
        $image = input('post.pic_url',[]);
        $image_array = [];
        if(is_array($image) && !empty($image)) {
            if(count($image) > 9) {
                return ajax('最多上传9张图片',-1);
            }
            foreach ($image as $v) {
                if(!file_exists($v)) {
                    return ajax('无效的图片路径,请重新上传图片',-1);
                }
            }
            foreach ($image as $v) {
                $image_array[] = rename_file($v,'res/estate/upload/');
            }
        }else {
            return ajax('请上传图片',-1);
        }
        $val['pic'] = serialize($image_array);
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('mp_apartment')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            $old_pics = unserialize($exist['pic']);
            Db::table('mp_apartment')->where($where)->update($val);
        }catch (\Exception $e) {
            foreach ($image_array as $v) {
                if(!in_array($v,$old_pics)) {
                    @unlink($v);
                }
            }
            return ajax($e->getMessage(),-1);
        }
        foreach ($old_pics as $v) {
            if(!in_array($v,$image_array)) {
                @unlink($v);
            }
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