# dynamic-routes-ci
Generate Dynamic Routes to Codeigniter framework

#Install
+ Download DynamicRoutes.php into libraries/
+ Download dynamicRoutes (sh) into path framework
+ Edit file config/routes.php

```php
include_once APPPATH .'/libraries/DynamicRoutes.php';
$DynamicRoutes = new DynamicRoutes([
	'type_file' => 'json'
]);
$route = array_merge( $route, $DynamicRoutes->current_routes );
```

+ Config dynamicRoutes

#Examples
##Remember! that you change the comment after a route only run the following in console
```sh
  php dynamicRoutes
```

### route controller + function
```php
/**
 * @route:example-route
 */
class Example1 extends CI_Controller
{
  /**
 * @route:product/(:num)
 * @route:product
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
 * @route:hello-world
 */
  function hello_world(  ){
    //url: /hello-world
  }
  
 /**
 * @route:hi-moon
 */
  function hello_moon(  ){
    //url: /hi-moon
  }
}
```
### arguments num and any
```php
/**
 * @route:arguments
 */
class Example3 extends CI_Controller
{
  /**
 * @route:numeric-values/(:num)/(:num)
 */
  function numeric( $a , $b ){
    //url: /arguments/numeric-values/$1/$2
  }
  
 /**
 * @route:any-values/(:any)
 */
  function any( $a ){
    //url: /arguments/any-values/$1
  }
  
  /**
 * @route:several/(:num)/(:num)/(:any)/(:any)
 */
  function several( $a, $b , $c, $d  ){
    //url: /arguments/several/$1/$2/$3/$4
  }
}
```
### Include method get, post, put, delete
```php
/**
 * @route:example4
 */
class Example4 extends CI_Controller
{
  /**
  * @route:{post}data_post
  */
  function data_post(){
    //url: example4/data_post/  method = post
  }
  
 /**
  * @route:{get}my_data_get
  */
  function my_data_get(){
    //url: /example4/my_data_get/ method = get
  }
}
```
