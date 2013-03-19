<?php
require_once 'FileModel.php';
require_once 'AntivirusTable.php';
require_once 'AnalysisAntivirusTable.php';

class FileController extends Zend_Controller_Action
{

	public function validate()
	{
		if (!$this->_request->isPost()) {
			die('no data');
		} else {
			
			if (!array_key_exists('file_upload', $_FILES)) {
				die('no file');
			}
			
			if ((trim($_FILES['file_upload']['size']) == '0') or 
			    (trim($_FILES['file_upload']['size']) == '')) {
				die('no file size');
			}
		}
	}

    public function analiseAction()
    {
    	$this->validate();
    	$fileModel = new FileModel();
        
        list($sha256, $sha1, $md5) = $fileModel->calcHash($_FILES['file_upload']['tmp_name']);

        $fileData['sha256']       = $sha256;
        $fileData['sha1']         = $sha1;
        $fileData['md5']          = $md5;
        $fileData['file_size']    = $_FILES['file_upload']['size'];
        $fileData['file_name']    = $_FILES['file_upload']['name'];
        $fileData['file_type']    = $_FILES['file_upload']['type'];        
        
        if (!$fileModel->fileExists($sha256)) {

        	$fileData['file_content'] = base64_encode(file_get_contents($_FILES['file_upload']['tmp_name']));
        	$fileModel->analyseFile($fileData);
        	
        	$fileData['file_content'] = null;
        	$fileData['file_exists'] = false;
        	die(json_encode($fileData));
        } else {
        	$fileData['file_exists'] = true;

        	$file         = $fileModel->getFile($sha256);
        	$analisys     = $fileModel->getAnalisys($file->id);
        	$lastAnalisys = $fileModel->getLastAnalisys($file->id);
        	
        	$ratio = 0;
        	foreach ($analisys as $result) {
        		if (trim($result['result']) != '') {$ratio++;}
        	}
        	
        	$fileData['date_analysis'] = $lastAnalisys->date_analysis;
        	$fileData['ratio']         = "{$ratio} / {$lastAnalisys->active_antivirus}";
        	        	
        	die(json_encode($fileData));
        }
        
        die;
    }

    public function viewAction()
    {
    	$sha256 = $this->_request->getParam('id');
    	$fileModel = new FileModel();
    	$file = $fileModel->getFile($sha256);
    	
    	if ($file !== false) {
    		$antivirus = new AntivirusTable();
    		$this->view->antiviruses = $antivirus->fetchAll('active = 1');
    		$this->view->file = $file;
    		$analisys = $fileModel->getAnalisys($file->id);
    		$lastAnalisys = $fileModel->getLastAnalisys($file->id);
    		
    		$this->view->analisysJson = json_encode($analisys);
    		$this->view->date_analysis = $lastAnalisys->date_analysis;
    		$this->view->id_analysis = $lastAnalisys->id;
    		$this->view->active_antivirus = $lastAnalisys->active_antivirus;

    	} else {
    		die('file not found');
    	}
    }
    
    public function getAnalisysAction()
    {
    	$sha256      = $this->_request->getParam('id');
    	$id_analysis = $this->_request->getParam('analysis');
    	
    	$fileModel = new FileModel();
    	$file = $fileModel->getFile($sha256);
    	
    	if ($file !== false) {
    		$analisys = $fileModel->getAnalisys($file->id, $id_analysis);
    		if ($analisys !== false) {
    			die(json_encode($analisys));
    		} else {
    			die('false');
    		}
    	} else {
    		die('false');
    	}
    }
    
    public function reanalysisAction()
    {
    	$sha256   = $this->_request->getParam('id');
    	$fileName = $this->_request->getParam('name');
    	
    	$fileModel = new FileModel();
    	$file = $fileModel->getFile($sha256);
    	
    	if ($file !== false) {
    		$fileModel->startReanalysis($file, $fileName);
    		$this->_redirect(SYSTEM_PATH . "/file/view/id/{$sha256}");
    	} else {
    		die('file not found');
    	}
    }

}