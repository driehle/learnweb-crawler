<?php

namespace LearnwebCrawler;

class PdfCrack
{
	public function crackFile ($file, $password)
	{
		if (substr($file, -4) != '.pdf') return;
		
		$psFile = substr($file, 0, -4) . '.tmp.ps';
		$crackFile = substr($file, 0, -4) . '.cracked.pdf';
		
		$cmd = sprintf(
			'pdftops -upw %s %s %s',
			escapeshellarg($password),
			escapeshellarg($file),
			escapeshellarg($psFile)
		);
		exec ($cmd);
		
		$cmd = sprintf(
			'ps2pdf %s %s',
			escapeshellarg($psFile),
			escapeshellarg($crackFile)
		);
		exec ($cmd);
		
		@unlink ($psFile);
	}
}
