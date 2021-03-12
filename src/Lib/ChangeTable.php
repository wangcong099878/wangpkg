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
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAl\Schema\Table;
use Illuminate\Support\Facades\Artisan;


class ChangeTable
{

    //Wang\Pkg\Lib\CreateTable::run('/Users/wangcong/php/pkgdev/tabStruct.tab');
    public static function run($filepath, $saveBasePath = 'migrations', $connect = 'mysql')
    {

        //读取文件
        $file = new Filesystem();
        $content = $file->get($filepath);

        //切割表
        $tabStrList = explode('---', $content);

        //本次执行的表
        $currentTabList = [];
        foreach ($tabStrList as $k => $v) {
            if ($v != '') {

                $tabName = self::AnalysisTable($v, $saveBasePath, $currentTabList);
                $currentTabList[] = $tabName;
            }
        }

        $showCommand = 'php artisan migrate --path=database/' . $saveBasePath . "\n";

        #记录到操作记录中
        $log_path = 'migrate.log';

        $wangpkgPath = storage_path('logs/wangpkg');

        if (!file_exists($wangpkgPath)) {
            mkdir($wangpkgPath);
        }


        $showCommandLog = date('Y-m-d H:i:s')." ".$showCommand;
        file_put_contents($wangpkgPath.'/'.$log_path, $showCommandLog, FILE_APPEND);

        $migratePath = 'database/' . $saveBasePath;
/*        $exitCode = Artisan::call('migrate', [
             '--path' => $migratePath
        ]);*/

        $result = \Wang\Pkg\Lib\Shell::execArtisan('migrate','--path='.$migratePath.' '.'--pretend');

        if($result){
            //前缀
            file_put_contents($wangpkgPath.'/sql.log', "\n".$showCommandLog.$result."\n", FILE_APPEND);
        }
        echo "\n";


        echo $showCommand." \n";

    }

    public static function AnalysisTable($tabStr, $saveBasePath, $currentTabList = [])
    {
        //判断该表是否已经存在
        //
        $tabIsExist = false;
        $tableNameList = ManageDB::getTables();
        $tableIndexList = [];

        $tabName = '';
        $tableDescription = '';

        //表连接
        $connect = 'mysql';

        $tabStruct = [];

        $defaultMap = [
            'int' => 0,
            'bigint' => 0,
            'tinyint' => 0,
            'decimal' => '0.00',
            'int:u' => 0,
            'bigint:u' => 0,
            'tinyint:u' => 0,
            'decimal:u' => '0.00',
            'varchar' => '',
            'char' => '',
            'float' => '0.00',
            'double' => '0.00',
            'text'=>''
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
            //'text'=>65535
        ];
        $allowMap = [
            'int',
            'bigint',
            'tinyint',
            'decimal',
            'varchar',
            'char',
            'float',
            'double',
            'text',
            'longtext',
            'timestamp',
             'bigint:u',
            'tinyint:u',
            'int:u',
            'decimal:u',
            ];

        $indexMap = [];
        $stateMap = [];

        //处理回滚
        $deleteField = [];
        $deleteIndex = [];

        //$table->dropColumn('alipay');
        //$table->dropColumn(['votes', 'avatar', 'location']);

        //notice_one_b_unique
        //notice_one_e_f_unique

        //notice_one_a_index
        //notice_one_c_d_index

        //$table->dropUnique('users_email_unique');    #从 “users” 表中删除唯一索引
        //$table->dropIndex('geo_state_index');    #从 “geo” 表中删除普通索引

        $lineList = explode("\n", $tabStr);

        foreach ($lineList as $k => $v) {

            if (trim($v) == '') {
                continue;
            }

            //判断表名
            if (strpos($v, '#') === 0) {
                $tabInfo = substr($v, 1);
                $tabInfoArr = explode('=', $tabInfo);
                $tabName = isset($tabInfoArr[0]) ? $tabInfoArr[0] : '';
                $tableDescription = isset($tabInfoArr[1]) ? $tabInfoArr[1] : '';

                if (in_array($tabName, $tableNameList)) {
                    $tabIsExist = true;
                    $tableIndexList = ManageDB::getIndexInfo($tabName);
                }

                //本次执行判断
                if (in_array($tabName, $currentTabList)) {
                    $tabIsExist = true;
                }

                continue;
            }

            //过滤注释
            if (strpos($v, '/') === 0) {
                continue;
            }

            //过滤其他字符
            if (strpos($v, '```') === 0) {
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


                        $deleteIndexStr = implode($indexArr, '_');
                        $indexName = "{$tabName}_{$deleteIndexStr}_unique";

                        if (!in_array($indexName, $tableIndexList)) {
                            $indexMap[] = "\$table->unique({$indexArrStr});";
                            $deleteIndex[] = "\$table->dropUnique('{$indexName}');";
                        }

                    } else {
                        $indexName = "{$tabName}_{$index}_unique";
                        if (!in_array($indexName, $tableIndexList)) {
                            $indexMap[] = "\$table->unique('{$index}');";
                            $deleteIndex[] = "\$table->dropUnique('{$indexName}');";
                        }

                    }
                    continue;
                }

                //关联索引  组合索引
                if (strpos($v, ',') !== false) {
                    $indexArr = explode(',', $index);
                    $indexArrStr = ManageDB::arrToStr($indexArr, false);
                    $deleteIndexStr = implode($indexArr, '_');
                    $indexName = "{$tabName}_{$deleteIndexStr}_index";

                    if (!in_array($indexName, $tableIndexList)) {
                        $indexMap[] = "\$table->index({$indexArrStr});";
                        $deleteIndex[] = "\$table->dropIndex('{$indexName}');";
                    }
                } else {
                    $indexName = "{$tabName}_{$index}_index";

                    if (!in_array($indexName, $tableIndexList)) {
                        $indexMap[] = "\$table->index('{$index}');";
                        $deleteIndex[] = "\$table->dropIndex('{$indexName}');";
                    }

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
                $tabStructItem['type'] = $fieldInfo[2] ? $fieldInfo[2] : 'varchar';


                if(!in_array($fieldInfo[2],$allowMap)){
                    $tabStructItem['type'] = 'varchar';
                }

            } else {
                $tabStructItem['type'] = 'varchar';
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

        $originalFieldList = [];
        if ($tabIsExist) {
            //查询原表字段是否存在
            $originalFieldList = ManageDB::getFieldList($tabName);
            //读取模板信息
            $content = $file->get(__DIR__ . '/stubs/change.stub');
        } else {
            //读取模板信息
            $content = $file->get(__DIR__ . '/stubs/create.stub');
        }


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
            //判断该字段时候存在原表
            $isChange = false;
            //如果是修改
            if (in_array($v['field'], $originalFieldList)) {
                $isChange = true;

                $oldCode = self::AnalysisField(ManageDB::getFieldInfo($tabName, $v['field']), $isChange);
                $newCode = self::AnalysisField($v, $isChange);

                if ($oldCode != $newCode) {
                    //获取原字段的回滚信息
                    $deleteIndex[] = $oldCode;
                    $DummyFields .= $newCode;
                }

            } else {
                $deleteIndex[] = "\$table->dropColumn('{$tabStructItem['field']}');";
                $DummyFields .= self::AnalysisField($v, $isChange);
            }

        }

        $content = str_replace('DummyFields', $DummyFields, $content);

        //生成索引 DummyIndexs
        $DummyIndexs = '';

        foreach ($indexMap as $v) {
            $DummyIndexs .= $v;
        }

        $content = str_replace('DummyIndexs', $DummyIndexs, $content);

        //回滚删除索引
        $DeleteIndexStr = '';

        foreach ($deleteIndex as $v) {
            $DeleteIndexStr .= $v;
        }

        $content = str_replace('DeleteIndex', $DeleteIndexStr, $content);

        //回滚删除字段
        $DeleteFieldStr = '';

        foreach ($deleteField as $v) {
            $DeleteFieldStr .= $v;
        }

        $content = str_replace('DeleteField', $DeleteFieldStr, $content);


        $u = (new \DateTime())->format('u');

        //类名
        //$DummyClass
        if ($tabIsExist) {
            $DummyClass = 'AddChangeTo' . ucfirst(ManageDB::camelize($tabName)) . $u . 'Table';
            $content = str_replace('DummyClass', $DummyClass, $content);
        } else {
            $DummyClass = 'Create' . ucfirst(ManageDB::camelize($tabName)) . $u . 'Table';
            $content = str_replace('DummyClass', $DummyClass, $content);
        }

        if ($tabIsExist) {
            //$datetime = (new \DateTime())->format('Y_m_d_His');
            $datetime = date('Y_m_d_His');
            //生成文件名
            $filename = $datetime . '_add_change_to_' . $tabName . '_' . $u . '_table.php';
        } else {
            //$datetime = (new \DateTime())->format('Y_m_d_His');
            $datetime = date('Y_m_d_His', time() - 1000);
            //生成文件名
            $filename = $datetime . '_create_' . $tabName . '_' . $u . '_table.php';
        }


        $basePath = database_path($saveBasePath);

        if (!file_exists($basePath)) {
            mkdir($basePath);
        }

        $savePath = $basePath . '/' . $filename;

        usleep(10);
        if ($DummyFields || $DummyIndexs) {
            $file->put($savePath, $content);
        }
        return $tabName;

    }

    //Wang\Pkg\Lib\ChangeTable::createMigration('user_bonus');
    public static function createMigration($tabName)
    {
        $tabStr = "\n";
        //获取单张表的
        $table = \DB::getDoctrineSchemaManager()->listTableDetails($tabName);
        $tabStr .= "#" . $table->getName() . '=' . $table->getComment() . "\n";

        foreach ($table->getColumns() as $column) {
            $fieldInfo = ManageDB::AnalysisField($column);
            if (in_array($fieldInfo['field'], ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $tabStr .= "{$fieldInfo['field']},{$fieldInfo['describe']},{$fieldInfo['type']},{$fieldInfo['length']},{$fieldInfo['default']}\n";
        }

        foreach ($table->getIndexes() as $index) {
            if ($index->isPrimary()) {
                continue;
            }

            $unique = '';
            if ($index->isUnique()) {
                $unique = ':unique';

            }
            $columns = $index->getColumns();

            if (count($columns) > 1) {
                $tabStr .= '-' . implode(',',$columns) . $unique . "\n";
            }else{
                $tabStr .= '-' . $columns[0] . $unique . "\n";
            }

        }

        return $tabStr;


        //获取所有表的
        //$tables = \DB::getDoctrineSchemaManager()->listTables();

        /*        foreach ($tables as $table) {
                    echo $table->getName() . " columns:\n\n";
                    echo $table->getComment() . "\n\n";
                    foreach ($table->getColumns() as $column) {
                        echo ' - ' . $column->getName() . "\n";
                    }
                }*/

        /*        //读取所有字段结构  读取表描述 以及字段结构  读取索引列表

                //字段列表   DummyFields
                $DummyFields = '';

                $fieldInfos = ManageDB::getFields($tabName);


                //print_r($fieldInfos);

                foreach ($fieldInfos as $k => $v) {
                    if(in_array($v['field'],['id','created_at','updated_at'])){
                        continue;
                    }

                    $newCode = self::AnalysisField($v, false);

                    $DummyFields .= $newCode;
                }

                //echo $DummyFields;
                $indexes = \DB::getDoctrineSchemaManager()->listTableIndexes($tabName);
                $indexList = [];
                foreach ($indexes as $index) {
                    print_r($index->getColumns());
                    echo $index->getName() . ': ' . ($index->isUnique() ? 'unique' : 'not unique') . "\n";
                    //$indexList[] = $index->getName();
                }


                print_r($indexList);*/

        //生成单张表的migration

        //生成多张表的migration

        #用 ManageDB  就可以实现大部分功能  不要重新生成Model
    }

    public static function AnalysisField($v, $isChange)
    {
        $existField = '';
        if ($isChange) {
            $existField = '->change()';
        }

        switch ($v['type']) {
            case 'bigint':
                return "\$table->bigInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'tinyint':
                return "\$table->tinyInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'int':
                return "\$table->integer('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'decimal':
                $places = explode('-', $v['length']);
                return "\$table->decimal('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;


            case 'bigint:u':
                return "\$table->unsignedBigInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'tinyint:u':
                return "\$table->unsignedTinyInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'int:u':
                return "\$table->unsignedInteger('{$v['field']}')->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'decimal:u':
                $places = explode('-', $v['length']);
                return "\$table->unsignedDecimal('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;


            case 'varchar':
                return "\$table->string('{$v['field']}',{$v['length']})->default('{$v['default']}')->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'timestamp':
                return "\$table->timestamp('{$v['field']}')->nullable()->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'float':
                $places = explode('-', $v['length']);
                return "\$table->float('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'double':
                $places = explode('-', $v['length']);
                return "\$table->double('{$v['field']}',{$places[0]},{$places[1]})->default({$v['default']})->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'char':
                return "\$table->char('{$v['field']}',{$v['length']})->default('{$v['default']}')->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'varchar':
                return "\$table->string('{$v['field']}',{$v['length']})->default('{$v['default']}')->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'text':
                return "\$table->text('{$v['field']}')->comment('{$v['describe']}'){$existField};\n";
                break;
            case 'longtext':
                return "\$table->longText('{$v['field']}')->comment('{$v['describe']}'){$existField};\n";
                break;
            default:
                return '';
        }
    }
}
