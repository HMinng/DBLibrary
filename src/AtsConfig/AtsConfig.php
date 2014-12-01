<?php
namespace HMinng\DBLibrary\AtsConfig;

use Symfony\Component\Yaml\Yaml;

class AtsConfig
{
	public static function configures()
	{
	    $configures = Yaml::parse(file_get_contents(APPLICATION_PATH . '/../conf/custom/Databases.yml'));

        $env = defined('APPLICATION_ENV') ? APPLICATION_ENV: 'product';

	    return $configures['databases'][$env];
	}
}