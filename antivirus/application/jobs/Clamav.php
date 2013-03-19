<?php
class Clamav extends RunnableAbstract
{
	protected $antivirusId = 1;
	
	public function run()
	{
		exec("clamscan {$this->filePath}", $output, $return);
		
		if ($return == 1) {
			$retorno =  $output[0];
			$retorno = str_replace('FOUND', '', $retorno);
			$retorno = explode(':', $retorno);
			$retorno = $retorno[1];
			$retorno = trim($retorno);
			return  $retorno;
		} else {
			return "";
		}
	}
}