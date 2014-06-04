<?php 
class ServiceProvider
{
	final public static function autoload($class)
	{
		$count = substr_count($class, '\\');
		
		if ($count < 2) {
			return true;
		}
		
		$class = explode('\\', $class);
		
		if ($class[0] == 'HMinng' && $class[1] == 'DBLibrary') {
			unset($class[0], $class[1]);
			
			$classPath = dirname(__FILE__);
			
			foreach ($class as $value) {
				$classPath  .= DIRECTORY_SEPARATOR . $value; 
			}
			
			$classPath .= '.php';
			
			require_once $classPath;
		}
		
		return true;
	}
}