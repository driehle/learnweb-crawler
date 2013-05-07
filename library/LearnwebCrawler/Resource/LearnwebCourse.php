<?php

namespace LearnwebCrawler\Resource;

class LearnwebCourse extends AbstractLearnweb
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
		
		$body  = $response->getBody();		
		$begin = strpos($body, '<div id="content">');
		$end   = strpos($body, '#content -->');
		
		if ($begin === false || $end === false) {
			echo 'Error finding content in ' . $config['source'] . "\n";
			return;
		}
		$data = substr($body, $begin, ($end - $begin));
		
		$moduleMatch = array();
		if (preg_match_all('/<li id="section-(\d+)" class="section main[^"]*"\s*>(.*)<\/ul><!--class=\'section\'-->/Us', 
						$data, $moduleMatch, PREG_SET_ORDER)) 
		{
			//var_dump($moduleMatch);
			foreach ($moduleMatch as $module) {
				$id = $module[1];
				$html = $module[2];
				
				$matchTitle = array();
				$title = '';
				if (preg_match('/<h3 class="sectionname">(.*)<\/h3>/Us', $html, $matchTitle)) {
					$title = $matchTitle[1];
					if (strpos($title, '<br') !== false) {
						$title = substr($title, 0, strpos($title, '<br'));
					}
					$title = html_entity_decode(strip_tags($title));
					$title = $this->_cleanString($title);
				}
				
				$target = $config['target'] . sprintf('/%02d - ', $id);
				if (empty($title)) {
					$target .= 'Unbenanntes Modul';
				}
				else {
					$target .= $title;
				}
				
				if (LEARNWEB_DEBUG) {
					echo 'Found module ' . $title . "\n";
					echo 'Target ' . $target . "\n";
				}
								
				$newConfig = array('target' => $target);
				if (isset($config['crackpdf'])) {
					$newConfig['crackpdf'] = $config['crackpdf'];
				}
				
				$filesMatch = array();
				if (preg_match_all('/<li[^>]*>(.*)<\/li>/Us', $html, $filesMatch, PREG_SET_ORDER)) {
				
					foreach ($filesMatch as $key => $file) {
						$fMatch = array();
						if (preg_match('/<a [^>]*href="([^"]*)"[^>]*>(.*)<\/a>/U', $file[1], $fMatch)) {
							$url = html_entity_decode($fMatch[1]) . '&redirect=1';
							
							$title = $fMatch[2];
							if (strpos($title, '<br') !== false) {
								$title = substr($title, 0, strpos($title, '<br'));
							}
							$title = html_entity_decode(strip_tags($title));
							$title = trim(str_replace('Datei', '', $title));
							$title = sprintf(
								'%02d %s - ',
								$key,
								$this->_cleanString($title)
							);
							
							if (defined('DEBUG')) {
								echo '  Found file ' . $title . "\n";
							}
							
							if (strpos($url, '/resource/') !== false) {
								$this->_fetchFile ($url, $title, $newConfig, $indent + 2);
							}
						}
					}
				}
			}
		}
	}
	
	protected function _fetchFile ($url, $title, array $config, $indent)
	{
		if (LEARNWEB_DEBUG) {
			echo str_repeat(' ', $indent) . 'Fetching File ' . $url . "\n";
		}
		
		if (!is_dir($this->_dropbox . '/' . $config['target'])) {
			mkdir ($this->_dropbox . '/' . $config['target']);
		}
		
		$target = $this->_dropbox . '/' . $config['target'] . '/' . $title;
		
		$client = $this->getClient();
		
		$client->setUri($url);
		$response = $client->send();
		$target .= $this->_cleanString(basename(urldecode($client->getUri())));
		

		if (!$response->isOk()) {
			echo str_repeat(' ', $indent) . 'Error fetching file ' . $url . "\n";
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
}
