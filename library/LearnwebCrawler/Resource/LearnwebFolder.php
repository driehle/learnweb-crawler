<?php

namespace LearnwebCrawler\Resource;

class LearnwebFolder extends AbstractLearnweb
{
	protected function _fetchDirectory (array $config, $indent = 0)
	{
		if (LEARNWEB_DEBUG) {
			echo 'Fetching Directory ' . $config['source'] . "\n";
		}
		
		$client = $this->getClient();
		
		$client->setUri($config['source']);
		
		$response = $client->send();
		if (!$response->isOk()) {
			echo 'Error reading ' . $config['source'] . "\n";
			return;
		}
		
		$begin = strpos($response->getContent(), '<div id="folder_tree" class="filemanager">');
		$end   = strpos($response->getContent(), "\n", $begin);
		
		if ($begin === false || $end === false) {
			echo 'Error finding content in ' . $config['source'] . "\n";
			return;
		}
		$data = substr($response->getContent(), $begin, ($end - $begin));
		
		$match = array();
		if (preg_match_all('/<a href="([^"]*)">(.*)<\/a>/U', $data, $match, PREG_SET_ORDER)) {
			foreach ($match as $link) {
				$url = html_entity_decode($link[1]);
				$title = html_entity_decode(strip_tags($link[2]));
				$title = $this->_cleanString($title);
				
				if (substr($url, 0, 4) != 'http') {
					// assume relative url
					$url = dirname($config['source']) . '/' . $url;
				}
				
				if (strpos($url, 'forcedownload=1') !== false) {
					if (LEARNWEB_DEBUG) {
						echo str_repeat(' ', $indent) . 'Found file: ' . $title . "\n";
					}
					$this->_fetchFile ($url, $title, $config, $indent);
				}
				else {
					if (LEARNWEB_DEBUG) {
						echo str_repeat(' ', $indent) . 'Found directory: ' . $title . "\n";
					}
					$newConfig = array(
						'source' => $url,
						'target' => $config['target'] . '/' . $title
					);
					if (isset($config['crackpdf'])) {
						$newConfig['crackpdf'] = $config['crackpdf'];
					}
					
					if (!is_dir($this->_dropbox . '/' . $newConfig['target'])) {
						mkdir ($this->_dropbox . '/' . $newConfig['target']);
					}
					$this->_fetchDirectory ($newConfig, $indent + 2);
					if (LEARNWEB_DEBUG) {
						echo str_repeat(' ', $indent) . "Finished Directory\n";
					}
				}
			}
		}
	}
	
	protected function _fetchFile ($url, $title, array $config, $indent = 0)
	{
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . 'Fetching File ' . $url . "\n";
		}
		
		$target = $this->_dropbox . '/' . $config['target'] . '/' . $title;
		
		$client = $this->getClient();
		
		$client->setUri($url);
		//$client->setStream($target);
		$response = $client->send();
		
		file_put_contents($target, $response->getContent());
		
		if (!$response->isOk()) {
			echo str_repeat(' ', $indent) . 'Error fetching file ' . $url . "\n";
			return;
		}
		
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
}
