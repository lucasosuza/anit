<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	public function _initDb()
	{
		$config = $this->getApplication()->getOptions();

		$db = Zend_Db::factory($config['db']['adapter'],$config['db']['config']);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

		//Configurando o cache
		$cache = Zend_Cache::factory(
			'Core', 'File',
			$config['cache']['frontendOptions'],
			$config['cache']['backendOptions']
		);

		Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

		Zend_Registry::set('config', $config);
		Zend_Registry::set('db', $db);
	}
	
	public function _initLayout()
	{
		Zend_Layout::startMvc(array(
			'layout'     => 'default',
			'layoutPath' => APPLICATION_PATH . '/views/layouts'
		));
	}

	protected function _initAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(array(
			'basePath' => APPLICATION_PATH,
        	'namespace' => ''
        ));

		return $autoloader;
	}
}

