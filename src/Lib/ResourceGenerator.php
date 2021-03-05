<?php

namespace Wang\Pkg\Lib;

use Illuminate\Database\Eloquent\Model;
use Wang\Pkg\Lib\BatchAddModel;

class ResourceGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $formats = [
        'form_field' => "\$form->%s('%s', '%s')",
        'show_field' => "\$show->field('%s', '%s')",
        'grid_column' => "\$grid->column('%s', '%s')",
    ];

    /**
     * @var array
     */
    private $doctrineTypeMapping = [
        'string' => [
            'enum', 'geometry', 'geometrycollection', 'linestring',
            'polygon', 'multilinestring', 'multipoint', 'multipolygon',
            'point',
        ],
    ];

    /**
     * @var array
     */
    protected $fieldTypeMapping = [
        'ip' => 'ip',
        'email' => 'email|mail',
        'password' => 'password|pwd',
        'url' => 'url|link|src|href',
        'mobile' => 'mobile|phone',
        'color' => 'color|rgb',
        'image' => 'image|img|avatar|pic|picture|cover',
        'file' => 'file|attachment',
    ];

    /**
     * ResourceGenerator constructor.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $this->getModel($model);
    }

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    protected function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (!class_exists($model) || !is_string($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model [$model] !");
        }

        return new $model();
    }

    /**
     * @return string
     */
    public function generateForm()
    {
        $reservedColumns = $this->getReservedColumns();

        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $type = $column->getType()->getName();
            $default = $column->getDefault();
            $comment = $column->getComment();

            $defaultValue = '';

            /*            if($name=='user_level'){
                            echo $type;
                        }*/

            // set column fieldType and defaultValue
            switch ($type) {
                case 'boolean':
                case 'bool':
                    $fieldType = 'text';
                    break;
                case 'json':
                case 'array':
                case 'object':
                    $fieldType = 'text';
                    break;
                case 'string':
                    $fieldType = 'text';
                    foreach ($this->fieldTypeMapping as $type => $regex) {
                        if (preg_match("/^($regex)$/i", $name) !== 0) {
                            $fieldType = $type;
                            break;
                        }
                    }
                    $defaultValue = "'{$default}'";
                    break;
                case 'integer':
                case 'bigint':
                case 'smallint':
                case 'timestamp':
                    $fieldType = 'number';
                    break;
                case 'decimal':
                case 'float':
                case 'real':
                    $fieldType = 'decimal';
                    break;
                case 'datetime':
                    $fieldType = 'datetime';
                    $defaultValue = "date('Y-m-d H:i:s')";
                    break;
                case 'date':
                    $fieldType = 'date';
                    $defaultValue = "date('Y-m-d')";
                    break;
                case 'time':
                    $fieldType = 'time';
                    $defaultValue = "date('H:i:s')";
                    break;
                case 'text':
                case 'blob':
                    $fieldType = 'textarea';
                    break;
                default:
                    $fieldType = 'text';
                    $defaultValue = "'{$default}'";
            }

            $defaultValue = $defaultValue ?: $default;

            if ($comment == "") {
                //$label = $this->formatLabel($name);
                $label = $name;
            } else {
                if (strpos($comment, '---') !== false) {
                    $tempArr = explode($comment, '---');
                    $label = trim($tempArr[0]);

                    //得到model和静态属性值
                    //$fieldType =
                } else {
                    $label = $comment;
                }
            }

            if ($label == 'created_at') {
                $label = '创建时间';
            }

            if ($label == 'updated_at') {
                $label = '更新时间';
            }

            if (strpos($label, '?') !== false && strpos($label, '=') !== false) {
                $labelArr = explode('?', $label);
                $label = $labelArr[0];
            }


            $output .= sprintf($this->formats['form_field'], $fieldType, $name, $label);

            if (trim($defaultValue, "'\"")) {
                $output .= "->default({$defaultValue})";
            }

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateShow()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();

            // set column label
            $label = $this->formatLabel($name);
            $comment = $column->getComment();


            if ($comment == "") {
                $label = $name;
            } else {
                if (strpos($comment, '---') !== false) {
                    $tempArr = explode($comment, '---');
                    $label = trim($tempArr[0]);
                } else {
                    $label = $comment;
                }
            }

            if ($label == 'created_at') {
                $label = '创建时间';
            }

            if ($label == 'updated_at') {
                $label = '更新时间';
            }

            if (strpos($label, '?') !== false && strpos($label, '=') !== false) {
                $labelArr = explode('?', $label);
                $label = $labelArr[0];
            }

            $output .= sprintf($this->formats['show_field'], $name, $label);

            //处理state
            $output .= ";\r\n";

        }

        return $output;
    }

    public function generateGrid()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            $label = $this->formatLabel($name);
            $comment = $column->getComment();


            if ($comment == "") {
                $label = $name;
            } else {
                if (strpos($comment, '---') !== false) {
                    $tempArr = explode($comment, '---');
                    $label = trim($tempArr[0]);
                } else {
                    $label = $comment;
                }
            }

            if ($label == 'created_at') {
                $label = '创建时间';
            }

            if ($label == 'updated_at') {
                $label = '更新时间';
            }

            if (strpos($label, '?') !== false && strpos($label, '=') !== false) {
                $labelArr = explode('?', $label);
                $label = $labelArr[0];
            }

            $output .= sprintf($this->formats['grid_column'], $name, $label);

            //处理state
            if (strpos($comment, '?') !== false && strpos($comment, '=') !== false) {
                $tabCamelize = ucfirst(BatchAddModel::camelize($this->model->getTable()));
                $fieldCamelize = BatchAddModel::camelize($name);
                $output .= "->using({$tabCamelize}::\${$fieldCamelize}Map);\r\n";
            } else {
                $output .= ";\r\n";
            }
        }

        return $output;
    }

    protected function getReservedColumns()
    {
        return [
            $this->model->getKeyName(),
            $this->model->getCreatedAtColumn(),
            $this->model->getUpdatedAtColumn(),
            'deleted_at',
        ];
    }

    /**
     * Get columns of a giving model.
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     * @throws \Exception
     *
     */
    protected function getTableColumns()
    {
        if (!$this->model->getConnection()->isDoctrineAvailable()) {
            throw new \Exception(
                'You need to require doctrine/dbal: ~2.3 in your own composer.json to get database columns. '
            );
        }

        $table = $this->model->getConnection()->getTablePrefix() . $this->model->getTable();
        /** @var \Doctrine\DBAL\Schema\MySqlSchemaManager $schema */
        $schema = $this->model->getConnection()->getDoctrineSchemaManager($table);

        // custom mapping the types that doctrine/dbal does not support
        $databasePlatform = $schema->getDatabasePlatform();

        foreach ($this->doctrineTypeMapping as $doctrineType => $dbTypes) {
            foreach ($dbTypes as $dbType) {
                $databasePlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }

        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }

        return $schema->listTableColumns($table, $database);
    }

    /**
     * Format label.
     *
     * @param string $value
     *
     * @return string
     */
    protected function formatLabel($value)
    {
        return ucfirst(str_replace(['-', '_'], ' ', $value));
    }
}
