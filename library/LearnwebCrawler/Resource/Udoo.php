<?php

namespace LearnwebCrawler\Resource;

use Zend\Http\Client;

class Udoo extends AbstractResource
{
	protected $_client;
	protected $_cookies;
	
	protected function getClient()
	{
		if ($this->_client == null) {
			$this->_client = new Client(null, array(
				'useragent' => 'DropboxBot/1.0',
				'timeout' => 30,
				'sslverifypeer' => false
			));
			
			$this->_client->setUri('http://udoo.uni-muenster.de/modules/accounts/login.php5');
			$this->_client->setParameterGet('referer', '/modules/myAccount/index.php5');
			$this->_client->setParameterPost('username', $this->_username);
			$this->_client->setParameterPost('passwd', $this->_password);
			$this->_client->setParameterPost('lang', '');
			$this->_client->setParameterPost('login', 'Login');
			$this->_client->setMethod('POST');
			
			$response = $this->_client->send();
			$this->_cookies = \Zend\Http\Client\Cookies::fromResponse($response);
			
			$this->_client->resetParameters(true);
		}
		
		return $this->_client;
	}
	
	public function handle ($config)
	{
		if ($config instanceof \Zend\Config\Config) {
			$config = $config->toArray();
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		$client = $this->getClient();
		$client->setUri($config['source']);
		$client->setCookies($this->_cookies->getMatchingCookies($client->getUri()));
		$response = $client->send();
		$client->resetParameters(true);
		
		$match = array();
		if (preg_match_all('/<h5>(.*)<\/h5>\s*(<table.*<\/table>)/Us', 
				$response->getContent(), $match, PREG_SET_ORDER)) 
		{
			foreach ($match as $set) {
				$title = html_entity_decode(strip_tags($set[1]));
				$title = $this->_cleanString($title);
				$html = $set[2];
				
				$target = $config['target'] . '/' . $title;
				if (isset($config['map'])) {
					foreach ($config['map'] as $map) {
						if ($map['from'] == $title) {
							$target = $config['target'] . '/' . $map['to'];
						}
					}
				}
				
				if (defined('DEBUG')) {
					echo 'Found list ' . $title . ', target ' . $target . "\n";
				}
				
				$rowMatch = array();
				if (preg_match_all('/<tr>(.*)<\/tr>/Us', $html, $rowMatch, PREG_SET_ORDER)) {
					foreach ($rowMatch as $row) {
						$linkMatch = array();
						if (preg_match('/href="([^"]+)"/', $row[1], $linkMatch)) {
							if (defined('DEBUG')) {
								echo 'Found file ' . $linkMatch[1] . "\n";
							}
							$newConfig = array_merge($config, array('target' => $target));
							$this->_fetchFile($linkMatch[1], $newConfig);
						}
					}
				}
			}
		}
	}
	
	public function _fetchFile ($url, array $config)
	{
		$client = $this->getClient();
		$client->setUri($url);
		$client->setCookies($this->_cookies->getMatchingCookies($client->getUri()));
		$response = $client->send();
		$client->resetParameters(true);
		
		$dispos = $response->getHeaders()->get('Content-Disposition');
		if (is_array($dispos)) $dispos = $dispos[0];
		$dispos = preg_replace('/^attachment; ?filename=/i', '', (string) $dispos);
		$dispos = $this->_cleanString($dispos);
		
		if (defined('DEBUG')) {
			echo 'Filename ' . $dispos . "\n";
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		$target = $this->_dropbox . '/' . $config['target'] . '/' . $dispos;
		
		file_put_contents($target, $response->getContent());
		
		if (isset($config['crackpdf']) && $config['crackpdf']) {
			if (defined('DEBUG')) {
				echo str_repeat(' ', $indent) . "Cracking pdf file...\n";
			}
			$this->_pdfcrack->crackFile($target, $config['crackpdf']);
		}
	}
}
