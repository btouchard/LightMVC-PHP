<?php
namespace Library\Managers;

use Library\Application;
use Library\Component;
use Library\Utils\StringUtils;

class Html extends Component {

    public function __call($name, $args) {
        $text = null;
        $attr = array();
        if (count($args) > 0) {
            $value = array_shift($args);
            if (is_array($value)) $attr = $value;
            else $text = (string) $value;
        }
        $tag = '<' . $name;
        if ($name == 'input' && !isset($attr['type'])) $attr['type'] = 'text';
        if (count($attr) > 0) foreach ($attr as $key=>$val) $tag .= ' ' . $key . '="' . $val . '"';
        $tag .= '>';
        if ($text != null) $tag .= $text . '</' . $name . '>';
        return $tag . "\n";
    }

    public function link($module=NULL, $params=NULL) {
        if ($module != NULL) {
            if ($module != '/') $url[] = StringUtils::toUrl($module);
            if (!empty($params)) {
                $ext = '.html';
                if (is_array($params)) {
                    foreach ($params as $value) $url[] = StringUtils::toUrl($value);
                } else $url[] = StringUtils::toUrl($params);
            } else $ext = '';
            return BASE_URL . implode(SEPARATOR, $url) . $ext;
        }
        return '';
    }

    public function script($src) {
        if (strpos($src, '//') === false) $src = BASE_URL . 'js/' . $src;
        return '<script src="' . $src . '"></script>' . "\n";
    }

    public function css($src, $rel = null) {
        if (strpos($src, '//') === false) $src = BASE_URL . 'css/' . $src;
        if ($rel === 'import') return '@import url(' . $src . ');';
        else if (!$rel) $rel = 'stylesheet';
        return '<link rel="' . $rel . '" href="' . $src . '">' . "\n";
    }
}