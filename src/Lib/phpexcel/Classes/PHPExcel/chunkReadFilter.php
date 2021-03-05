<?php

/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2017/06/21
 * Time: 20:42
 */
include 'Reader/IReadFilter.php';

class chunkReadFilter implements PHPExcel_Reader_IReadFilter
{
    private $_startRow = 0;     // 开始行
    private $_endRow = 0;       // 结束行

    public function __construct($startRow, $chunkSize)
    {    // 我们需要传递：开始行号&行跨度(来计算结束行号)
        $this->_startRow = $startRow;
        $this->_endRow = $startRow + $chunkSize;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        /*if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) {
            return true;
        }  */
        if (($row >= $this->_startRow && $row < $this->_endRow)) {
            return true;
        }
        return false;
    }
}