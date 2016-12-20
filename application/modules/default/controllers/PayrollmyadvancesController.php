<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayrollMyAdvancesController
 *
 * @author suresvan
 */
class Default_PayrollMyAdvancesController extends Zend_Controller_Action {
   public function preDispatch()
    {
        
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('getrequests', 'json')->initContext();		
    }
    
    public function init()
    {
        $this->_options= $this->getInvokeArg('bootstrap')->getOptions();
    }
    
    
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
		
		if($auth->hasIdentity()){
		 	$loginUserId = $auth->getStorage()->read()->id;
			$loginuserRole = $auth->getStorage()->read()->emprole;
			$loginuserGroup = $auth->getStorage()->read()->group_id;
		}	
		
		//login user add permission checking
		$addPermission = sapp_Global::_checkprivileges(PAYROLLMYADVANCES,$loginuserGroup,$loginuserRole,'add');
		
                $limit=4;$offset=0;
		$advancesModel = new Default_Model_Payrolladvances();
		$myAdvances = $advancesModel->getMyAdvances($limit,$offset,$loginUserId);
		$myutilizes = $advancesModel->getMyAdvanceData($loginUserId);
		
		/* To show more advances*/	

		$getadvancesCount = $advancesModel->getAdvancesCount($loginUserId);
		
		$this->view->limit = $limit;
		$this->view->offset = $limit+$offset;
		$this->view->getadvancesCount = $getadvancesCount;
		$this->view->myAdvances = $myAdvances;
		$this->view->myutilizes = $myutilizes;
		$this->view->addPermission = $addPermission;
    }
}
