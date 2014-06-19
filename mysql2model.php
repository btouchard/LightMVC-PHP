<?php
header('Content-type: text/plain');
setlocale(LC_ALL, 'fr_FR.UTF8');
require_once 'datas.nfo.php';
require_once 'Library/bootstrap.php';

use \Library\Utils\DB;
use \Library\Utils\Debug;
use \Library\Utils\StringUtils;

define('DATABASE', 'mydb');

$models = array();

$tables = DB::query('SELECT `TABLE_NAME` AS `name` FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_SCHEMA`=\'' . DATABASE . '\' AND `AUTO_INCREMENT` IS NOT NULL'); // AND `TABLE_NAME`=\'users\'
while ($table = $tables->fetch(PDO::FETCH_ASSOC)) {
    $model = new \Model;
    $model->name = ucwords($table['name']);
    $model->table = $table['name'];
    $model->fields = array();
    $model->joins = array();
    // Champs
    $fields = DB::query('SELECT `COLUMN_NAME` AS `name`, `DATA_TYPE` AS `type`, `COLUMN_KEY` AS `key` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`=\'' . DATABASE . '\' AND `TABLE_NAME`=\'' . $model->table . '\'');
    while ($field = $fields->fetch(PDO::FETCH_ASSOC)) {
        array_push($model->fields, array('name' => $field['name'], 'type' => $field['type'], 'key' => $field['key']));
    }
    $models[] = $model;
}

for ($i=0; $i<count($models); $i++) {
    $model = $models[$i];
    $joins = array();
    $keys = DB::query('SELECT i.CONSTRAINT_NAME, i.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
                       FROM `INFORMATION_SCHEMA`.`TABLE_CONSTRAINTS` AS i
                       LEFT JOIN `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` k ON i.`CONSTRAINT_NAME` = k.`CONSTRAINT_NAME`
                       WHERE i.`CONSTRAINT_TYPE` = \'FOREIGN KEY\' AND i.`TABLE_SCHEMA` = \'' . DATABASE . '\'
                       AND k.`REFERENCED_TABLE_NAME` = \'' . $model->table . '\'
                       ORDER BY i.CONSTRAINT_NAME');
    while ($key = $keys->fetch(PDO::FETCH_ASSOC)) {
        if (StringUtils::startWith($model->table, $key['constraint_name'])) {
            $join = array();
            $keys2 = DB::query('SELECT i.CONSTRAINT_NAME, i.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
                                FROM `INFORMATION_SCHEMA`.`TABLE_CONSTRAINTS` AS i
                                LEFT JOIN `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` k ON i.`CONSTRAINT_NAME` = k.`CONSTRAINT_NAME`
                                WHERE i.`CONSTRAINT_TYPE` = \'FOREIGN KEY\' AND i.`TABLE_SCHEMA` = \'' . DATABASE . '\'
                                AND k.`TABLE_NAME` = \'' . $key['table_name'] . '\' AND i.CONSTRAINT_NAME != \'' . $key['constraint_name'] . '\'');
            if ($keys2->rowCount() > 0) {
                $key2 = $keys2->fetch(PDO::FETCH_ASSOC);
                // hasAndBelongsToMany (N->N)
                $join['type'] = 'hasAndBelongsToMany';
                $join['class'] = ucwords($key2['referenced_table_name']);
                $join['table'] = $key2['referenced_table_name'];
                $join['joinTable'] = $key['table_name'];
                $join['foreignKey'] = $key['column_name'];
                $join['associationForeignKey'] = $key2['column_name'];
                // invert key -> hasAndBelongsToMany (N->N)
                // TODO: Dangereux pour la récurtion lors de l'utilisation !!
                /*$inverse['type'] = 'hasAndBelongsToMany';
                $inverse['class'] = ucwords($key['referenced_table_name']);
                $inverse['table'] = $key['referenced_table_name'];
                $inverse['joinTable'] = $key['table_name'];
                $inverse['foreignKey'] = $key2['column_name'];
                $inverse['associationForeignKey'] = $key['column_name'];
                // setInverse($join['class'], $inverse);*/
            } else {
                // hasMany (1->N)
                $join['type'] = 'hasMany';
                $join['class'] = ucwords($key['table_name']);
                $join['table'] = $key['table_name'];
                $join['foreignKey'] = $key['column_name'];
                // invert key -> belongsTo (N->1)
                $inverse['type'] = 'belongsTo';
                $inverse['class'] = ucwords($key['referenced_table_name']);
                $inverse['table'] = $key['referenced_table_name'];
                $inverse['foreignKey'] = $key['column_name'];

                $mirror = getModel($join['class']);
                $field = getField($mirror, $key['column_name']);
                if ($field['key'] == 'UNI') $join['type'] = 'hasOne';

                setInverse($join['class'], $inverse);
            }
            if (!empty($join)) $joins[] = $join;
        }
    }
    $model->joins = $joins;
    $models[$i] = $model;
}

foreach ($models as $model) {
    echo $model . "\n\n";
}

function setInverse($class, $join) {
    global $models;
    for ($i=0; $i<count($models); $i++) {
        if ($models[$i]->name == $class) {
            array_push($models[$i]->joins, $join);
        }
    }
}
function getModel($name) {
    global $models;
    for ($i=0; $i<count($models); $i++) {
        if ($models[$i]->name == $name) return $models[$i];
    }
}
function getField($model, $name) {
    foreach ($model->fields as $field) {
        if ($field['name'] == $name) return $field;
    }
    return null;
}


/**
 * @property mixed name
 * @property mixed table
 * @property mixed fields
 * @property mixed joins
 */
class Model {

    public function __toString() {
        $str[] = $this->name;
        $vls = array();
        foreach ($this->fields as $fd) $vls[] = $fd['name'];
        if (count($vls) > 0) $str[] = '{' . implode(', ', $vls) . '}';
        if (!empty($this->joins))
            foreach ($this->joins as $jn) {
                $v = "\n\t" . $jn['type'] . ': ' . $jn['class'];
                if ($jn['type'] == 'hasAndBelongsToMany') $v .= ' {joinTable: '.$jn['joinTable'] . ', foreignKey: '.$jn['foreignKey'] . ', associationForeignKey: '.$jn['associationForeignKey'] . '}';
                else $v .= ' {table: '.$jn['table'] . ', foreignKey: ' . $jn['foreignKey'] . '}';
                $str[] = $v;
            }
        return implode(' ', $str);
    }

}