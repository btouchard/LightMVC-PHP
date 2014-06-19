<?php
namespace Library;

use Library\Utils\ArrayUtils;
use Library\Utils\DB;
use Library\Utils\Debug;
use Library\Utils\ISerializable;
use Library\Utils\StringUtils;
use PDO;

class Model extends Component implements ISerializable {

    private $class;
    protected $table = null;
    public $id = 0;

    public function __construct(Application $app, array $data = null) {
        parent::__construct($app);
        $this->class = get_class($this);
        if ($this->table === null) $this->table = $this->getTable();
        if ($data != null) $this->populate($data);
        if ($this->id > 0) $this->checkConstraints();
    }

    public function table(){
        return $this->table;
    }

    private function getTable() {
        $tmp = explode('\\', $this->class);
        $name = array_pop($tmp);
        return strtolower($name);
    }

    private function populate(array $data) {
        foreach ($data as $key => $value) {
            //if (!property_exists($this, $key)) continue;
            if (is_numeric($value)) $this->$key = (int) $value;
            else $this->$key = $value;
        }
    }

    private function checkConstraints() {
        $constraints = array('hasOne', 'hasMany', 'belongsTo', 'hasAndBelongsToMany');
        foreach ($constraints as $constraint) {
            if (property_exists($this->class, $constraint)) $this->prepareConstraint($constraint, $this->$constraint);
        }
    }
    private function prepareConstraint($type, $constraint) {
        if (is_array($constraint) && is_int(key($constraint))) $constraint = array('className' => $constraint[0]);
        else if (!is_array($constraint)) $constraint = array('className' => $constraint);
        //Debug::log($constraint['className'] . ' in ' . StringUtils::getClassName($this->getCallingClass()) . ' => ' . StringUtils::startWith($constraint['className'], StringUtils::getClassName($this->getCallingClass())));
        if (StringUtils::startWith($constraint['className'], StringUtils::getClassName($this->getCaller()['class']))) return;
        $this->setupConstraint($type, $constraint);
        if (!is_null($constraint)) $this->loadConstraint($constraint);
    }
    private function setupConstraint($type, &$constraint) {
        $class = '\\App\\Model\\' . $constraint['className'];
        if (!exist($class)) return $constraint = null;
        $constraint['class'] = $class;
        $instance = new $class($this->app);
        switch ($type) {
            case 'hasOne':
                $constraint['WHERE'] = array($instance->table() . '.id' => $this->{'id_' . rtrim($instance->table(), 's')});
                break;
            case 'hasMany':
                $constraint['WHERE'] = array($instance->table() . '.id_' . rtrim($this->table, 's') => $this->id);
                break;
            case 'belongsTo':
                $constraint['INNER JOIN'] = array(
                    '`' . $this->table . '`',
                    'ON' => array( $instance->table() . '.id' => $this->table . '.id_' . rtrim($instance->table(), 's') )
                );
                $constraint['WHERE'] = array($this->table . '.id_' . rtrim($instance->table(), 's') => $this->id);
                break;
            case 'hasAndBelongsToMany':
                $table = rtrim($this->table, 's') .  '_' . $instance->table();
                $constraint['SELECT'] = array($instance->table() . '.*', $table . '.*');
                $constraint['INNER JOIN'] = array(
                    '`' . $table . '`',
                    'ON' => array(
                        $instance->table() . '.id' => $table . '.id_' . rtrim($instance->table(), 's'),
                        $table . '.id_' . rtrim($this->table(), 's') => $this->id
                    )
                );
                break;
        }
    }

    private function loadConstraint(&$constraint) {
        $instance = new $constraint['class']($this->app);
        $result = $instance->find($constraint);
        if (is_null($result)) return;
        $name = $constraint['className'];
        if (is_array($result) && !StringUtils::endWith('s', $name)) $name .= 's';
        $this->$name = $result;
    }

    private function parseQuery($qry, $block = null) {
        $mysqlWords = array('SELECT', 'FROM', 'INNER JOIN', 'ON', 'WHERE', 'ORDER BY', 'LIMIT');
        if ($block == 'SELECT') {
            for ($i=0; $i<count($qry); $i++) $qry[$i] = '`' . $this->table . '`.`' . $qry[$i] . '`';
            return implode(', ', $qry);
        } else if ($block == 'WHERE' || $block == 'ON') {
            if (is_int(key($qry))) $glue = 'AND';
            else if (key($qry) == 'AND' || key($qry) == 'OR') {
                $glue = key($qry);
                $qry = $qry[key($qry)];
            }
            if (!isset($glue) || is_null($glue)) $glue = 'AND';
            $values = array();
            foreach ($qry as $key => $value) $values[] = '`' . $this->table . '`.`' . $key . '` = ' . DB::quote($value);
            return '(' . implode(' ' . $glue . ' ', $values) . ')';
        }
        $query = '';
        foreach ($qry as $part => $value) {
            if (!in_array($part, $mysqlWords)) continue;
            if (is_numeric($part)) {
                if (is_array($value)) $query .= $this->parseQuery($value);
                else $query .= $value;
            } else if (is_array($value)) $query .= $part . ' ' . $this->parseQuery($value, $part);
            else $query .= $part . ' ' . $value;
            $query .= ' ';
        }
        //$query = preg_replace('#\'?([a-z_]+)\.([a-z_]+)\'?#', '`$1`.`$2`', $query);
        //$query = preg_replace('#([a-z_]+)\.(\*)#', '`$1`.$2', $query);
        return trim($query);
    }

    private function isSingleResultQuery($qry) {
        if (isset($qry['WHERE']) && ArrayUtils::keyExists($this->table . '.id', $qry['WHERE'])) return true;
        if (isset($qry['LIMIT'])) return 1 == (int) preg_replace('#\d, ?(\d)#', '$1', $qry['LIMIT']);
        return false;
    }

    public function find($params='all') {
        $result = array();
        $qry = array();
        if (!empty($params['SELECT'])) $qry['SELECT'] = $params['SELECT'];
        else $qry['SELECT'] = '`' . $this->table . '`.*';
        if (!empty($params['FROM'])) $qry['FROM'] = $params['FROM'];
        else $qry['FROM'] = '`' . $this->table . '`';
        if (is_numeric($params)) $qry['WHERE'] = array('id' => $params);
        if (is_array($params)) $qry = array_merge($qry, $params);
        else if ($params == 'all' || $params == 'first' || $params == 'last') {
            $direction = $params == 'last' ? 'DESC' : 'ASC';
            if (property_exists($this, 'created')) $qry['ORDER BY'] = '`' . $this->table . '`.`created ' . $direction;
            else $qry['ORDER BY'] = '`' . $this->table . '`.`id ' . $direction;
            if ($params == 'first' || $params == 'last') $qry['LIMIT'] = '0, 1';
        }
        $query = $this->parseQuery($qry);
        //Debug::log($query);
        $rs = DB::query($query);
        if ($rs->rowCount() == 1) {
            $result = new $this->class($this->app, $rs->fetch(PDO::FETCH_ASSOC));
            if (!is_array($result) && !$this->isSingleResultQuery($qry)) $result = array($result);
        } else if ($rs->rowCount() > 1) {
            while ($rw = $rs->fetch(PDO::FETCH_ASSOC)) $result[] = new $this->class($this->app, $rw);
        }
        return $result;
    }

    public function save() {
        $news = $_GET['id'] > 0 ? $this->find($_GET['id']) : new $this->class($this->app);
        $qry  = $news->id > 0 ? 'UPDATE ' : 'INSERT INTO ';
        $qry .= $this->table . ' SET ';
        $values = array();
        foreach ($_POST as $key => $value) {
            if (isset($news->$key) && $news->$key != $value)
                $values[] = '`' . $key . '` = \'' . addslashes($value) . '\'';
        }
        if (count($values) > 0) {
            $qry .= implode(', ', $values);
            if ($news->id > 0) $qry .= ' WHERE `id` = ' . $news->id;
            $success = DB::exec($qry);
            if ($success) {
                if ($news->id > 0) $news->id = DB::lastInsertId();
                $news = $this->find($news->id);
            } else {
                $news->id = 0;
            }
        } else {
            $news->id = 0;
        }
        return $news;
    }

    public function delete() {
        $news = $this->find($_GET['id']);
        if ($news->id == 0) return false;
        $qry = 'DELETE FROM ' . $this->table . ' WHERE `id` = ' . $news->id;
        $success = DB::exec($qry);
        return $success ? true : false;
    }

    public function asSerializable() {
        $fields = $this->getFields();
        $reflexion = array();
        foreach ($fields as $field) $reflexion[$field] = $this->$field;
        return $reflexion;
    }
}