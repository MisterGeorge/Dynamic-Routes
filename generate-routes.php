<?php
/**
 * CLI element
 * buscar los controladores con la cadena @route::nombrederuta
 * @route::welcome/admin/
 * @route::welcome/private/
 * @route::welcome/private/
 */
define('BASEPATH', str_replace('\\', '/', 'system'));
define('APPPATH',  realpath('app') );


if( file_exists( APPPATH. '/config/routes-generator.php') )
{		
	$include_route = include_once APPPATH . '/config/routes-generator.php';
}
else
{	
	$include_route = [];
}
	
$directory_controller = APPPATH . '/controllers/';

$GenerateRoutes = function() use ( $directory_controller ) {
	
	$pattern_routes = '/@route::([a-zA-Z0-9\/\-\:\(\)\_]+)/';
	
	require_once BASEPATH.'/core/Controller.php';
	//require_once APPPATH.'/core/APP_Controller.php';
	
	$create_route = [];
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory_controller ));
	foreach ($files as $file)
	{
		$file_info = pathinfo( $file );

		if($file_info['extension']!='php')
		{
			continue;
		}

		require_once $file;		
				
		$controller_file = str_replace($directory_controller, "" ,$file_info['dirname'] .'/'.  $file_info['filename'] );
		
		$reflection_clas = new ReflectionClass( $file_info['filename'] );
		$docs_class      = $reflection_clas->getDocComment();
		$methods         = $reflection_clas->getMethods(ReflectionMethod::IS_PUBLIC);
		
		
		if(preg_match_all($pattern_routes, $docs_class , $matches_class) > 0 )
		{
			$route_controller = $matches_class[1][0].'/';
		}
		else
		{
			$route_controller = '';//strtolower( $file_info['filename'] );
		}

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

			if (preg_match_all( $pattern_routes , $docs_method, $matches_method ) > 0 ) 
			{		
				$matches_method_tmp = $matches_method[1];
				foreach ($matches_method_tmp as $key => $value) 
				{	
					if($value === '__avoid__' && $route_controller != '')
					{		
						$create_route[ substr($route_controller,0,-1) ] = $controller_file. '/' .$name_param;
					}
					else
					{
						$create_route[ $route_controller.$value ] = $controller_file. '/' .$name_param;
					}
				}
			}else
			{
				$create_route[ $route_controller.$route_action ] = $controller_file. '/' .$name_param;
			}	
				
		}
	}
	ksort($create_route);	
	return $create_route;
};


$add_routes_generator = $GenerateRoutes();
$actual_routes        = array_keys($include_route);
$print_new_routes     = [];
	
	
$content = "<?php \nreturn [\n";
foreach ($add_routes_generator as $route => $action ) {	
	
	if(!in_array( $route, $actual_routes ) )
	{	
		$print_new_routes[$route] = $action;
	}
	$content.="\t'".$route."' => '".$action."',\n";
}		
$content.="];";	
$file_path = APPPATH . '/config/routes-generator.php';


if( $fp = fopen( $file_path, 'w') )
{					
	fputs($fp, $content );
    fclose($fp);
    chmod($file_path,  0765);
}	
$c = new Colors();

if( count($print_new_routes) )
{				
	$c->getColoredString("\nSe crearon nuevas rutas\n\n","light_green");
	foreach ($print_new_routes as $key => $value) {	
		$c->getColoredString("Ruta: ".$key."\nClase: ".$value."\n","green");
		echo "\n";
	}	
}

	
class Colors {
	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		echo $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}