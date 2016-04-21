# routes-ci
Generate routes to Codeigniter framework

#Install
+ create file config/routes-generator.php
+ add next code on file config/routes.php

```php
$file_path = APPPATH . 'config/routes-generator.php';

if(file_exists($file_path)){
	
	$include_route = include $file_path;
	if (isset($include_route) && is_array($include_route))
	{	
		$route = array_merge( $route, $include_route);
	}
}
```

+ Add the file on FCPATH (home path codeigniter) /  **generate-routes.php
+ Define var on **generate-routes.php (not necesary)
```php
define('BASEPATH', str_replace('\\', '/', 'system'));
define('APPPATH',  realpath('app') );
```
#Examples
##Remember! that you change the comment after a route only run the following in console
```sh
  php generate-routes.php
```

### route controller + function
```php
/**
 * @route::example-route
 */
class Example1 extends CI_Controller
{
  /**
 * @route::product/(:num)
 * @route::product
 */
  function product( $parm ){
    //url1: /example-route/product/1
    //url2: /example-route/product
  }
}
```

### route on function
```php
class Example2 extends CI_Controller
{
  /**
 * @route::hello-world
 */
  function hello_world(  ){
    //url: /hello-world
  }
  
 /**
 * @route::hi-moon
 */
  function hello_moon(  ){
    //url: /hi-moon
  }
}
```
### arguments num and any
```php
/**
 * @route::arguments
 */
class Example3 extends CI_Controller
{
  /**
 * @route::numeric-values/(:num)/(:num)
 */
  function numeric( $a , $b ){
    //url: /arguments/numeric-values/$1/$2
  }
  
 /**
 * @route::any-values/(:any)
 */
  function any( $a ){
    //url: /arguments/any-values/$1
  }
  
  /**
 * @route::several/(:num)/(:num)/(:any)/(:any)
 */
  function several( $a, $b , $c, $d  ){
    //url: /arguments/several/$1/$2/$3/$4
  }
}
```
