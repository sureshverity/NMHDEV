<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2015 Sapplica
 *   
 *  Sentrifugo is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Sentrifugo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Sentrifugo.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Sentrifugo Support <support@sentrifugo.com>
 ********************************************************************************/

class Default_FormulafieldsController extends Zend_Controller_Action
{

    private $options;
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
		$servicedeskdepartmentmodel = new Default_Model_Formulafields();	
                $call = $this->_getParam('call');
		if($call == 'ajaxcall')
				$this->_helper->layout->disableLayout();
		
		$view = Zend_Layout::getMvcInstance()->getView();		
		$objname = $this->_getParam('objname');
		$refresh = $this->_getParam('refresh');
		$dashboardcall = $this->_getParam('dashboardcall');
		
		$data = array();
		$searchQuery = '';
		$searchArray = array();
		$tablecontent='';
		
		if($refresh == 'refresh')
		{
		    if($dashboardcall == 'Yes')
				$perPage = DASHBOARD_PERPAGE;
			else	
				$perPage = PERPAGE;
			$sort = 'DESC';$by = 'e.ModifiedDate';$pageNo = 1;$searchData = '';$searchQuery = '';$searchArray='';
		}
		else 
		{
			$sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'DESC';
			$by = ($this->_getParam('by')!='')? $this->_getParam('by'):'e.ModifiedDate';
			if($dashboardcall == 'Yes')
				$perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
			else 
				$perPage = $this->_getParam('per_page',PERPAGE);
			$pageNo = $this->_getParam('page', 1);
			/** search from grid - START **/
			$searchData = $this->_getParam('searchData');	
			$searchData = rtrim($searchData,',');
			/** search from grid - END **/
		}
				
		$dataTmp = $servicedeskdepartmentmodel->getGrid($sort, $by, $perPage, $pageNo, $searchData,$call,$dashboardcall);		 		
					
		array_push($data,$dataTmp);
                //print_r($dataTmp); exit;
		$this->view->dataArray = $data;
		$this->view->call = $call ;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		//$this->renderScript('commongrid/index.phtml');
               
		$this->render('formulafields/index', null, true);
		
    }

     public function addAction()
	{
                  
             $auth = Zend_Auth::getInstance();
     	   if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
					$loginuserRole = $auth->getStorage()->read()->emprole;
					$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		$addform = new Default_Form_formulafields();
		$addModel = new Default_Model_Formulafields();
                $formulaexistcomponents = $addModel->getFormulacomponents();
                
                $componentsModel = new Default_Model_Components();
               
                $data = $componentsModel->getComponentsforformulafield();
                
                                      
					 $shortname = $data['shortname'];
					$formulatype = array('1'=>'Global');
					$operator = array('-1'=>'--Select--','1'=>'+','2'=>'-');
					
                                        if(!empty($data))
					{
                                            
                                            foreach ($data as $res) {
                                                $addform->shortname->addMultiOption($res['id'],utf8_encode($res['shortname']));
                                                $addform->setDefault('shortname',$shortname);
                                            }
//					 foreach($formulaexistcomponents as $formulacomponent)
//                                         {
//                                                foreach($data as $res)
//                                         {
//                                                    if($formulacomponent['ComponentId'] != $res['id'])
//                                                    {
//                                                    $addform->shortname->addMultiOption($res['id'],utf8_encode($res['shortname']));
//                                                    $addform->setDefault('shortname',$shortname);
//                                                    }
//                                         }
//                                         }
                                                    $addform->FormulaType->addMultiOption('1','Global');
                                                    $addform->setDefault('FormulaType',$formulatype);
                                                    $addform->Operator->addMultiOption($operatordata[id],$operator);       
                                                    $addform->setDefault('Operator',$Operator);                                                
                                                    $addform->populate($data);
                                                    $addform->setAttrib('action',BASE_URL.'formulafields/edit/id/'.$id);
                                                    $this->view->data = $data;
					}else
					{
						$this->view->ermsg = 'norecord';
					}
//               
		$msgarray = array();
		$addform->setAttrib('action',BASE_URL.'formulafields/add');
		$this->view->form = $addform; 
                
		if($this->getRequest()->getPost()){
      		$result = $this->save($addform);	
		    $this->view->msgarray = $result; 
		}
                $this->render('form');            
                      
	}

    public function viewAction()
	{	
		$id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		$objName = 'formulafields';
		$form = new Default_Form_formulafields();
		$form->removeElement("submit");
		$elements = $form->getElements();
		if(count($elements)>0)
		{
			foreach($elements as $key=>$element)
			{
				if(($key!="Cancel")&&($key!="Edit")&&($key!="Delete")&&($key!="Attachments")){
				$element->setAttrib("disabled", "disabled");
			}
        	}
        }
		try
		{
		    if($id)
			{
			    if(is_numeric($id) && $id>0)
				{
					$model = new Default_Model_Formulafields();	
					$data = $model->getFormulaID($id);
					if(!empty($data))
					{
						$data = $data[0]; 
                                               // print_r($data); exit;
						$form->populate($data);
					}else
					{
					   $this->view->ermsg = 'norecord';
					}
                } 
                else
				{
				   $this->view->ermsg = 'norecord';
				}				
			}
            else
			{
			   $this->view->ermsg = 'norecord';
			} 			
		}
		catch(Exception $e)
		{
			   $this->view->ermsg = 'nodata';
		}
		$this->view->controllername = $objName;
		$this->view->id = $id;
		$this->view->data = $data;
		$this->view->flag = 'view';
		$this->view->form = $form;
		$this->render('form');	
             
	}
	

        public function editAction()
	{
     
                $auth = Zend_Auth::getInstance();
                if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
					$loginuserRole = $auth->getStorage()->read()->emprole;
					$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
                $id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
                {
			$this->_helper->layout->disableLayout();
                }
		
                
                 $editform = new Default_Form_formulafields();
                
		$editModel = new Default_Model_Formulafields();
		$componentsModel = new Default_Model_Components();
                
               
                $editData = $editModel->getFormulaDatabyID($id);
               
                $shortname = $editData[0]['ComponentId'];
               
                $Operator = $editData[0]['Operator'];
                
                		$formulatype = array('1'=>'Global');
                                $operatordata = array('-1'=>'--Select--','+'=>'+','-'=>'-');
                                $shortnamedata = $componentsModel->getComponentsforformulafield();
                              // print_r($shortnamedata); exit;
                               		foreach($shortnamedata as $res) 
					$editform->shortname->addMultiOption($res['id'],utf8_encode($res['shortname']));		
					if(isset($shortname) && $shortname != 0 && $shortname != '')
					$editform->setDefault('shortname',$shortname);
                                       $editform->FormulaType->addMultiOption('1','Global');
                                                $editform->setDefault('FormulaType',$formulatype);
                                          $editform->Operator->addMultiOption($operatordata[id],$operatordata);       
					$editform->setDefault('Operator',$Operator);
                                       
//				}
                
                
		$editform->submit->setLabel('Update');
	 	try
        {		
			if($id)
			{
                           
			    if(is_numeric($id) && $id>0)
				{
					$data = $editModel->getFormulaDatabyID($id);
					
                                        if(!empty($data))
					{
						  $data = $data[0];
                                                  
						$editform->populate($data);
                                                
						$editform->setAttrib('action',BASE_URL.'formulafields/edit/id/'.$id);
                        $this->view->data = $data;
					}else
					{
						$this->view->ermsg = 'norecord';
					}
				}
                else
				{
					$this->view->ermsg = 'norecord';
				}				
			}
			else
			{
				$this->view->ermsg = 'nodata';
			}
		}	
		catch(Exception $e)
		{
			   $this->view->ermsg = 'nodata';
		}	
		$this->view->form = $editform;
		if($this->getRequest()->getPost()){
      		$result = $this->save($editform);	
		    $this->view->msgarray = $result; 
		}
		$this->render('form');	 		
	}
	
	
	public function save($empform)
	{
	  $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
		} 
	    $employeemodel = new Default_Model_Formulafields();
		$msgarray = array();
	    
		  if($empform->isValid($this->_request->getPost())){
            try{
            $id = $this->_request->getParam('id');
          
             $shortname = $this->_request->getParam('shortname');	
                        $operator='';
			$formulatype = $this->_request->getParam('FormulaType');
			$operatorid = $this->_request->getParam('Operator');
                        if($operatorid =='1')
                        {
                            $operator = '+';
                        }
                        else{
                             $operator = '-';
                        }
			$isondays = $this->_request->getParam('IsOnDays');
			$DomainFormula = $this->_request->getParam('DomainFormula');
			$actionflag = '';
			$tableid  = ''; 
			   $data = array('FormulaType'=>$formulatype, 
							 'ComponentId'=>$shortname,
							 'Operator'=>$operator,
							  'IsOnDays'=>$isondays,
							  'DomainFormula'=>$DomainFormula,
							  'CreatedBy'=>$loginUserId,
							  'ModifiedBy'=>$loginUserId,
							  'ModifiedDate'=>gmdate("Y-m-d H:i:s"),
							  'CreatedDate'=>gmdate("Y-m-d H:i:s")
					);
				if($id!=''){
					$where = array('id=?'=>$id);  
					$actionflag = 2;
				}
				else
				{
					$data['CreatedBy'] = $loginUserId;
					$data['CreatedDate'] = gmdate("Y-m-d H:i:s");
					$data['isactive'] = 1;
					$where = '';
					$actionflag = 1;
				}
                               
				$Id = $employeemodel->SaveorUpdateformulaData($data, $where);
				if($Id == 'update')
				{
				   $tableid = $id;
				   $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Formula updated successfully."));
				}   
				else
				{
				   $tableid = $Id; 	
					$this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Formula added successfully."));					   
				}   
				$menuID = FORMULAFIELDS;
				$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);
				$this->_redirect('formulafields');	
                  }
        catch(Exception $e)
          {
             $msgarray['service_desk_name'] = "Something went wrong, please try again.";
             return $msgarray;
          }
		}else
		{
			$messages = $empform->getMessages();
			foreach ($messages as $key => $val)
				{
					foreach($val as $key2 => $val2)
					 {
						$msgarray[$key] = $val2;
						break;
					 }
				}
			return $msgarray;	
		}
	
	}
	
	public function deleteAction()
	{
	     $auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
				$loginUserId = $auth->getStorage()->read()->id;
		 }
		  $id = $this->_request->getParam('objid');
               
		 $messages['message'] = '';
		 $actionflag = 3;
		    if($id)
			{
	    	  $componentmodel = new Default_Model_Formulafields();
			  $components =  $componentmodel->checkformulas($id);
                         // print_r($components);exit;
			  if($components > 0)
			  {				
				  $data = array('isactive'=>0);
				  $where = array('id=?'=>$id);
				  $Id = $componentmodel->SaveorUpdateformulaData($data, $where);
					if($Id == 'update')
					{
					   $menuID = FORMULAFIELDS;
					   $result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$id); 
					   $messages['message'] = 'Formula deleted successfully.';
					   $messages['msgtype'] = 'success';
					}else{
					   $messages['message'] = 'Formula cannot be deleted.';			
					   $messages['msgtype'] = 'error';
					}
				}
			}
			else
			{ 
			 $messages['message'] = 'Formula cannot be deleted.';
			 $messages['msgtype'] = 'error';
			}
			$this->_helper->json($messages);
		
	}
	
public function addpopupAction()
	{
		Zend_Layout::getMvcInstance()->setLayoutPath(APPLICATION_PATH."/layouts/scripts/popup/");
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
			$loginUserId = $auth->getStorage()->read()->id;
		}
		$id = $this->getRequest()->getParam('id');

		$msgarray = array();
		$controllername = 'employeecomponents';
		$form = new Default_Form_employeecomponents();
		$model = new Default_Model_Employeecomponents();
		$form->setAction(BASE_URL.'formulafields/addpopup');
		if($this->getRequest()->getPost()){
			if($form->isValid($this->_request->getPost())){
			$id = $this->_request->getParam('id');
            $componentId = $this->_request->getParam('ComponentId');	
			///$description = $this->_request->getParam('description');
			$actionflag = '';
			$tableid  = ''; 
			   $data = array('ComponentId'=>$componentId, 
							// 'description'=>$description,
							  'modifiedby'=>$loginUserId,
							  'modifieddate'=>gmdate("Y-m-d H:i:s")
					);
				if($id!=''){
					$where = array('id=?'=>$id);  
					$actionflag = 2;
				}
				else
				{
					$data['createdby'] = $loginUserId;
					$data['createddate'] = gmdate("Y-m-d H:i:s");
					$data['isactive'] = 1;
					$where = '';
					$actionflag = 1;
				}
				$Id = $model->SaveorUpdateEmpcomponentData($data, $where);
				$tableid = $Id;
				$menuID = EMPLOYEECOMPONENTS;
				$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);

				$empData = $model->getEmpcomponentData();
				$opt ='';
				foreach($empData as $record){
					$opt .= sapp_Global::selectOptionBuilder($record['id'], utf8_encode($record['service_desk_name']));
				}
				$this->view->departmentData = $opt;
					
				$this->view->eventact = 'added';
				$close = 'close';
				$this->view->popup=$close;
			}else
			{
				$messages = $form->getMessages();
				foreach ($messages as $key => $val)
				{
					foreach($val as $key2 => $val2)
					{
						$msgarray[$key] = $val2;
                                                break;
					}
				}
				$this->view->msgarray = $msgarray;
			}
		}
		$this->view->controllername = $controllername;
		$this->view->form = $form;
		$this->view->ermsg = '';
		$this->render('form');	
	}
	
	
	public function getrequestsAction()
    {
        $service_desk_id = $this->_getParam('service_desk_id',null);
        $service_desk_conf_id = $this->_getParam('service_desk_conf_id',null);
        $request_for_flag = $this->_getParam('request_for_flag',null);
        $data = array();
        $options = sapp_Global::selectOptionBuilder('', 'Select request', '');
        if($service_desk_id != '')
        {
        	if($request_for_flag!='') {
        		$request_for_param = explode('_',$service_desk_id);
        		if($request_for_param[1]==1) {
	        		$sd_dept_model = new Default_Model_Servicedeskdepartment();
		            $data = $sd_dept_model->getRequestsById($request_for_param[0]);
		            if(count($data) > 0)
		            {
		                foreach($data as $opt)
		                {                    
		                    $options .= sapp_Global::selectOptionBuilder($opt['id'], utf8_encode($opt['service_request_name']), '');
		                }
		            }
        			
        		}else{
        			$assetsModel = new Assets_Model_Assets();
        			$data = $assetsModel->getAllAssetForCategory($request_for_param[0]);
        			if(count($data) > 0)
        		    {
		                foreach($data as $opt)
		                {                    
		                    $options .= sapp_Global::selectOptionBuilder($opt['id'], utf8_encode($opt['name']), '');
		                }
		            }
        		}
        	}
        	else
        	{	
	            $sd_dept_model = new Default_Model_Servicedeskdepartment();
	            $data = $sd_dept_model->getRequestsById($service_desk_id);
	            if(count($data) > 0)
	            {
	                foreach($data as $opt)
	                {                    
	                    $options .= sapp_Global::selectOptionBuilder($opt['id'], utf8_encode($opt['service_request_name']), '');
	                }
	            }
        	}            
        }
        $attachment = false;
        if($service_desk_conf_id != '')
        {
        	
        	$sd_conf_model = new Default_Model_Servicedeskconf();
        	$sd_conf_data = $sd_conf_model->getServiceDeskConfbyID($service_desk_conf_id);
        	if(count($sd_conf_data)>0){
        		if($sd_conf_data[0]['attachment'] == 1)
        			$attachment = true;	
        	}
        }
        $this->_helper->json(array('options' => $options,'datacount'=>count($data),'attachment'=>$attachment));
    }// end of getrequests action
    
	
}

