<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/6/23
 * Time: 2:30
 */
namespace app\xiandu\controller;
use think\Db;

include ROOT_PATH . '/extend/phpspreadsheet/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel extends Common {

    public function orderList() {
        $param['search'] = input('param.search','');
        $param['status'] = input('param.status','');
        $param['logmin'] = input('param.logmin');
        $param['logmax'] = input('param.logmax');
        $param['refund_apply'] = input('param.refund_apply','');

        $where = " 1";
        $order = " ORDER BY `id` DESC";
        $orderby = " ORDER BY `d`.`id` DESC";
        if($param['status'] !== '') {
            $where .= " AND status=" . $param['status'];
        }
        if($param['refund_apply']) {
            $where .= " AND refund_apply=" . $param['refund_apply'];
        }
        if($param['logmin']) {
            $where .= " AND create_time>=" . strtotime(date('Y-m-d 00:00:00',strtotime($param['logmin'])));
        }
        if($param['logmax']) {
            $where .= " AND create_time<=" . strtotime(date('Y-m-d 23:59:59',strtotime($param['logmax'])));
        }
        if($param['search']) {
            $where .= " AND (pay_order_sn LIKE '%".$param['search']."%' OR tel LIKE '%".$param['search']."%')";
        }
        try {
            $sql = "SELECT 
`o`.`id`,`o`.`pay_order_sn`,`o`.`trans_id`,`o`.`receiver`,`o`.`tel`,`o`.`address`,`o`.`pay_price`,`o`.`total_price`,`o`.`carriage`,`o`.`create_time`,`o`.`refund_apply`,`o`.`reason`,`o`.`status`,`o`.`refund_apply`,`d`.`order_id`,`d`.`goods_id`,`d`.`num`,`d`.`unit_price`,`d`.`goods_name`,`d`.`attr`,`g`.`pics` 
FROM (SELECT * FROM mp_order WHERE " . $where . $order . " ) `o` 
LEFT JOIN `mp_order_detail` `d` ON `o`.`id`=`d`.`order_id`
LEFT JOIN `mp_goods` `g` ON `d`.`goods_id`=`g`.`id`
" . $orderby;
            $list = Db::query($sql);
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $order_id = [];
        $newlist = [];
        foreach ($list as $v) {
            $order_id[] = $v['id'];
        }
        $uniq_order_id = array_unique($order_id);
        foreach ($uniq_order_id as $v) {
            $child = [];
            foreach ($list as $li) {
                if($li['order_id'] == $v) {
                    $data['id'] = $li['id'];
                    $data['pay_order_sn'] = $li['pay_order_sn'];
                    $data['pay_price'] = $li['pay_price'];
                    $data['trans_id'] = $li['trans_id'];
                    $data['receiver'] = $li['receiver'];
                    $data['tel'] = $li['tel'];
                    $data['address'] = $li['address'];
                    $data['total_price'] = $li['total_price'];
                    $data['carriage'] = $li['carriage'];
                    $data['status'] = $li['status'];
                    $data['refund_apply'] = $li['refund_apply'];
                    $data['reason'] = $li['reason'];
                    $data['create_time'] = date('Y-m-d H:i',$li['create_time']);
                    $data_child['goods_id'] = $li['goods_id'];
                    $data_child['cover'] = unserialize($li['pics'])[0];
                    $data_child['goods_name'] = $li['goods_name'];
                    $data_child['num'] = $li['num'];
                    $data_child['unit_price'] = $li['unit_price'];
                    $data_child['total_price'] = sprintf ( "%1\$.2f",($li['unit_price'] * $li['num']));
                    $data_child['attr'] = $li['attr'];
                    $child[] = $data_child;
                }
            }
            $data['child'] = $child;
            $newlist[] = $data;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('订单统计');

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(120);
        $sheet->getColumnDimension('J')->setWidth(35);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(20);

        $sheet->getStyle('A:Z')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A1')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('B:C')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle('D:E')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

        $sheet->mergeCells('A1:L1');

        $sheet->setCellValue('A1', '先度文化订单统计' . date('Y-m-d H:i:s') . ' 制表人:' . session('realname'));
        $sheet->setCellValue('A2', '#');
        $sheet->setCellValue('B2', '订单号');
        $sheet->setCellValue('C2', '微信单号');
        $sheet->setCellValue('D2', '订单金额');
        $sheet->setCellValue('E2', '实际支付');
        $sheet->setCellValue('F2', '下单时间');
        $sheet->setCellValue('G2', '订单状态');
        $sheet->setCellValue('H2', '退款状态');
        $sheet->setCellValue('I2', '购买商品');
        $sheet->setCellValue('J2', '收货地址');
        $sheet->setCellValue('K2', '收货人');
        $sheet->setCellValue('L2', '手机号');
        $sheet->getStyle('A2:L2')->getFont()->setBold(true);

        $index = 3;
        foreach ($newlist as $v) {
            $sheet->setCellValue('A'.$index, $v['id']);
            $sheet->setCellValue('B'.$index, $v['pay_order_sn']);
            $sheet->setCellValue('C'.$index, $v['trans_id']);
            $sheet->setCellValue('D'.$index, $v['total_price']);
            $sheet->setCellValue('E'.$index, $v['pay_price']);
            $sheet->setCellValue('F'.$index,$v['create_time']);
            switch ($v['status']) {
                case 0:$status='待付款';break;
                case 1:$status='待发货';break;
                case 2:$status='待收货';break;
                case 3:$status='已完成';break;
                default:$status='';
            }
            $sheet->setCellValue('G'.$index, $status);
            switch ($v['refund_apply']) {
                case 1:$refund='退款中';break;
                case 2:$refund='已退款';break;
                case 3:$refund='未同意';break;
                default:$refund='';
            }
            $sheet->setCellValue('H'.$index, $refund);
            $str = '';
            foreach ($v['child'] as $vv) {
                $str .= $vv['goods_name'] .' 规格:'. $vv['attr'] .' 数量:'.  $vv['num'] .' 价格:'.  $vv['total_price'] . ';';
            }
            $sheet->setCellValue('I'.$index, $str);
            $sheet->setCellValue('J'.$index, $v['address']);
            $sheet->setCellValue('K'.$index, $v['receiver']);
            $sheet->setCellValue('L'.$index, $v['tel']);
            $index++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
//header(‘Content-Type:application/vnd.ms-excel‘);//告诉浏览器将要输出Excel03版本文件
        header('Content-Disposition: attachment;filename="订单统计'.date('Y-m-d').'.xlsx"');//告诉浏览器输出浏览器名称
        header('Cache-Control: max-age=0');//禁止缓存

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }


    public function userList() {

        $param['status'] = input('param.status','');
        $param['logmin'] = input('param.logmin');
        $param['logmax'] = input('param.logmax');
        $param['search'] = input('param.search');

        $where = [
            ['u.del','=',0]
        ];

        if(!is_null($param['status']) && $param['status'] !== '') {
            $where[] = ['u.status','=',$param['status']];
        }

        if($param['logmin']) {
            $where[] = ['u.create_time','>=',strtotime(date('Y-m-d 00:00:00',strtotime($param['logmin'])))];
        }

        if($param['logmax']) {
            $where[] = ['u.create_time','<=',strtotime(date('Y-m-d 23:59:59',strtotime($param['logmax'])))];
        }

        if($param['search']) {
            $where[] = ['u.tel','like',"%{$param['search']}%"];
        }

        try {
            $list = Db::table('mp_user')->alias('u')
                ->join('mp_userinfo i','u.id=i.uid','left')
                ->field('u.*,i.name,i.address,i.linkman,i.linktel,i.busine')
                ->where($where)->order(['u.id'=>'DESC'])->select();
        } catch(\Exception $e) {
            die($e->getMessage());
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('用户统计');

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(8);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(30);
        $sheet->getColumnDimension('L')->setWidth(12);

        $sheet->getStyle('A:Z')->applyFromArray([
            'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A1')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('C')->getNumberFormat()->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $sheet->mergeCells('A1:K1');

        $sheet->setCellValue('A1', '加工宝用户统计' . date('Y-m-d H:i:s') . ' 制表人:' . session('realname'));
        $sheet->setCellValue('A2', '#');
        $sheet->setCellValue('B2', '手机号');
        $sheet->setCellValue('C2', '企业名称');
        $sheet->setCellValue('D2', '企业地址');
        $sheet->setCellValue('E2', '负责人姓名');
        $sheet->setCellValue('F2', '联系电话');
        $sheet->setCellValue('G2', '是否会员');
        $sheet->setCellValue('H2', '到期时间');
        $sheet->setCellValue('I2', 'VIP_PV');
        $sheet->setCellValue('J2', '注册时间');
        $sheet->setCellValue('K2', '备注');
        $sheet->getStyle('A2:K2')->getFont()->setBold(true);

        $index = 3;
        foreach ($list as $v) {
            $sheet->setCellValue('A'.$index, $v['id']);
            $sheet->setCellValue('B'.$index, $v['tel']);
            $sheet->setCellValue('C'.$index, $v['name']);
            $sheet->setCellValue('D'.$index, $v['address']);
            $sheet->setCellValue('E'.$index, $v['linkman']);
            $sheet->setCellValue('F'.$index, $v['linktel']);
            $sheet->setCellValue('G'.$index, $v['vip'] ? '是' : '否');
            $sheet->setCellValue('H'.$index, date('Y-m-d',$v['vip_time']));
            $sheet->setCellValue('I'.$index, $v['vip_pv']);
            $sheet->setCellValue('J'.$index, date('Y-m-d H:i',$v['create_time']));
            $sheet->setCellValue('K'.$index, $v['desc']);
            $index++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
//header(‘Content-Type:application/vnd.ms-excel‘);//告诉浏览器将要输出Excel03版本文件
        header('Content-Disposition: attachment;filename="用户统计'.date('Y-m-d').'.xlsx"');//告诉浏览器输出浏览器名称
        header('Cache-Control: max-age=0');//禁止缓存

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }


}