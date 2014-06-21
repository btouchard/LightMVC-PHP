LightMVC
========

LightMVC is a simple PHP MVC Framework
* No routes (auto load controller from uri).
* Automaticly load models relation from reading database cardinality.
* Html or service mode renderer.
* Multi Layout implementation.
* Helper for simplify HTML link, js, images, css, ...

Requirements
------------

* PHP5
* MySQL5

Installation
------------

* Clone or download and extract the archive to your working directory.
* Implement first Model, View and Controler in App directory.

Sample
------
* HelloWorld view (App/Views/Hello/index.php)
```html
<h1>$text</h1>
```
* HelloWorld Controller (App/Controller/HelloController.class.php)
```php
<?php
namespace App\Controller;
use Library\Controller;
class HomeController extends Controller {
    protected function index() {
        $this->set('text', 'HelloWorld !');
    }
}
```
