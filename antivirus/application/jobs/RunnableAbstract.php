<?php
abstract class RunnableAbstract
{

	protected $connection;
	protected $antivirusId;
	protected $antivirusName;
	protected $antivirusLastUpdate;
	protected $antivirusEngine;
	
	protected $filePath;
	protected $analysisId;
	
	/*
	 * @return String
	 */
	abstract public function run();
	
	public function __construct($filePath, $analysisId)
	{
		$this->connection = mysql_connect('localhost', 'root', '123');
		mysql_select_db('antivirus', $this->connection);
		
		$this->filePath   = $filePath;
		$this->analysisId = $analysisId;
		$this->setAntivirusInfo();
		$this->saveAnalisys($this->run());

		exit(0);
	}
	
	public function saveAnalisys($result)
	{
		$sql  = "insert into analysis_antivirus (id_analysis, id_antivirus, antivirus_last_update, antivirus_engine, result) ";
		$sql .= "values ({$this->analysisId}, {$this->antivirusId}, '{$this->antivirusLastUpdate}', '{$this->antivirusEngine}', '$result')";
		mysql_query($sql, $this->connection);
	}
	
	public function setAntivirusInfo()
	{
		$sql = "select name, last_update, engine from antivirus where id = {$this->antivirusId}";
		$result = mysql_query($sql, $this->connection);
		$row = mysql_fetch_object($result);
		$this->antivirusName = $row->name;
		$this->antivirusLastUpdate = $row->last_update;
		$this->antivirusEngine = $row->engine;
	}
}