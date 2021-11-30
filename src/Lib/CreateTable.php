<?php
/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2019/05/13
 * Time: 11:38
 */

namespace Wang\Pkg\Lib;

use Illuminate\Support\Facades\Cache;
use Illuminate\Filesystem\Filesystem;

class CreateTable
{

    //Wang\Pkg\Lib\CreateTable::run('/Users/wangcong/php/pkgdev/tabStruct.tab');
    public static function run($filepath, $saveBasePath)
    {

        //读取文件
        $file = new Filesystem();
        $content = $file->get($filepath);

        //切割表
        $tabStrList = explode('---', $content);


        foreach ($tabStrList as $k => $v) {
            if ($v != '') {
                self::AnalysisTable($v, $saveBasePath);
            }
        }

        $showCommand = 'php artisan migrate --path=database/' . $saveBasePath . "\n";

        #记录到操作记录中
        $log_path = 'migrate.log';
        file_put_contents(base_path($log_path), $showCommand, FILE_APPEND);

        echo $showCommand;

    }

    public static function AnalysisTable($tabStr, $saveBasePath)
    {
        $tabName = '';
        $tableDescription = '';

        //表连接
        $connect = 'mysql';

        $tabStruct = [];

        $defaultMap = [
            'int' => 0,
            'bigint' => 0,
            'tinyint' => 0,
            'decimal' => 0.00,
            'varchar' => '',
            'char' => '',
            'float' => 0.00,
            'double' => 0.00,
        ];

        $defaultLengthMap = [
            'int' => 10,
            'bigint' => 20,
            'tinyint' => 4,
            'decimal' => '8-2',
            'varchar' => 255,
            'char' => 255,
            'float' => '8-2',
            'double' => '10-2',
        ];

        $indexMap = [];
        $stateMap = [];

        $lineList = explode("\n", $tabStr);

        foreach ($lineList as $k => $v) {

            if (trim($v) == '') {
                continue;
            }

            //判断表名
            if (strpos($v, '#') === 0) {
                //$tabInfo = str_replace('#', '', $v);
                $tabInfo = substr($v, 1);
                $tabInfoArr = explode('=', $tabInfo);
                $tabName = isset($tabInfoArr[0]) ? $tabInfoArr[0] : '';
                $tableDescription = isset($tabInfoArr[1]) ? $tabInfoArr[1] : '';
                continue;
            }

            //判断索引
            if (strpos($v, '-') === 0) {
                //去除定位符
                //$v[0]='';

                //去除定位符
                $index = substr($v, 1);

                //唯一索引
                if (strpos($v, ':unique') !== false) {
                    $index = str_replace(':unique', '', $index);
                    if (strpos($v, ',') !== false) {
                        $indexArr = explode(',', $index);
                        $indexArrStr = ManageDB::arrToStr($indexArr, false);
                        $indexMap[] = "\$table->unique({$indexArrStr});";
                    } else {
                        $indexMap[] = "\$table->unique('{$index}');";
                    }
                    continue;
                }

                //关联索引  组合索引
                if (strpos($v, ',') !== false) {
                    $indexArr = explode(',', $index);
                    $indexArrStr = ManageDB::arrToStr($indexArr, false);
                    $indexMap[] = "\$table->index({$indexArrStr});";
                } else {
                    $indexMap[] = "\$table->index('{$index}');";
                }

                continue;
            }

            //处理字段
            $fieldInfo = explode(',', $v);
            $tabStructItem = [
                'field' => '',     //字段名
                'describe' => '',  //字段描述
                'type' => '',      //类型
                'length' => '',    //长度
                'default' => '',   //默认值
            ];

            //字段名，字段描述，类型，长度，默认值
            //处理字段名称
            if (isset($fieldInfo[0])) {
                if ($fieldInfo[0] != '') {
                    $tabStructItem['field'] = $fieldInfo[0];
                }
            } else {
                continue;
            }

            //类型
            if (isset($fieldInfo[2])) {
                $tabStructItem['type'] = $fieldInfo[2] ? $fieldInfo[2] : 'int';
            } else {
                $tabStructItem['type'] = 'int';
            }

            //描述
            if (isset($fieldInfo[1])) {
                $tabStructItem['describe'] = $fieldInfo[1] ? $fieldInfo[1] : '';

                //处理描述中的状态码
                if (strpos($tabStructItem['describe'], '?') !== false && strpos($tabStructItem['describe'], '=') !== false) {
                    $tempArr = explode('?', $tabStructItem['describe']);
                    parse_str($tempArr[1], $out); //反解 http_build_str()
                    $stateMap[$tabStructItem['field']] = $out;
                }

            } else {

            }

            //长度
            if (isset($fieldInfo[3])) {
                $tabStructItem['length'] = $fieldInfo[3];
                if (!$fieldInfo[3]) {
                    $fieldType = str_replace(':u', '', $tabStructItem['type']);
                    $tabStructItem['length'] = isset($defaultLengthMap[$fieldType]) ? $defaultLengthMap[$fieldType] : '';
                }

            } else {
                $fieldType = str_replace(':u', '', $tabStructItem['type']);
                $tabStructItem['length'] = isset($defaultLengthMap[$fieldType]) ? $defaultLengthMap[$fieldType] : '';
            }

            //默认值
            if (isset($fieldInfo[4])) {
                $tabStructItem['default'] = $fieldInfo[4];
            } else {
                $tabStructItem['default'] = isset($defaultMap[$tabStructItem['type']]) ? $defaultMap[$tabStructItem['type']] : '';
            }

            array_push($tabStruct, $tabStructItem);


            //foreach end
        }


        if (!$tabName) {
            return;
        }

        $file = new Filesystem();

        ////读取模板信息
        $content = $file->get(__DIR__ . '/stubs/create.stub');


        //类名
        //$DummyClass
        $DummyClass = 'Create' . ucfirst(ManageDB::camelize($tabName)) . 'Table';
        $content = str_replace('DummyClass', $DummyClass, $content);

        //状态map  DummyStateMap
        if ($stateMap) {
            $DummyStateMap = '$map = ' . ManageDB::arrToCode($stateMap) . ';';
            $content = str_replace('DummyStateMap', $DummyStateMap, $content);
        } else {
            $content = str_replace('DummyStateMap', '$map = [];', $content);
        }


        //表名   DummyTableName
        $content = str_replace('DummyTableName', $tabName, $content);

        //表描述 DummyTabDescribe
        $content = str_replace('DummyTabDescribe', $tableDescription, $content);

        //字段列表   DummyFields
        $DummyFields = '';

        //处理字段列表
        foreach ($tabStruct as $v) {
            switch ($v['type']) {

                case 'bigint':
                    $DummyFields .= "\$table->bigInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'tinyint':
                    $DummyFields .= "\$table->tinyInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'int':
                    $DummyFields .= "\$table->integer('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'decimal':
                    $places = explode('-', $v['length']);
                    $DummyFields .= "\$table->decimal('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;


                case 'bigint:u':
                    $DummyFields .= "\$table->unsignedBigInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'tinyint:u':
                    $DummyFields .= "\$table->unsignedTinyInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'int:u':
                    $DummyFields .= "\$table->unsignedInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'decimal:u':
                    $places = explode('-', $v['length']);
                    $DummyFields .= "\$table->unsignedDecimal('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;


                case 'varchar':
                    $DummyFields .= "\$table->string('{$v['field']}',{$v['length']})->default('{$v['default']}')->comment('{$v['describe']}');\n";
                    break;
                case 'timestamp':
                    $DummyFields .= "\$table->timestamp('{$v['field']}')->nullable()->comment('{$v['describe']}');\n";
                    break;
                case 'float':
                    $places = explode('-', $v['length']);
                    $DummyFields .= "\$table->float('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'double':
                    $places = explode('-', $v['length']);
                    $DummyFields .= "\$table->double('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}');\n";
                    break;
                case 'char':
                    $DummyFields .= "\$table->char('{$v['field']}',{$v['length']})->default('{$v['default']}')->comment('{$v['describe']}');\n";
                    break;
                case 'varchar':
                    $DummyFields .= "\$table->string('{$v['field']}',{$v['length']})->default('{$v['default']}')->comment('{$v['describe']}');\n";
                    break;
                case 'text':
                    $DummyFields .= "\$table->text('{$v['field']}')->comment('{$v['describe']}');\n";
                    break;
                case 'longtext':
                    $DummyFields .= "\$table->longText('{$v['field']}')->comment('{$v['describe']}');\n";
                    break;
                default:
            }
        }

        $content = str_replace('DummyFields', $DummyFields, $content);

        //生成索引 DummyIndexs
        $DummyIndexs = '';

        foreach ($indexMap as $v) {
            $DummyIndexs .= $v;
        }

        $content = str_replace('DummyIndexs', $DummyIndexs, $content);


        //生成文件名
        $filename = date('Y_m_d_His') . '_create_' . $tabName . '_table.php';

        $basePath = database_path($saveBasePath);

        if (!file_exists($basePath)) {
            mkdir($basePath);
        }

        $savePath = $basePath . '/' . $filename;

        $file->put($savePath, $content);


    }
}
