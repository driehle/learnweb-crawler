<?php

namespace LearnwebCrawler;

class PdfCrack
{
	protected $pdf2PsCmd = null;
	protected $ps2PdfCmd = null;
	
	public function __construct($pdf2PsCmd, $ps2PdfCmd)
	{
		if ($pdf2PsCmd != null) {
			$this->pdf2PsCmd = (string) $pdf2PsCmd;
		}
		else {
			$this->pdf2PsCmd = "pdftops -upw %s %s %s";
		}
		
		if ($ps2PdfCmd != null) {
			$this->ps2PdfCmd = (string) $ps2PdfCmd;
		}
		else {
			$this->ps2PdfCmd = "ps2pdf %s %s";
		}
	}
	
	public function crackFile ($file, $password)
	{
		if ($password === false) return;
		if (substr($file, -4) != '.pdf') return;
		
		$psFile = substr($file, 0, -4) . '.tmp.ps';
		$crackFile = substr($file, 0, -4) . '.cracked.pdf';
		
		$cmd = sprintf(
			$this->pdf2PsCmd,
			escapeshellarg($password),
			escapeshellarg($file),
			escapeshellarg($psFile)
		);
		exec ($cmd);
		
		$cmd = sprintf(
			$this->ps2PdfCmd,
			escapeshellarg($psFile),
			escapeshellarg($crackFile)
		);
		exec ($cmd);
		
		@unlink ($psFile);
	}
}
