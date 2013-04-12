<?php

namespace LearnwebCrawler\Resource;

abstract class AbstractResource
{
	protected $_username;
	protected $_password;
	protected $_pdfcrack;
	protected $_dropbox;
	
	public function setAuth ($user, $pw)
	{
		$this->_username = (string) $user;
		$this->_password = (string) $pw;
	}
	
	public function setPdfCrack ($pdf)
	{
		$this->_pdfcrack = $pdf;
	}
	
	public function setDropboxPath ($path)
	{
		$path = (string) $path;
		if (!is_dir($path)) {
			throw new Exception(sprintf(
				'Error: path "%s" is not a directory',
				$path
			));
		}
		$this->_dropbox = $path;
	}
	
	protected function _cleanString ($string)
	{
		$string = preg_replace('/[^a-zA-z0-9\-_äöüÄÖÜß. ]/', '_', $string);
		$string = str_replace(
			array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'),
			array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue'),
			$string
		);
		$string = trim($string, ' _');
		
		if (strlen($string) > 50) {
			$lastDot = strrpos($string, '.');
			if ($lastDot !== false) {
				$string = substr($string, 0, 35) . substr($string, $lastDot);
			}
			else {
				$string = substr($string, 0, 25) . '...' . substr($string, -15);
			}
		}
		
		return $string;
	}
	
	abstract public function handle ($config);
}
