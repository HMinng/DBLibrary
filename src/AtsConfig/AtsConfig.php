<?php
namespace HMinng\DBLibrary\AtsConfig;

use Symfony\Component\Yaml\Yaml;

class AtsConfig
{
    private static $file = '/Users/HMinng/HMinng/workspaces/Video/application/library/config/AtsConfig.yml';
    
	public static function configures()
	{
	    $configures = Yaml::parse(self::$file);
	    
	    return $configures['databases'];
	}
}