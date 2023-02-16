<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/28
 * Time: 15:50
 */

namespace Wang\Pkg\Lib;

use SebastianBergmann\Diff\Output\AbstractChunkOutputBuilder;

class ShowDB
{

    //Wang\Pkg\Lib\ShowDB::showStruct();
    public static function showStruct($database = '')
    {
        if(!$database){
            $database = env('DB_DATABASE', '');
        }

        $filter = ['information_schema', 'mysql'];
        //过滤表名
        if (in_array($database, $filter)) {
            die("无法显示");
        }

        // 配置数据库
        $dbserver = env('DB_HOST', '127.0.0.1');
        $dbusername = env('DB_USERNAME', 'forge');
        $dbpassword = env('DB_PASSWORD', '');


        // 其他配置
        $title = $database . '项目数据库字典';

        $mysql_conn = mysqli_connect($dbserver, $dbusername, $dbpassword, $database) or die("Mysql connect is error.");

        mysqli_select_db($mysql_conn, $database);
        mysqli_query($mysql_conn, 'SET NAMES utf8');

        $table_result = mysqli_query($mysql_conn, 'show tables');

        // 取得所有的表名
        while ($row = mysqli_fetch_array($table_result)) {
            $tables [] ['TABLE_NAME'] = $row [0];
        }

        if (!isset($tables)) {
            die("暂无数据");
        }

        //查询表的所有索引 show index from table_name

        // 循环取得所有表的备注及表中列消息
        foreach ($tables as $k => $v) {
            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.TABLES ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
            $table_result = mysqli_query($mysql_conn, $sql);
            while ($t = mysqli_fetch_array($table_result)) {
                $tables [$k] ['TABLE_COMMENT'] = $t ['TABLE_COMMENT'];
            }

            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

            $fields = array();
            $field_result = mysqli_query($mysql_conn, $sql);
            while ($t = mysqli_fetch_array($field_result)) {
                $fields [] = $t;
            }
            $tables [$k] ['COLUMN'] = $fields;
        }
        mysqli_close($mysql_conn);


        print_r($tables);
    }

    public static function show()
    {
        /**
         * nginx添加
         * location / {
         * if (!-e $request_filename) {
         * rewrite  ^(.*)$  /index.php?s=$1  last;
         * break;
         * }
         * }
         * //打印数据库
         * dbshow.com/dbname/ip/user/pwd
         * //打印数据库中所有列表
         * dbshow.com/dblist.php
         */
        /**
         * 生成mysql数据字典
         */
        header("Content-type: text/html; charset=utf-8");

        $database = 'local_wbwan';

        if (isset($_GET['db'])) {
            $database = $_GET['db'];
        } else {
            $database = env('DB_DATABASE', '');
        }

        $filter = ['information_schema', 'mysql'];
        //过滤表名
        if (in_array($database, $filter)) {
            die("无法显示");
        }

        // 配置数据库
        $dbserver = env('DB_HOST', '127.0.0.1');
        $dbusername = env('DB_USERNAME', 'forge');
        $dbpassword = env('DB_PASSWORD', '');


        // 其他配置
        $title = $database . '项目数据库字典';

        $mysql_conn = mysqli_connect($dbserver, $dbusername, $dbpassword, $database) or die("Mysql connect is error.");

        mysqli_select_db($mysql_conn, $database);
        mysqli_query($mysql_conn, 'SET NAMES utf8');

        $table_result = mysqli_query($mysql_conn, 'show tables');

        // 取得所有的表名
        while ($row = mysqli_fetch_array($table_result)) {
            $tables [] ['TABLE_NAME'] = $row [0];
        }

        if (!isset($tables)) {
            die("暂无数据");
        }

        //查询表的所有索引 show index from table_name

        // 循环取得所有表的备注及表中列消息
        foreach ($tables as $k => $v) {
            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.TABLES ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
            $table_result = mysqli_query($mysql_conn, $sql);
            while ($t = mysqli_fetch_array($table_result)) {
                $tables [$k] ['TABLE_COMMENT'] = $t ['TABLE_COMMENT'];
            }

            $sql = 'SELECT * FROM ';
            $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
            $sql .= 'WHERE ';
            $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

            $fields = array();
            $field_result = mysqli_query($mysql_conn, $sql);
            while ($t = mysqli_fetch_array($field_result)) {
                $fields [] = $t;
            }
            $tables [$k] ['COLUMN'] = $fields;
        }
        mysqli_close($mysql_conn);


        $html = '';
// 循环所有表
        foreach ($tables as $k => $v) {
// $html .= '<p><h2>'. $v['TABLE_COMMENT'] . '&nbsp;</h2>';
            $html .= '<table  border="1" cellspacing="0" cellpadding="0" align="center">';
            $html .= '<caption>' . $v ['TABLE_NAME'] . '  ' . $v ['TABLE_COMMENT'] . '</caption>';


            //代码生成
            $tabName = ucfirst(\Wang\Pkg\Lib\ManageDB::camelize($v ['TABLE_NAME']));
            $str = '';
            foreach ($v ['COLUMN'] as $f) {
                if($f['COLUMN_NAME']!='created_at' && $f['COLUMN_NAME']!='updated_at' && $f['COLUMN_NAME']!='id'){
                    $str .= "\${$f ['COLUMN_NAME']}=\\request('{$f ['COLUMN_NAME']}');\n";
                }
            }
            $str .= "\n";
            $str .= "\$data=[\n";
            foreach ($v ['COLUMN'] as $f) {
                if($f['COLUMN_NAME']!='created_at' && $f['COLUMN_NAME']!='updated_at' && $f['COLUMN_NAME']!='id'){
                    $str .= "'{$f ['COLUMN_NAME']}'=>\${$f ['COLUMN_NAME']},\n";
                }
            }
            $str.="];\n";
            $str.="{$tabName}::create(\$data);\n";
            $str.="if({$tabName}::where(\$data)->exists()){Response::halt([],201,'已存在');}\n";
            $str .= "\n";
            $str .= "\$obj = new {$tabName}();\n";
            foreach ($v ['COLUMN'] as $f) {
                if($f['COLUMN_NAME']!='created_at' && $f['COLUMN_NAME']!='updated_at' && $f['COLUMN_NAME']!='id'){
                    $str .= "\$obj->{$f ['COLUMN_NAME']}=\${$f ['COLUMN_NAME']};\n";
                }
            }
            $str.="\n";
            //$str.="if(\$obj->save()){\n\n}else{\n\n}\n";
$resStr = <<<ABC
if(\$obj->save()){
    Response::halt([],0,'请求成功');
}else{
    Response::halt([],201,'请求失败');
}
ABC;

            $str.=$resStr;



            $str.="php artisan wangpkg:dmake {$tabName}Controller --model=App\\\\Models\\\\{$tabName} --title={$v ['TABLE_COMMENT']}\n";


            $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th>
    <th>允许非空</th>
    <th>自动递增</th><th>备注</th></tr>';
            $html .= '';

            foreach ($v ['COLUMN'] as $f) {
                $html .= '<tr><td class="c1">' . $f ['COLUMN_NAME'] . '</td>';
                $html .= '<td class="c2">' . $f ['COLUMN_TYPE'] . '</td>';
                $html .= '<td class="c3">&nbsp;' . $f ['COLUMN_DEFAULT'] . '</td>';
                $html .= '<td class="c4">&nbsp;' . $f ['IS_NULLABLE'] . '</td>';
                $html .= '<td class="c5">' . ($f ['EXTRA'] == 'auto_increment' ? '是' : '&nbsp;') . '</td>';
                $html .= '<td class="c6">&nbsp;' . $f ['COLUMN_COMMENT'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<div style="width: 880px;margin: 0 auto"><pre style="width: 100%;height: auto;">' . $str . '</pre></div>';
            $html .='</p>';
        }

// 输出
        echo '<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>' . $title . '</title>
<style>
body,td,th {font-family:"宋体"; font-size:12px;}
table{border-collapse:collapse;border:1px solid #CCC;background:#6089D4;}
table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }
table th{text-align:left; font-weight:bold;height:26px; line-height:25px; font-size:16px; border:3px solid #fff; color:#ffffff; padding:5px;}
table td{height:25px; font-size:12px; border:3px solid #fff; background-color:#f0f0f0; padding:5px;}
.c1{ width: 150px;}
.c2{ width: 130px;}
.c3{ width: 70px;}
.c4{ width: 80px;}
.c5{ width: 80px;}
.c6{ width: 300px;}
</style>
</head>
<body>';
        echo '<h1 style="text-align:center;">' . $title . '</h1>';
        echo $html;
        echo '</body></html>';

    }
}
