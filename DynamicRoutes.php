<?php
/**
* 
*/
class DynamicRoutes
{
	
	private $pattern_routes_class  = '/@route:([a-zA-Z0-9\/\-\:\(\)\_\|]+)/';

	private $pattern_routes_method = '/@route:({get}|{post}|{put}|{delete}|)([a-zA-Z0-9\/\-\:\(\)\_\|]+)/';

	private $routes_path;
	
	private $controller_path;

	private $type_file;

	public $current_routes;

	public $create_routes = [];

	/**
	 * @param $config
	 *		#BASEPATH 		: base path system
	 *		#APPPATH 		: application path
	 *		#routes_path 		: file routes output
	 * 		#controller_path 	: path controllers
	 * 		#type_file 		: json|php
	 */
	public function __construct(  $config = null )
	{
		$base_path       = isset($config['BASEPATH']) ? $config['BASEPATH'] : '';
		$app_path        = isset($config['APPPATH']) ? $config['APPPATH'] : ''; 
		$route_path      = isset($config['routes_path']) ? $config['routes_path'] : ''; 
		$controller_path = isset($config['controller_path']) ? $config['controller_path'] : ''; 
		$this->type_file = isset($config['type_file']) ? $config['type_file'] : 'json'; 

		if(!defined("BASEPATH"))
		{
			define("BASEPATH", $base_path);
		}
		if(!defined("APPPATH"))
		{
			define("APPPATH", $app_path );
		}
		
		$this->routes_path     = ($route_path) ? $route_path : APPPATH . "/config/routes-generator";
		$this->controller_path = ($controller_path) ? $controller_path : APPPATH . "/controllers/";
		
		$this->current_routes = $this->get_current_routes();
	}

	public function get_current_routes()
	{
		if( file_exists( $this->routes_path ) )
		{	
			if($this->type_file==='json')
			{
				$currentRoutes =  @json_decode( file_get_contents($this->routes_path),true);
			}
			else if($this->type_file==='php')
			{
				$currentRoutes = include $this->routes_path;
			}
			else
			{
				$this->_error("Type not found [".$this->type_file."] use: [php or json]");
			}

			if(  !is_array($currentRoutes) )
			{
				//$this->_error("file is worng, maybe you change php to json or inverse (Compile again)");
				return [];
			}
		}
		else
		{	
			return [];
		}

		return $currentRoutes;
	}
	
	/**
	 * 
	 */
	public function help()
	{
		echo "\nUse comments in a controller:\n\n";
		echo "\t\tDefault route \t\t\t\t @route:NAMEROUTE\n";
		echo "\t\tParam num \t\t\t\t @route:NAMEROUTE/(:num)\n";
		echo "\t\tParam any \t\t\t\t @route:NAMEROUTE/(:any)\n";
		echo "\t\tNo route (Class required route) \t @route:__avoid__\n\n";
	}

	public function extendsFile( $fileName = "")
	{
		$fullname 	 = realpath($fileName);
		
		if(file_exists($fileName))
		{	
			require_once $fileName;
		}
		else
		{
			$this->_error("File not found [".$fileName."]");
		}

		return $this;
	}

	public function routes_not_found($new_routes = null, $current_routes = null)
	{
		$routes_not_found = [];
		$new_routes       = is_array($new_routes) ? $new_routes : [];
		$current_routes   = is_array($current_routes) ? $current_routes : [];
		$keys_tmp         = array_keys( $current_routes );

		foreach ($new_routes as $firstKey => $firstValue) {
			
			if(is_array($firstValue) )
			{	
				foreach ($firstValue as $secondKey => $secondValue) {
					$compare = isset($current_routes[$firstKey][$secondKey]) ? $current_routes[$firstKey][$secondKey] : '';
					if($compare !=  $new_routes[$firstKey][$secondKey])
					{	
						$routes_not_found[] = [
							'url' => "{".$secondKey."}".$firstKey,
							'path' => $new_routes[$firstKey][$secondKey]
						];
					}
				}
			}
			else if(!in_array($firstKey,  $keys_tmp) )
			{	
				$routes_not_found[] = [
					'url' => $firstKey,
					'path' => $new_routes[$firstKey]
				];
			}
		}

		return $routes_not_found;
	}

	private function print_new_routes( $routes_news )
	{
		if(count($routes_news) > 0)
		{	
			$str_ouput = "\033[1;32m";
			$str_ouput.= "Routes news (".count($routes_news).") \n";
			$str_ouput.= "\033[0m";
			foreach ($routes_news as $value) {
				$str_ouput.= "\033[0;32m";
				$str_ouput.= "\t".str_pad($value['url'].' ', 50,' ')." ";
				$str_ouput.= "\033[0m";
				$str_ouput.= "\033[0;36m";
				$str_ouput.= $value['path']."\n";
				$str_ouput.= "\033[0m";
			}
			
			echo $str_ouput;
		}
		else
		{	
			echo " The routes are updated \n\n";
		}
	}

	public function compile( $print_new_routes = true )
	{
		$files = new RecursiveIteratorIterator( 
			new RecursiveDirectoryIterator( $this->controller_path )
		);

		$this->_retriveClasses( $files );
		
		ksort( $this->create_routes );
		
		$this->_saveRoutes( $this->create_routes );

		if($print_new_routes)
		{
			$this->print_new_routes( $this->routes_not_found( $this->create_routes ,  $this->current_routes));
		}
	}

	private function _saveRoutes( $routes )
	{
		if($this->type_file === 'json')
		{
			$routesStr = json_encode($routes);
		}
		else if($this->type_file === 'php' )
		{
			$replace_content = print_r($routes, 1 ).";";
			$replace_content = str_replace("[",'"',$replace_content);
			$replace_content = str_replace("]",'"',$replace_content);
			$replace_content = str_replace("=> Array",'=>Array',$replace_content);
			$replace_content = str_replace("=> ",'=> "',$replace_content);
			$replace_content = str_replace("/\n","/\",\n",$replace_content);
			$replace_content = str_replace(")\n\n","),\n",$replace_content);

			$routesStr = "<?php \nreturn ";
			$routesStr.= $replace_content;
		}

		if( $fp = fopen( $this->routes_path, 'w') )
		{
			fputs($fp, $routesStr );
		    fclose($fp);
		    @chmod($this->routes_path,  0775);
		}
	}

	private function _retriveClasses( $files )
	{
		foreach ($files as $file) {
			$file_info = pathinfo( $file );
		
			if(!isset($file_info['extension']) || $file_info['extension']!='php')
			{
				continue;
			}

			require_once $file;		
					
			$controller_file = str_replace( $this->controller_path, "" ,$file_info['dirname'] .'/'.  $file_info['filename'] );
			
			$reflection_clas = new ReflectionClass( $file_info['filename'] );
			$docs_class      = $reflection_clas->getDocComment();
			$methods         = $reflection_clas->getMethods(ReflectionMethod::IS_PUBLIC);
			
			
			if(preg_match_all($this->pattern_routes_class, $docs_class , $matches_class) > 0 )
			{
				$route_controller = $matches_class[1][0].'/';
			}
			else
			{
				$route_controller = '';
			}

			$this->_retriveMethods( $methods , $route_controller, $file_info, $controller_file );
		}
	}

	private function _retriveMethods( $methods , $route_controller, $file_info, $controller_file)
	{
		foreach ($methods as $reflection) 
		{
			$route_action = strtolower( $reflection->name );
			$name_param   = $reflection->name.'/';

			if ( substr( $reflection->name, 0, 1 ) === '_' ||  $reflection->name === 'get_instance')
			{
				continue;		
			}
			
			$reflection_method = new ReflectionMethod( $file_info['filename'] ,  $reflection->name );
			$docs_method       = $reflection_method->getDocComment();
			
			$aux_param = array();
			foreach ($reflection_method->getParameters() as $num => $value) 
			{
				$name_param.= '$'. ($num + 1).'/';
			}
			
			$controller_action_path =  $controller_file. '/' .$name_param;

			if (preg_match_all( $this->pattern_routes_method , $docs_method, $matches ) > 0 ) 
			{		
				$matches_method_tmp = $matches[1];
				$matches_url_tmp 	= $matches[2];
				foreach ($matches_url_tmp as $key => $value) 
				{
					$type_method = isset($matches_method_tmp[$key]) ? str_replace(['{','}'], '', $matches_method_tmp[$key]) : '';
					
					if($value === '__avoid__' && $route_controller != '')
					{	
						if($type_method==='')
						{
							$this->create_routes[ substr($route_controller,0,-1) ] = $controller_action_path;
						}
						else 
						{
							$this->create_routes[ substr($route_controller,0,-1) ][$type_method] = $controller_action_path;
						}
					}
					else
					{	
						if($type_method==='')
						{
							$this->create_routes[ $route_controller.$value ] = $controller_action_path;
						}
						else
						{
							$this->create_routes[ $route_controller.$value ][$type_method] = $controller_action_path;
						}
					}

				}
			}else
			{
				$this->create_routes[ $route_controller.$route_action ] = $controller_action_path;
			}	
				
		}
	}

	private function _error( $str )
	{
		die(" Error: <<<{$str}>>>\n\n");
	}
}
