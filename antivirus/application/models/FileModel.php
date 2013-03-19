<?php
require_once 'FileTable.php';
require_once 'AnalysisTable.php';
require_once 'AntivirusTable.php';
require_once 'AnalysisAntivirusTable.php';

class FileModel
{
	/*
	 * 
		truncate table analysis_antivirus;
		truncate table analysis;
		truncate table file;
	 * 
	 */
	
	public function calcHash($file)
	{		
		$result[] = hash_file('sha256', $file);
		$result[] = hash_file('sha1', $file);
		$result[] = hash_file('md5', $file);
		return $result;
	}
	
	public function fileExists($sha256)
	{
		$file = new FileTable();
		$select = $file->select()->where('sha256 = ?', $sha256);
		$result = $file->fetchAll($select);
		return $result->valid();
	}
	
	public function getFile($sha256)
	{
		$file = new FileTable();
		$select = $file->select()->where('sha256 = ?', $sha256);
		$result = $file->fetchAll($select);
		if ($result->valid()) {
			return $result->current();
		} else {
			return false;
		}
	}
	
	public function getAnalisys($id_file, $id_analysis = null)
	{
		$analysisTable = new AnalysisTable();
		
		if ($id_analysis !== null) {
			$select = $analysisTable->select()->where('id=?', $id_analysis);		
		} else {
			$select = $analysisTable->select()
									->where('id_file=?', $id_file)
									->order('id desc')->limit(1);
		}
		
		$resultLastAnalisys = $analysisTable->fetchAll($select);
		
		if ($resultLastAnalisys->valid()) {
			$rowLastAnalisys = $resultLastAnalisys->current();
		} else {
			return array();
		}
		
		$analisysAntivirus = new AnalysisAntivirusTable();
		$select = $analisysAntivirus->select()->where('id_analysis=?', $rowLastAnalisys->id);
		$resultAnalisys = $analisysAntivirus->fetchAll($select);
		
		if ($resultAnalisys->valid()) {
			return $resultAnalisys->toArray();
		} else {
			return array();
		}
	}
	
	public function getLastAnalisys($id_file)
	{
		$analysisTable = new AnalysisTable();
		$select = $analysisTable->select()
								->where('id_file=?', $id_file)
								->order('id desc')->limit(1);

		$resultLastAnalisys = $analysisTable->fetchAll($select);
		if ($resultLastAnalisys->valid()) {
			return $resultLastAnalisys->current();
		} else {
			return false;
		}
	}
	
	public function getCountAntivirus()
	{
		$antivirus = new AntiVirusTable();
		$select = $antivirus->select()->where('active = 1');
		$result = $antivirus->fetchAll($select);
		if ($result->valid()) {
			return count($result->toArray());
		} else {
			return 0;
		}
	}
	
	public function getAntiviroses()
	{
		$antivirus = new AntiVirusTable();
		$select = $antivirus->select()->where('active = 1');
		$result = $antivirus->fetchAll($select);
		if ($result->valid()) {
			return $result;
		} else {
			return array();
		}
	}
	
	public function analyseFile($fileData)
	{
		$file = new FileTable();
		$idFile = $file->insert($fileData);
		$this->startAnalysis($idFile, $fileData);
	}
	
	public function startAnalysis($idFile, $fileData)
	{
		$dataAnalysis['id_file']          = $idFile;
		$dataAnalysis['file_name']        = $fileData['file_name'];
		$dataAnalysis['date_analysis']    = date('Y-m-d H:i:s');
		$dataAnalysis['active_antivirus'] = $this->getCountAntivirus();
		
		$analysis   = new AnalysisTable();
		$idAnalysis = $analysis->insert($dataAnalysis);
		$pathJob    = APPLICATION_PATH . '/jobs/start.php';
		//$filePath   = APPLICATION_PATH . '/tmp/' . $idFile;
		$filePath   = '/tmp/' . $idFile;
		
		move_uploaded_file($_FILES['file_upload']['tmp_name'], $filePath);
		
		$antiviroses = $this->getAntiviroses();
		foreach ($antiviroses as $rowAntivirus) {
			exec("php $pathJob {$rowAntivirus->class_name} $filePath $idAnalysis > /dev/null 2>&1 &");
		}
		
		//unlink($filePath);
	}
	
	public function startReanalysis($file, $fileName)
	{
		$dataAnalysis['id_file']          = $file->id;
		$dataAnalysis['file_name']        = $fileName;
		$dataAnalysis['date_analysis']    = date('Y-m-d H:i:s');
		$dataAnalysis['active_antivirus'] = $this->getCountAntivirus();
	
		$analysis   = new AnalysisTable();
		$idAnalysis = $analysis->insert($dataAnalysis);
		$pathJob    = APPLICATION_PATH . '/jobs/start.php';
		//$filePath   = APPLICATION_PATH . '/tmp/' . $file->id;
		$filePath   = '/tmp/' . $file->id;
	
		file_put_contents($filePath, base64_decode($file->file_content));
	
		$antiviroses = $this->getAntiviroses();
		foreach ($antiviroses as $rowAntivirus) {
			exec("php $pathJob {$rowAntivirus->class_name} $filePath $idAnalysis > /dev/null 2>&1 &");
		}
	}
}