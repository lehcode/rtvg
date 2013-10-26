<?php
/**
 * Duplicates Controller
 * 
 * @author     Antony Repin <egeshisolutions@gmail.com>
 * @package backend
 * @version    $Id: DuplicatesController.php,v 1.6 2013-03-16 12:46:19 developer Exp $
 *
 */
class Admin_DuplicatesController extends Rtvg_Controller_Admin
{
	
    public function init()
    {
    	parent::init();
    }

    public function indexAction()
    {
        // action body
    }

    public function actorsAction()
    {
    	ini_set('max_execution_time', 0);
    	$model = new Admin_Model_Actors();
    	$dupes  = $model->getDuplicates();
    	$this->view->assign('origin', $dupes['origin']);
    	$this->view->assign('clones', $dupes['clones']);
    	$total = $dupes['total'];
    	$this->view->assign('total', $dupes['total']);
    	$total = $dupes['processed'];
    	$this->view->assign('processed', $dupes['processed']);
    	$processed_pct = round($dupes['processed']/(($dupes['total']/100)), 2);
    	$this->view->assign('processed_pct', $processed_pct);
		$this->render('actors-duplicates');
       
    }

    public function actorsNamesAction()
    {
        ini_set('max_execution_time', 0);
    	$model = new Admin_Model_Actors();
    	$model->fixNames();
    	$this->_forward('index');
    }
    
	public function mainActorAction(){
		
		$request = $this->_request->getParams();
		$filter_id = new Zend_Filter_Digits();
	   	$id = $filter_id->filter($request['id']);
		$table = new Admin_Model_DbTable_Actors();
		$programs_props = new Admin_Model_DbTable_ProgramsProps();
		
		try {
			$progs = $programs_props->fetchAll("`actors` LIKE '%,".$id."]' OR `actors` LIKE '[".$id.",%' OR `actors` LIKE '%,".$id.",%'");
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__FILE__.': '.__LINE__);
		}
		
		if (count($progs)==0) {
			try{
				die(__FILE__.': '.__LINE__);
				//$table->delete("`id`='$id'");
			} catch (Exception $e) {
				echo $e->getMessage();
				die(__FILE__.': '.__LINE__);
			}
		} else {
			
			foreach ($progs as $p) {
				$prog = $p->toArray();
				$serializer = new Zend_Serializer_Adapter_Json();
				$actors = $serializer->unserialize($prog['actors']);
				$model  = new Admin_Model_Actors();
    			$main   = $model->getPersonByKey($id);
    			$dupes  = $model->getPersonDuplicates($main);
    			die(__FILE__.': '.__LINE__);
    			
				if ($k = array_search($id, $actors)){
					if ((int)$actors[$k] != (int)$dupes['origin']['id']) {
						$actors[$k] = (int)$dupes['origin']['id'];
					}
				}
				die(__FILE__.': '.__LINE__);
			}
			die(__FILE__.': '.__LINE__);
		}
		
		$this->_forward('actors');
		
	}
	
	public function deleteDirectorAction(){
		
		$request = $this->_request->getParams();
		$filter_id = new Zend_Filter_Digits();
	   	$id = $filter_id->filter($request['id']);
		$table = new Admin_Model_DbTable_Directors();
		
		try{
			$table->delete("`id`='$id'");
		} catch (Exception $e) {
			echo $e->getMessage();
			die(__FILE__.': '.__LINE__);
		}
		
		$this->_forward('actors');
		
	}
	
	public function directorsNamesAction(){
		
		ini_set('max_execution_time', 0);
		$model = new Admin_Model_Directors();
		$model->fixNames();
    	$this->_forward('index');
		
	}
	
	public function directorsAction() {
    	ini_set('max_execution_time', 0);
    	$model = new Admin_Model_Directors();
    	$dupes  = $model->getDuplicates();
    	$this->view->assign('origin', $dupes['origin']);
    	$this->view->assign('clones', $dupes['clones']);
    	$total = $dupes['total'];
    	$this->view->assign('total', $dupes['total']);
    	$total = $dupes['processed'];
    	$this->view->assign('processed', $dupes['processed']);
    	$processed_pct = round($dupes['processed']/(($dupes['total']/100)), 2);
    	$this->view->assign('processed_pct', $processed_pct);
		$this->render('directors-duplicates');
    }


}





