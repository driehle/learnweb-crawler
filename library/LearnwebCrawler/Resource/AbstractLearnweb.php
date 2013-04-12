<?php

namespace LearnwebCrawler\Resource;

use \Zend\Http\Client;
use \Zend\Http\Client\Cookies;

abstract class AbstractLearnweb extends AbstractResource
{
	protected $_client;
	
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
}
