<?php

namespace LearnwebCrawler\Resource;

use \Zend\Http\Client;
use \Zend\Http\Client\Cookies;

abstract class AbstractLearnweb extends AbstractResource
{
	protected $_client;
	
	public function handle ($config)
	{
		if ($config instanceof \Zend\Config\Config) {
			$config = $config->toArray();
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		if (isset($config['cleanTarget']) && $config['cleanTarget']) {
			$this->_clearDirectoryContent($this->_dropbox . '/' . $config['target']);
		}
		
		$this->_fetchDirectory ($config);
	}
	
	protected function getClient()
	{
		if ($this->_client == null) {
			$this->_client = new Client(null, array(
				'useragent' => 'DropboxBot/1.0',
				'timeout' => 30,
				'sslverifypeer' => false,
				'maxredirects' => 5,
				'storeresponse' => true
			));
			
			$this->_client->setAuth($this->_username, $this->_password);
		}
		
		return $this->_client;
	}
	
	protected function _clearDirectoryContent($dir)
	{
		$dir = rtrim($dir, DIRECTORY_SEPARATOR);
		if (!is_dir($dir)) {
			return false;
		}
		
		$content = scandir($dir);
		
		// dont do anythig if directory contains a file ".keep"
		if (in_array('.keep', $content)) {
			return false;
		}
		
		// handle directory
		foreach ($content as $entry) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			if (is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
				$result = $this->_clearDirectoryContent($dir . DIRECTORY_SEPARATOR . $entry);
				if ($result) {
					rmdir($dir . DIRECTORY_SEPARATOR . $entry);
				}
			}
			else {
				unlink($dir . DIRECTORY_SEPARATOR . $entry);
			}
		}
		return true;
	}
}
