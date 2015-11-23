<?php
namespace HMinng\DBLibrary\AtsConfig;

class AtsConfig
{
	public static function configures()
	{
	    $configures = yaml_parse_file(APPLICATION_PATH . '/../conf/custom/data.yml');

        $env = defined('APPLICATION_ENV') ? APPLICATION_ENV: 'product';

	    return $configures['databases'][$env];
	}
}