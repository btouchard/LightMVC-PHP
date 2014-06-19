<?php
echo "ok";
exit();

$table = 'commands';
$qry = array(
    'className' => 'Toto',
    'SELECT' => $table . '.*',
    'FROM' => $table,
    'INNER JOIN' => array(
        'users',
        'ON' => array(
            $table . '.id_user' => 'users.id',
            $table . '.id_toto' => 'toto.tata'
        )
    ),
    'WHERE' => array(
        $table . '.id' => 1
    ),
    'ORDER BY' => '`created` DESC',
    'LIMIT' => '0, 2'
);

//\Library\Utils\Debug::log($qry);
echo parseQuery($qry);

function parseQuery($qry, $block = null) {
    $mysql = array('SELECT', 'FROM', 'INNER JOIN', 'ON', 'WHERE', 'ORDER BY', 'LIMIT');
    if ($block == 'WHERE' || $block == 'ON') {
        \Library\Utils\Debug::log($block);
        \Library\Utils\Debug::log($qry);
        if (key($qry) == 'AND' || key($qry) == 'OR') {
            $glue = key($qry);
            $qry = $qry[key($qry)];
        }/* else return key($qry) . ' = ' . $qry[key($qry)];*/
        if (!isset($glue) || is_null($glue)) $glue = 'AND';
        foreach ($qry as $key => $value) {
            $values[] = $key . ' = ' . $value;
        }
        return implode(' ' . $glue . ' ', $values);
    }
    $query = '';
    foreach ($qry as $part => $value) {
        if (!in_array($part, $mysql)) continue;
        if (is_numeric($part)) {
            if (is_array($value)) $query .= parseQuery($value);
            else $query .= $value;
        } else if (is_array($value)) $query .= $part . ' ' . parseQuery($value, $part);
        else $query .= $part . ' ' . $value;
        $query .= ' ';
    }
    $query = preg_replace('#([a-z_]+)\.([a-z_]+)#', '`$1`.`$2`', $query);
    $query = preg_replace('#([a-z_]+)\.(\*)#', '`$1`.$2', $query);
    return trim($query);
}