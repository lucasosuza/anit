<?php
class McAfee extends RunnableAbstract
{
	protected $antivirusId = 2;
	
	public function run()
	{
                $output = NULL;
		exec("uvscan -v {$this->filePath}", $output, $return);
				
		if (is_array($output)) {
			foreach ($output as $line) {
				if (strpos($line, 'Found:') !== false) {
					$retorno =  $line;
					$retorno = explode('Found:', $retorno);
					$retorno = $retorno[1];
					$retorno = trim($retorno);
					return  $retorno;					
				}
			}
		} else {
			return "";
		}
		
		return "";
	}
}