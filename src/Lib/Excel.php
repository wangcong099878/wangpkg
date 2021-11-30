<?php

namespace Wang\Pkg\Lib;

include 'phpexcel/Classes/PHPExcel.php';

class Excel
{

    /**
     * 导入文件返回二维数组 以第一行作为key
     * @param type $path
     */
    //Wang\Pkg\Lib\Excel::getTitle();
    public static function getTitle($path,$titleMap=[])
    {
        //Wang\Pkg\Lib\Excel::getTitle(storage_path('app/data/liantong.csv'));
        //Wang\Pkg\Lib\Excel::getTitle(storage_path('app/data/liantong.csv'),['收货地址省份编码'=>'code']);
        /** Error reporting */
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        date_default_timezone_set('Asia/ShangHai');

        //获取后缀
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $inputFileName = $path;
        if ($ext == 'xls') {
            $objPHPExcelReader = \PHPExcel_IOFactory::createReader('Excel5');
            $PHPExcel = $objPHPExcelReader->load($inputFileName);
        }else if($ext == 'xlsx'){
            $objPHPExcelReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $PHPExcel = $objPHPExcelReader->load($inputFileName);
        } else if ($ext == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
                ->setDelimiter(',')
                ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
                ->setEnclosure('"')
                //->setLineEnding("\r\n")
                ->setSheetIndex(0);
            $PHPExcel = $objReader->load($path);
        } else {
            die('Not supported file types!');
        }


        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数

        $highestColumm++;

        $title = [];
        $result = [];

        /** 循环读取每个单元格的数据 */
        for ($row = 1; $row <= $highestRow; $row++) {//行数是以第1行开始
            $item = [];
            for ($column = 'A'; $column != $highestColumm; $column++) {//列数是以A列开始

                if ($row == 1) {
                    $column = trim($column);
                    $title[$column] = (string)$sheet->getCell($column . $row)->getValue();
                } else {
                    $key = trim($title[$column]);

                    if(isset($titleMap[$key])){
                        $key = $titleMap[$key];
                    }

                    $item[$key] = (string)$sheet->getCell($column . $row)->getValue();
                }
            }
            if ($row != 1) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public static function excelTime($t){
        return gmdate("Y-m-d H:i:s", \PHPExcel_Shared_Date::ExcelToPHP($t));
    }

    /**
     * 导入文件返回二维数组 以第一行作为key
     * @param type $path
     */
    //Wang\Pkg\Lib\Excel::importKv(storage_path('app/data/liantong.csv'));
    public static function importKv($path)
    {
        /** Error reporting */
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        date_default_timezone_set('Asia/ShangHai');

        //获取后缀
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $inputFileName = $path;
        if ($ext == 'xls') {
            $objPHPExcelReader = \PHPExcel_IOFactory::createReader('Excel5');
            $PHPExcel = $objPHPExcelReader->load($inputFileName);
        }else if($ext == 'xlsx'){
            $objPHPExcelReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $PHPExcel = $objPHPExcelReader->load($inputFileName);
        } else if ($ext == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
                ->setDelimiter(',')
                ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
                ->setEnclosure('"')
                //->setLineEnding("\r\n")
                ->setSheetIndex(0);
            $PHPExcel = $objReader->load($path);
        } else {
            die('Not supported file types!');
        }


        $sheet = $PHPExcel->getSheet(0); // 读取第一个工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数

        $highestColumm++;

        $title = [];
        $result = [];

        /** 循环读取每个单元格的数据 */
        for ($row = 1; $row <= $highestRow; $row++) {//行数是以第1行开始
            $item = [];
            for ($column = 'A'; $column != $highestColumm; $column++) {//列数是以A列开始

                if ($row == 1) {
                    $column = trim($column);
                    $title[$column] = (string)$sheet->getCell($column . $row)->getValue();
                } else {
                    $key = trim($title[$column]);
                    $item[$key] = (string)$sheet->getCell($column . $row)->getValue();
                }
            }
            if ($row != 1) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 导入文件返回二维数组
     * @param type $path
     */
    //Wang\Pkg\Lib\Excel::import(storage_path('app/data/liantong.csv'));
    public static function import($path)
    {
        /** Error reporting */
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);

        date_default_timezone_set('Asia/ShangHai');

        //获取后缀
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $inputFileName = $path;
        if ($ext == 'xls') {
            $objPHPExcelReader = \PHPExcel_IOFactory::createReader('Excel5');
            $PHPExcel = $objPHPExcelReader->load($inputFileName);
        }else if($ext == 'xlsx'){
            $objPHPExcelReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $PHPExcel = $objPHPExcelReader->load($inputFileName);
        } else if ($ext == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader('CSV')
                ->setDelimiter(',')
                ->setInputEncoding('GBK')//不设置将导致中文列内容返回boolean(false)或乱码
                ->setEnclosure('"')
                //->setLineEnding("\r\n")
                ->setSheetIndex(0);
            $PHPExcel = $objReader->load($path);
        } else {
            die('Not supported file types!');
        }

        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数


        $title = [];

        $result = [];
        $highestColumm++;

        /** 循环读取每个单元格的数据 */
        for ($row = 1; $row <= $highestRow; $row++) {//行数是以第1行开始
            $item = [];
            for ($column = 'A'; $column != $highestColumm; $column++) {         //列数是以A列开始
                $item[] = (string)$sheet->getCell($column . $row)->getValue();
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * 保存文件到指定目录  返回文件地址
     * @param type $filepath
     * @param type $data
     */
    public static function save($data, $filepath, $alias = [])
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);

        $objWriter = self::dataToXls($data, $alias,$ext);
        //输出到浏览器
        $objWriter->save($filepath);
    }

    /**
     * 生成下载的xml文件
     * @param type $data
     * @param type $filepath
     *         Excel::download($data, 'weimob.xls', [
     * 'id' => '渠道id',
     * 'ctime' => '创建时间',
     * 'dcode_name' => '渠道名称',
     * 'dcode_pic' => '二维码下载地址',
     * 'incre_count' => '净增长关注',
     * 'lose_count' => '流失数',
     * 'qrcode_type' => '二维码类型',
     * 'scan_count' => '扫描次数',
     * 'subscribe_count' => '新增关注数',
     * 'type' => '标签id',
     * 'valid_time' => '有效期限',
     * ]);
     *
     */
    public static function download($data, $filepath = 'yangxi.xls', $alias = [])
    {
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filepath . '"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $objWriter = self::dataToXls($data, $alias,$ext);



        //输出到浏览器
        $objWriter->save('php://output');
    }

    public static function dataToXls($data, $alias = [],$ext='xls')
    {

        /** Error reporting */
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Asia/ShangHai');



        $tmp = $data[0];
        $title = [];
        $showtitle = [];

        $i = "A";



        foreach ($tmp as $k => $v) {
            $title[$i] = $k;
            $showtitle[$i] = isset($alias[$k]) ? $alias[$k] : $k;
            $i++;
        }




        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Yangxi")
            ->setLastModifiedBy("Yangxi")
            ->setTitle("Yangxi Document")
            ->setSubject("Yangxi Document")
            ->setDescription("Yangxi Data.")
            ->setKeywords("Yangxi Data")
            ->setCategory("Yangxi Data");

        $highestColumm = count($title);
        $total = count($data);
        for ($row = 1; $row <= $total; $row++) {
            for ($column = 'A'; $column != $i; $column++) {
                $page = $row - 1;
                $psize = $row + 1;
                if ($row == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $row, (string)$showtitle[$column]);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $psize, (string)$data[$page][$title[$column]]);
                } else {
                    //echo $data[$page][$title[$column]];
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . $psize, (string)$data[$page][$title[$column]]);
                }
            }
        }

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('WeimobData');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        if ($ext == 'xls') {
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        }else if ($ext == 'xlsx') {
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }else{

        }



        return $objWriter;
    }

}
