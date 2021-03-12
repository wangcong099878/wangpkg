<?php

namespace Wang\Pkg\Lib;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Schema\Index;

class ManageDB
{
    public static function addModels($arr)
    {
        foreach ($arr as $tabName) {
            self::addModel($tabName);
        }
    }

    public static function mapToStr($mapArr)
    {
        return '[' . trim(trim(var_export($mapArr, true), 'array ('), ')') . ']';
    }

    //Wang\Pkg\Lib\ManageDB::test();
    public static function test()
    {
        $map = [
            'user_info' => [
                1 => '美女',
                2 => '帅哥',
            ],
            'user' => [
                1 => '美女',
                2 => '帅哥',
            ]
        ];


        return self::createAssocMapList($map);
    }


    //Wang\Pkg\Lib\ManageDB::createAssocMap('user_info',[1,2,3]);
    public static function createAssocMap($field, $mapArr)
    {

        $fieldStr = self::camelize($field);
        $mapStr = self::mapToStr($mapArr);
        $mapStr = str_replace("\n", "\n\t", $mapStr);

        return <<<ABC

    public static \${$fieldStr}Map = {$mapStr};

ABC;
    }

    public static function createAssocMapList($arr)
    {
        $resultStr = '';
        foreach ($arr as $k => $v) {
            $resultStr .= self::createAssocMap($k, $v);
        }
        return $resultStr;
    }

    //Wang\Pkg\Lib\ManageDB::arrToStr([1,2,3]);
    public static function arrToStr($array, $assoc = true)
    {
        if ($assoc) {
            if (is_array($array)) {
                $str = "[";
                foreach ($array as $key => $value) {
                    if (is_string($key)) {
                        $str .= '\'' . $key . '\'=>' . self::arrToStr($value, $assoc) . ',';
                    } else {
                        $str .= $key . '=>' . self::arrToStr($value, $assoc) . ',';
                    }

                }
                $str = substr($str, 0, strlen($str) - 1);
                $str .= ']';
                return $str;
            } else {
                return '\'' . $array . '\'';
            }
        } else {
            if (is_array($array)) {
                $str = "[";
                foreach ($array as $key => $value) {
                    $str .= self::arrToStr($value, $assoc) . ',';
                }
                $str = substr($str, 0, strlen($str) - 1);
                $str .= ']';
                return $str;
            } else {
                return '\'' . $array . '\'';
            }
        }
    }


    //二维数组转化为字符串，中间用,隔开
    public static function arrToCode($array)
    {
        if (is_array($array)) {
            $str = "[";
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    $str .= '"' . $key . '"=>' . self::arrToCode($value) . ',';
                } else {
                    $str .= '' . $key . '=>' . self::arrToCode($value) . ',';
                }
            }
            $str = substr($str, 0, strlen($str) - 1);
            $str .= ']';
            return $str;
        } else {
            return '"' . $array . '"';
        }
    }

    //Wang\Pkg\Lib\ManageDB::addFillable('users');
    public static function addFillable($tabName)
    {
        $list = \Illuminate\Support\Facades\Schema::getColumnListing($tabName);

        foreach ($list as $k => $v) {
            if (in_array($v, ['id', 'created_at', 'updated_at'])) {
                unset($list[$k]);
            }
        }

        return self::arrToStr(array_values($list), false);
    }

    //Wang\Pkg\Lib\ManageDB::getTables();
    public static function getTables()
    {
        //

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        return $tables;
    }

    //查询表的索引信息
    //Wang\Pkg\Lib\ManageDB::getIndexInfo('notice');
    public static function getIndexInfo($tabName)
    {
        $indexes = \DB::getDoctrineSchemaManager()->listTableIndexes($tabName);
        $indexList = [];
        foreach ($indexes as $index) {
            //echo $index->getName() . ': ' . ($index->isUnique() ? 'unique' : 'not unique') . "\n";
            $indexList[] = $index->getName();
        }
        return $indexList;
    }

    //Wang\Pkg\Lib\ManageDB::getFields();
    public static function getFields($tabName)
    {

        $fieldInfos = [];
        $columns = \DB::getDoctrineSchemaManager()->listTableColumns($tabName);
        foreach ($columns as $column) {
            $fieldInfo = self::AnalysisField($column);
            $fieldInfos[] = $fieldInfo;
        }

        return $fieldInfos;
    }

    //Wang\Pkg\Lib\ManageDB::getFieldInfo('notice_two','a');
    public static function getFieldInfo($tabName, $field)
    {

        $fieldInfo = [];
        $columns = \DB::getDoctrineSchemaManager()->listTableColumns($tabName);
        foreach ($columns as $column) {
            if ($column->getName() == $field) {

                $fieldInfo = self::AnalysisField($column);
            }
        }

        return $fieldInfo;
    }


    //解析字段
    public static function AnalysisField($column)
    {
        $fieldInfo = [
            'field' => '',     //字段名
            'describe' => '',  //字段描述
            'type' => '',      //类型
            'length' => '',    //长度
            'default' => '',   //默认值
        ];

        $typeMap = [
            'integer' => 'int',
            'bigint' => 'bigint',
            'boolean' => 'tinyint',
            'decimal' => 'decimal',
            'string' => 'varchar',
            'string' => 'char',
            'float' => 'double',
            'double' => 'double',
            'datetime' => 'timestamp',
            'text' => 'text',
            'text' => 'longtext',
            'enum'=>'varchar'
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
            'text' => ''
        ];

        $defaultMap = [
            'int' => 0,
            'bigint' => 0,
            'tinyint' => 0,
            'decimal' => '0.00',
            'varchar' => '',
            'char' => '',
            'float' => '0.00',
            'double' => '0.00',
            'timestamp' => '',
            'text' => '',
            'longtext' => ''
        ];


        $columnInfoArr = $column->toArray();

        //如果类型不支持则使用varchar
        $typeName = $column->getType()->getName();
        //$type = isset($typeMap[$typeName]) ? $typeMap[$typeName] : $typeName;
        $type = isset($typeMap[$typeName]) ? $typeMap[$typeName] : 'varchar';

        if ($column->getType()->getName() == 'string') {
            if ($column->getFixed() == 1) {
                $type = 'char';
            } else {
                $type = 'varchar';
            }
        }

        if ($column->getType()->getName() == 'text') {
            if ($column->getLength() == 65535) {
                $type = 'text';
            } else {
                $type = 'longtext';
            }
        }

        $fieldInfo['field'] = $column->getName();
        $describe = $column->getComment();

        $describe = str_replace(',','，',$describe);
        $describe = str_replace("'",'‘',$describe);
        $describe = str_replace('"','‘',$describe);
        $describe = str_replace('-','',$describe);
        $fieldInfo['describe'] = $describe;
        if (in_array($type, ['decimal', 'double'])) {
            $fieldInfo['length'] = $columnInfoArr['precision'] . '-' . $columnInfoArr['scale'];
        } else {
            if ($column->getLength()) {
                $fieldInfo['length'] = $column->getLength();
            } else {
                $fieldInfo['length'] = isset($defaultLengthMap[$type]) ? $defaultLengthMap[$type] : '';
            }
        }


        //$fieldInfo['length'] = $column->getLength() ? $column->getLength() : $defaultLengthMap[$type];
        $fieldInfo['default'] = $column->getDefault() ? $column->getDefault() : $defaultMap[$type];




        if ($column->getUnsigned() == 1) {
            $type = $type . ':u';
        }
        $fieldInfo['type'] = $type;
        $fieldInfo['_'] = $columnInfoArr;

        return $fieldInfo;
    }

    //Wang\Pkg\Lib\ManageDB::getFieldList('notice');
    public static function getFieldList($tabName)
    {
        $fieldList = [];
        $columns = \DB::getDoctrineSchemaManager()->listTableColumns($tabName);
        foreach ($columns as $column) {
            $fieldList[] = $column->getName();
        }

        return $fieldList;
    }

    //Wang\Pkg\Lib\ManageDB::getStateMap('notice');
    public static function getStateMap($tabName)
    {
        $stateMap = [];
        $columns = \DB::getDoctrineSchemaManager()->listTableColumns($tabName);
        //$table = \DB::getDoctrineSchemaManager()->listTableDetails($tabName);
        //$tables = \DB::getDoctrineSchemaManager()->listTables();

        foreach ($columns as $column) {
            $field = $column->getName();
            $describe = $column->getComment();
            //echo $column->getName() . ': ' . $column->getType() . ":".$column->getComment()."\n";

            if (strpos($describe, '?') !== false && strpos($describe, '=') !== false) {
                $tempArr = explode('?', $describe);
                parse_str($tempArr[1], $out); //反解 http_build_str()
                $stateMap[$field] = $out;
            }
        }

        return $stateMap;
    }

    //Wang\Pkg\Lib\ManageDB::addModel('game_orders');
    public static function addModel($tabName, $addFillable = true, $cover = false, $expand = '', $connectName = 'mysql')
    {

        if (!file_exists(base_path('app/Models'))) {
            mkdir(base_path('app/Models'));
        }

        //$modelName = ucfirst(self::camelize(rtrim($tabName, 's')));
        $modelName = ucfirst(self::camelize($tabName));

        $path = base_path('app/Models/' . $modelName . '.php');

        //判断是否覆盖
        if (!$cover) {
            if (file_exists($path)) {
                return '';
            }
        }

        if (!$expand) {
            $expand = ManageDB::createAssocMapList(self::getStateMap($tabName));
        }


        /*        $expandStr = '';

                if (is_string($expand)){
                    $expandStr = $expand;
                }*/

        /*        if (is_callable($expand)) {
                    //pass functions (as a string) or arrays or closures(executable classes with __invoke)
                    $expandStr = call_user_func($expand);
                }*/


        $fillable = '';
        if ($addFillable) {
            $fillable = self::addFillable($tabName);
        }


        $str = <<<ABC
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class $modelName extends Model
{
    protected \$connection = '{$connectName}';
    protected \$table = '{$tabName}';
    public \$timestamps = true;
    protected \$fillable = {$fillable};

    {$expand}
}
ABC;

        file_put_contents($path, $str);
    }

    /**
     * 下划线转驼峰
     */
    //ucfirst(Wang\Pkg\Lib\ManageDB::camelize('add_log'));
    public static function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
    }

    /**
     *  驼峰命名转下划线命名
     */
    public static function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}
