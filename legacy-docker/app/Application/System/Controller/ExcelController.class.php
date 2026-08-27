<?php

namespace System\Controller;
use Think\Controller;
class ExcelController extends Controller {
    public function __construct() {
        Vendor("PHPExcel.PHPExcel"); //引入phpexcel类(注意你自己的路径)
        Vendor("PHPExcel.PHPExcel.IOFactory");
        Vendor("PHPExcel.PHPExcel.Style.Alignment");
    }
    // 导入excel
    public function read($filename, $encode, $file_type) {
        if (strtolower($file_type) == 'xls') {//判断excel表类型为2003还是2007
            Vendor("Excel.PHPExcel.Reader.Excel5");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        } elseif (strtolower($file_type) == 'xlsx') {
            Vendor("Excel.PHPExcel.Reader.Excel2007");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }
    // 导出excel
    public function export_excel($exceltitle,$title, $data, $name,$type) {
        error_reporting(E_ALL);
        date_default_timezone_set('Europe/London');
        $name = iconv('utf-8', 'gbk', $name);
        $objPHPExcel = new \PHPExcel();
        $PHPExcel_Style_Alignment=new \PHPExcel_Style_Alignment();
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical($PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
        /*  $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical($PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
         $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal($PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:H1')->getFont()->setBold(true); //加粗

        $count=count($data)+1;
        $objPHPExcel->getActiveSheet()->getStyle('A1:H'.$count)->getAlignment()->setWrapText(true);//设置单元格“自动换行”属性
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);//换行必要设置  文本用"\n"
        */
        $objPHPExcel->setActiveSheetIndex(0);
        $arr_abc = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $num = 0;
        if ($type==1) {
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
            $arrhb=array();
            foreach($exceltitle as $k =>$v){
              $objPHPExcel->getActiveSheet()->setCellValue($arr_abc[$num].'1', $v);
                  foreach($data as $xk=>$xv){
                        $picnum = sizeof($xv['piclist']);
                        $num_val = $xv[$k];
                        if ($k=='piclist') {
                            foreach ($num_val as $k1 => $v1) {
                                // $objDrawing = new \PHPExcel_Worksheet_Drawing();
                                // $objDrawing->setPath('.'.$v1);
                                // $objDrawing->setHeight(50);
                                // $objDrawing->setCoordinates($arr_abc[$num].($xk+2+$k1));
                                // $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                                // $objPHPExcel->getActiveSheet()->getRowDimension( $xk+2+$k1 )->setRowHeight(60);
                                $objPHPExcel->getActiveSheet()->setCellValue($arr_abc[$num].($xk+2+$k1), $v1);
                                $objPHPExcel->getActiveSheet()->getCell($arr_abc[$num].($xk+2+$k1))->getHyperlink()->setUrl(C('SITEURL').$v1);
                            }
                        }else{
                            $objPHPExcel->getActiveSheet()->setCellValueExplicit($arr_abc[$num].($xk+2), $num_val);
                            if ($picnum>0) {
                                array_push($arrhb, $arr_abc[$num].($xk+2).':'.$arr_abc[$num].($xk+2+$picnum-1));
                            }
                        }
                  }
                  $num++;
            }
            $arrhb=array_unique($arrhb);
            // var_dump($arrhb);exit;
            foreach ($arrhb as $k => $v) {
                $objPHPExcel->getActiveSheet()->mergeCells($v);
            }
        }else{
            foreach($exceltitle as $k =>$v){
              $objPHPExcel->getActiveSheet()->setCellValue($arr_abc[$num].'1', $v);
              foreach($data as $xk=>$xv){
                    $num_val = $xv[$k];
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit($arr_abc[$num].($xk+2), $num_val);
                 /*   $objPHPExcel->getActiveSheet()->setCellValueExplicit($arr_abc[$num].($xk+2), $num_val,\PHPExcel_Cell_DataType::TYPE_STRING);//设置数字的科学计数法显示为文本*/
              }
              $num++;
            }
        }
    /*    if($title){
            foreach($title as $k=>$v){
                $objPHPExcel->getActiveSheet()->setCellValue($arr_abc[$num].'1', $v);
                foreach($data as $xk=>$xv){
                    $tmp_val='';
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit($arr_abc[$num].($xk+2), $tmp_val);
                }
                $num++;
            }
        }*/
        ob_end_clean();//防止导出excel乱码
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}