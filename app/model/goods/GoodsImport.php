<?php

/**
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2019-2029 杭州牛之云科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: https://www.niushop.com
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 */

namespace app\model\goods;


use app\model\BaseModel;
use addon\virtualcard\model\VirtualGoods as VirtualCardGoods;

/**
 * 商品导入
 */
class GoodsImport extends BaseModel
{
    /**
     * 读取excel中的数据
     * @param $excel_path
     * @return array
     * @throws \PHPExcel_Reader_Exception
     */
    public function readGoodsExcel($excel_path){
        if (!file_exists($excel_path)) return $this->error('', '商品导入Excel文件路径错误');

        $excel_reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

        try {
            $excel_reader->setReadDataOnly(true); // 只读取数据,会忽略所有空白行
            $php_excel = $excel_reader->load($excel_path);
            $goods_data = $this->getSheetData($php_excel, 0);
            $sku_data = $this->getSheetData($php_excel, 1);

            $data = [
                'original_goods_data' => $goods_data,
                'original_sku_data' => $sku_data
            ];

            $goods_field = $goods_data[1];
            $sku_field = $sku_data[1];

            unset($goods_data[1], $goods_data[2], $sku_data[1], $sku_data[2]); // 移除表头

            $data['list'] = $goods_data;

            $sku_temp = [];
            foreach ($sku_data as $k => $sku_item) {
                $sku_item = array_combine($sku_field, $sku_item);
                $sku_data[$k] = $sku_item;
                if (isset($sku_temp[ 'goods_' . $sku_item['goods_number'] ])) {
                    array_push($sku_temp[ 'goods_' . $sku_item['goods_number'] ], $sku_item);
                } else {
                    $sku_temp[ 'goods_' . $sku_item['goods_number'] ] = [ $sku_item ];
                }
            }

            foreach ($data['list'] as $k => $item) {
                $item = array_combine($goods_field, $item);
                $data['list'][$k] = $item;
                if (isset($item['is_many_sku']) && $item['is_many_sku'] && isset($sku_temp[ 'goods_' . $item['goods_number'] ])) {
                    $data['list'][$k]['sku'] = $sku_temp[ 'goods_' . $item['goods_number'] ];
                }
            }
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('', $e->getMessage().$e->getFile().$e->getLine());
        }
    }

    /**
     * 将图表中的数据读取出来
     * @param $excel_reader
     * @param int $sheet
     */
    public function getSheetData($php_excel, $sheet = 0){
        $all_column = $php_excel->getSheet($sheet)->getHighestColumn(); //取得最大的列号
        $all_row = $php_excel->getSheet($sheet)->getHighestRow(); //取得一共有多少行

        $data = [];
        for ($row = 1; $row <= $all_row; $row++) {
            //从A列读取数据
            for ($col = 'A'; $col <= $all_column; $col++) {
                // 读取单元格
                $data[$row][] = trim((string)$php_excel->getSheet($sheet)->getCell("$col$row")->getValue());
            }
        }
        return $data;
    }

    /**
     * 导入商品
     * @param $data
     * @param $site_id
     * @param $goods_class
     * @return array
     */
    public function importGoods($data, $site_id, $goods_class){
        switch ($goods_class) {
            case 1:
                $name = '实物商品导入';
                $goods_class = new Goods();
                break;
            case 2:
                $name = '虚拟商品导入';
                $goods_class = new VirtualGoods();
                break;
            case 3:
                $name = '卡密商品导入';
                $goods_class = new VirtualCardGoods();
                break;
        }

        $import_data = [
            'site_id' => $site_id,
            'import_time' => time(),
            'success_num' => 0,
            'record_name' => $name,
            'fail_num' => 0,
            'fail_data' => [],
        ];
        // 执行导入
        foreach ($data['list'] as $key => $item) {
            $res = $goods_class->importGoods($item, $site_id);
            if ($res['code'] == 0) {
                $import_data['success_num'] += 1;
            } else {
                array_push($import_data['fail_data'], [
                    'index' => $key,
                    'reason' => $res['message']
                ]);
                $import_data['fail_num'] += 1;
            }
        }
        unset($data['list']);

        // 添加导入记录
        $import_data['data'] = json_encode($data);
        $import_data['fail_data'] = empty($import_data['fail_data']) ? '' : json_encode($import_data['fail_data'], JSON_UNESCAPED_UNICODE);
        model('goods_import_record')->add($import_data);

        return $this->success(['error_count' => $import_data['fail_num'], 'success_count' => $import_data['success_num']]);
    }

    /**
     * 获取导入记录
     * @param array $condition
     * @param int $page
     * @param int $list_rows
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getImportPageList($condition = [], $page = 1, $list_rows = PAGE_LIST_ROWS, $field = true, $order = 'import_time desc'){
        $data = model('goods_import_record')->pageList($condition, $field, $order, $page, $list_rows);
        return $this->success($data);
    }

    /**
     * 下载失败数据
     * @param $id
     * @param $site_id
     */
    public function downloadFailData($id, $site_id){
        $info = model('goods_import_record')->getInfo([ ['id', '=', $id],['site_id', '=', $site_id], ['fail_num', '>', 0] ], 'data,fail_data');
        if (empty($info)) return $this->error('', '未获取到导出记录');

        $original_data = json_decode($info['data'], true);
        $fail_data = json_decode($info['fail_data'], true);

        $field = $original_data['original_goods_data'][1];
        $header = $original_data['original_goods_data'][2];
        array_push($field, 'reason');
        array_push($header, '失败原因（再次上传前请先删除该列）');

        // 需要导出的商品数据
        $export_goods = [
            $field,
            $header
        ];
        // 需要导出的规格数据
        $export_sku = [
            $original_data['original_sku_data'][1],
            $original_data['original_sku_data'][2]
        ];

        // 处理sku数据
        unset($original_data['original_sku_data'][1], $original_data['original_sku_data'][2]);
        $sku_data = $original_data['original_sku_data'];
        $sku_temp = [];
        foreach ($sku_data as $k => $sku_item) {
            $sku = array_combine($export_sku[0], $sku_item);
            if (isset($sku_temp[ 'goods_' . $sku['goods_number'] ])) {
                array_push($sku_temp[ 'goods_' . $sku['goods_number'] ], $sku_item);
            } else {
                $sku_temp[ 'goods_' . $sku['goods_number'] ] = [ $sku_item ];
            }
        }

        foreach ($fail_data as $item) {
            $goods = $original_data['original_goods_data'][ $item['index'] ];
            array_push($goods, $item['reason']);
            array_push($export_goods, $goods);

            $goods = array_combine($export_goods[0], $goods);
            if ($goods['is_many_sku'] && isset($sku_temp[ 'goods_' . $goods['goods_number'] ])) {
                $export_sku = array_merge($export_sku, $sku_temp[ 'goods_' . $goods['goods_number'] ]);
            }
        }

        $letter = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

        $file_name = '商品导入失败数据-'.date('YmdHis');
        $php_excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $php_excel->getProperties()->setTitle($file_name); //设置标题
        // 设置当前sheet
        $php_excel->setActiveSheetIndex(0);
        // 设置当前sheet的名称
        $php_excel->getActiveSheet()->setTitle('商品');

        for ($i = 0; $i < count($export_goods); $i++) {
            $item = $export_goods[$i];
            for ($j = 0; $j < count($item); $j++) {
                $php_excel->getActiveSheet()->setCellValue($letter[$j] . ($i + 1), $item[$j]);
            }
        }

        // 设置当前sheet
        $php_excel->createSheet(1);
        $php_excel->setActiveSheetIndex(1);
        // 设置当前sheet的名称
        $php_excel->getActiveSheet()->setTitle('规格');
        for ($i = 0; $i < count($export_sku); $i++) {
            $item = $export_sku[$i];
            for ($j = 0; $j < count($item); $j++) {
                $php_excel->getActiveSheet()->setCellValue($letter[$j] . ($i + 1), $item[$j]);
            }
        }

        ob_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx');
        header('Cache-Control: max-age=1');
        $objWriter =  \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($php_excel, 'Xlsx');
        $objWriter->save('php://output');
    }
}
