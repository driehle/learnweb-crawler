<?php

namespace LearnwebCrawler\Resource;
use Zend\Dom\Query;

class LearnwebCourse extends AbstractLearnweb
{
	protected function _fetchDirectory (array $config, $indent = 0)
	{
		if (LEARNWEB_DEBUG) {
			echo 'Fetching Directory ' . $config['source'] . "\n";
		}
		
		$client = $this->getClient();
		$client->setUri($config['source']);
		$this->_configureClient($client);
	
		$response = $client->send();
		
		if (!$response->isOk()) {
			echo 'Error reading ' . $config['source'] . "\n";
			echo $response->getStatusCode() . ' ' . $response->getReasonPhrase() . "\n";
			return;
		}
		
		$dom = new Query($response->getBody());
		$mainResult = $dom->execute('div[role="main"] li.section');

	
		foreach ($mainResult as $result) {
			$nodePath = $result->getNodePath();
			$titleResult = $dom->queryXPath($nodePath . '//h3');
			$fileResult = $dom->queryXPath($nodePath . '//a');

			if (count($titleResult) < 1) {
				continue;
			}

			$title = $titleResult[0]->firstChild->wholeText;

			if (LEARNWEB_DEBUG) {
				echo "Found section: $title\n";
			}
				
			$id = preg_replace('/\D/', '', $result->getAttribute('id'));
			$target = $config['target'] . sprintf('/%02d - ', $id) . $this->_cleanString($title);

			$newConfig = array('target' => $target);
			if (isset($config['crackpdf'])) {
				$newConfig['crackpdf'] = $config['crackpdf'];
			}

			foreach ($fileResult as $file) {
				$url = $file->getAttribute('href');
				$name = '';

				if ($file->lastChild->nodeType == XML_ELEMENT_NODE) {
					$name = $this->_cleanString($file->lastChild->firstChild->wholeText);
				}

				$this->_configureClient($client);
					
				if (strpos($url, '/mod/resource/') !== false) {
					$url .= (strpos($url, '?') === false ? '?' : '&') . 'redirect=1';
					$this->_fetchFile ($url, $name, $newConfig, $indent + 2);
				}
				elseif (strpos($url, '/mod/url/') !== false) {
					$url .= (strpos($url, '?') === false ? '?' : '&') . 'redirect=1';
					$this->_fetchLink ($url, $name, $newConfig, $indent + 2);
				}
				elseif (preg_match('=/mod/(forum|choice|quiz|assign)/=', $url)) {
					$this->_createLink ($url, $name, $newConfig, $indent + 2);
				}
			}
		}
	}
	
	protected function _fetchFile ($url, $title, array $config, $indent)
	{
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . 'Fetching File ' . $url . "\n";
			echo str_repeat(' ', $indent) . 'Name: ' . $title . "\n";
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		$target = $this->_dropbox . '/' . $config['target'] . '/';
		$client = $this->getClient();
		
		$client->setUri($url);
		$response = $client->send();

		$filename = basename(urldecode($client->getUri()));
		$filename = preg_replace('/\\?(.*)$/', '', $filename);
		$title    = preg_replace('/\\.[a-z]{2,4}$/', '', $title);
		$target  .= $this->_cleanString($title . ' ' . $filename);

		if (!$response->isOk()) {
			echo str_repeat(' ', $indent) . 'Error ';
			echo $response->getStatusCode() . ' ' . $response->getReasonPhrase();
			echo ' fetching file ' . $url . "\n";
			return;
		}
		
		file_put_contents($target, $response->getBody());
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . "File fetched\n";
		}
		
		if (isset($config['crackpdf']) && $config['crackpdf']) {
			if (LEARNWEB_DEBUG) {
				echo str_repeat(' ', $indent) . "Cracking pdf file...\n";
			}
			$this->_pdfcrack->crackFile($target, $config['crackpdf']);
		}
	}
	
	protected function _fetchLink ($url, $title, array $config, $indent)
	{
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . 'Fetching Link ' . $url . "\n";
			echo str_repeat(' ', $indent) . 'Name: ' . $title . "\n";
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		$target = $this->_dropbox . '/' . $config['target'] . '/' . $title . '.url';
		
		$client = $this->getClient();
		$client->setUri($url);
		$response = $client->send();
		
		$link = (string) $client->getUri();
		$content = "[InternetShortcut]\r\n"
				 . "URL=$link\r\n ";
		
		file_put_contents($target, $content);
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . "Link fetched\n";
		}
	}
	
	protected function _createLink ($url, $title, array $config, $indent)
	{
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . 'Creating Link ' . $url . "\n";
			echo str_repeat(' ', $indent) . 'Name: ' . $title . "\n";
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		$target = $this->_dropbox . '/' . $config['target'] . '/' . $title . '.url';
		$content = "[InternetShortcut]\r\n"
				 . "URL=$url\r\n ";
		
		file_put_contents($target, $content);
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . "Link created\n";
		}
	}
}
