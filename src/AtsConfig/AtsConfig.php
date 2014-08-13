<?php
namespace HMinng\DBLibrary\AtsConfig;

use Symfony\Component\Yaml\Yaml;

class AtsConfig
{
	public static function configures()
	{
	    $configures = Yaml::parse(APPLICATION_PATH . '/../application/library/config/Databases.yml');
	    
	    return $configures['databases'];
	}
}