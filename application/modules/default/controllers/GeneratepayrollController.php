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

class Default_GeneratepayrollController extends Zend_Controller_Action
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
        $generatepayrollmodel = new Default_Model_Generatepayroll();	
        $call = $this->_getParam('call');
        if($call == 'ajaxcall')
        {
            $this->_helper->layout->disableLayout();
        }
        $data = array();
        $dataAjax = array();
        $view = Zend_Layout::getMvcInstance()->getView();
        $objname = $this->_getParam('objname');
        $refresh = $this->_getParam('refresh');
        $method = $this->_getParam('method');
        $dashboardcall = $this->_getParam('dashboardcall');
        $data['businessunitslist'] = $generatepayrollmodel->getBusinessunitList();
        $data['payrollyearslist'] = $generatepayrollmodel->getPayrollyears();
        $this->view->dataArray = $data;
        $this->view->call = $call ;
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->render('generatepayroll/index', null, true);

		
    }
 
    
    public function getBusinessUnit()
    {
        
    }
    public function getDepartment($bunit)
    {
         $servicedeskdepartmentmodel = new Default_Model_Generatepayroll();	
         return $data['departmentlist'] = $servicedeskdepartmentmodel->getDepartment($bunit); 
       
    }

    public function payrollPeriods($bunit)
    {
        $servicedeskdepartmentmodel = new Default_Model_Generatepayroll();	
        $data = $servicedeskdepartmentmodel->payrollPeriods($bunit); 
        return $data;

    }

    public function BuildHeader($param) {
        $header1 = "";
        $header="<thead><tr><th style='text-align: center; vertical-align: middle;' rowSpan='2'>Id</th>";
        $header .="<th style='text-align: center; vertical-align: middle;' rowSpan='2'>Name</th>";
        $header .="<th style='text-align: center; vertical-align: middle;' rowSpan='2'>Days</th>";
         $components = new Default_Model_Generatepayroll();
         $cmplist=$components->getComponentBasedOnFilter(1);
         
         if($cmplist)
         { 
             $header .="<th style='text-align: center;' colSpan='".count($cmplist)."' >Earnings</th>";
             foreach ($cmplist as $value) {
                  $header1 .="<th style='text-align: center;' cmpId='".$value['id']."' >".$value['shortname']."</th>";
             } 
         }
         
         $cmplist=$components->getComponentBasedOnFilter(2);
         if($cmplist)
         { $header .="<th style='text-align: center;' colSpan='".count($cmplist)."' >Deductions</th>";
             foreach ($cmplist as $value) {
                  $header1 .="<th style='text-align: center;' cmpId='".$value['id']."' >".$value['shortname']."</th>";
             } 
         }
         $header .="</tr>";
         $header .="<tr>" .$header1. "</tr></thead>";
             
        return $header;
    }
    public function BuildBody()
    {
        $components = new Default_Model_Generatepayroll();
        		$view = Zend_Layout::getMvcInstance()->getView();		
		$objname = $this->_getParam('objname');
		$refresh = $this->_getParam('refresh');
		$dashboardcall = $this->_getParam('dashboardcall');
		
		$data = array();
		$searchQuery = '';
		$searchArray = array();
		$tablecontent='';
        $sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'DESC';
			$by = ($this->_getParam('by')!='')? $this->_getParam('by'):'e.modifieddate';
			if($dashboardcall == 'Yes')
				$perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
			else 
				$perPage = $this->_getParam('per_page',PERPAGE);
			$pageNo = $this->_getParam('page', 1);
			/** search from grid - START **/
			$searchData = $this->_getParam('searchData');	
			$searchData = rtrim($searchData,',');
        $dataTmp = $components->getGrid($sort, $by, $perPage, $pageNo, $searchData,$call,$dashboardcall);
        array_push($data,$dataTmp);
        $arrEmployee = array();
       foreach($data[0]["tablecontent"] as $row)
        {
           
            if($arrEmployee)
            {
              $isture = $this->searchForId($row["EmployeeId"],$arrEmployee);                
                if($isture > -1 )
                {
                $arrEmployee[$isture][$row["shortName"]] = $row["Amount"]; 
                }
                else
                {
                    $currentId = Count($arrEmployee) ;//+ 1;
                    //print_r($currentId) ; 
                    $arrEmployee[$currentId] = array();
                    $arrEmployee[$currentId]["id"] = $row["EmployeeId"];
                    $arrEmployee[$currentId]["EmpName"] = $row["userfullname"];
                    $arrEmployee[$currentId]["EmpId"] = $row["EmpCode"];
                    $arrEmployee[$currentId][$row["shortName"]] = $row["Amount"];
                    
                }
            }
            else
            {
                $arrEmployee[0] = array();
                $arrEmployee[0]["id"] = $row["EmployeeId"];
                $arrEmployee[0]["EmpName"] = $row["userfullname"];
                $arrEmployee[0]["EmpId"] = $row["EmpCode"];
                $arrEmployee[0][$row["shortName"]] = $row["Amount"];
            }
             
        }
        $body = "<tbody>";
        
        foreach ($arrEmployee as $value) {
           $body .='<tr id="'.$value["id"].'" style="height:55px" class="row1" onclick="selectrow(generatepayroll,this);">' ;
            $body .='<td style="text-align: center; vertical-align: middle;">'.$value["EmpId"].'</td>' ;
           $body .='<td style="text-align: center; vertical-align: middle;">'.$value["EmpName"].'</td>' ;
          
           $body .='<td id="pdays" style="text-align: center; vertical-align: middle;"><input name="presentdays" style="width: 75px;" id="pre_'.$row['EmployeeId'].'" value="" maxlength="20" type="text"></td>' ;
           $components = new Default_Model_Generatepayroll();
            $cmplist=$components->getComponentBasedOnFilter(1);
           if($cmplist)
         { 
             foreach ($cmplist as $comp) {
                 if(!$value[$comp['shortname']])
                 {
                     $body .="<td style='text-align: center; vertical-align: middle;'>0</td>";
                 }
                 else
                 {
                  $body .="<td style='text-align: center; vertical-align: middle;'>".$value[$comp['shortname']]."</td>";
                 }
                } 
         }
         
            $cmplist1=$components->getComponentBasedOnFilter(2);
           if($cmplist1)
         { 
             foreach ($cmplist1 as $comp) {
                 if(!$value[$comp['shortname']])
                 {
                     $body .="<td style='text-align: center; vertical-align: middle;'>0</td>";
                 }
                 else
                 {
                  $body .="<td style='text-align: center; vertical-align: middle;'>".$value[$comp['shortname']]."</td>";
                 }
                } 
         }
           $body .='</tr>' ;
        }
        
        $body .='</tbody>' ;
return $body;
  }
    function searchForId($id, $array) {
        foreach ($array as $key => $val) {
       
            if ($val['id'] === $id) {
                
           return $key;
       }
   }
   return -1;
}

function searchForValue($id,$array,$columnName)
{
            foreach ($array as $key => $val) {
       
            if ($val[$columnName] === $id) {
                
           return $key;
       }
   }
   return -1;
}
    public function tableGrid()
    {
        $div = '<div id="grid_generatepayroll" class="all-grid-control">'; $endDiv = '</div>';
        $table = '<div class="table-header"> <span>Generate Payroll</span>';
        $table .= '</div>';
        $table .= '<div class="slimScrollDiv" style="position: relative; overflow: hidden; width: 750px; height: auto;">';
        $table .= '<div id="generatepayroll" class="details_data_display_block newtablegrid" style="overflow: hidden; width: 250px; height: auto; background: rgb(204, 204, 204) none repeat scroll 0% 0%; padding-bottom: 10px;">';
        $table .=  '<table class="grid" id="payroll_table" cellspacing="0" cellpadding="4" border="0" align="center" width="100%">'.$this->BuildHeader(null);
        $table .= $this->BuildBody();
        $table .= "</table></div>";
       return $div.$table .$endDiv;
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
		$servicedeskdepartmentform = new Default_Form_servicedeskdepartment();
		
                
                
                $msgarray = array();
		$servicedeskdepartmentform->setAttrib('action',BASE_URL.'servicedeskdepartment/add');
		$this->view->form = $servicedeskdepartmentform; 
		$this->view->msgarray = $msgarray; 
		$this->view->ermsg = '';
			if($this->getRequest()->getPost()){
                           
                            // print_r($this->getRequest()->getPost()); exit;
				 $result = $this->save($servicedeskdepartmentform);	
				 $this->view->msgarray = $result; 
			}  		
		$this->render('form');	
	}
        public function GeneratePreview($data)
        {
            
            //print_r($data);exit;
             $components = new Default_Model_Generatepayroll();
             $response =  $components->responsegeneratepayroll($data);
             $month=$data["payrollmonth"];
            $department_id=$components->getpayrolldepatment($data["department"]);
            $department=$department_id[0]["deptname"];
            // print_r($response); 
             if($response != 'false')
             {
                $arrEmployee = array();
               
                foreach($response as $row)
                {
                    
                    if($arrEmployee)
                    {
                        
                        $isture = $this->searchForId($row["EmployeeId"],$arrEmployee);
                        
                        if($isture > -1 )
                        {
                            
                           $arrEmployee[$isture] = $this->AddEarningDeduction($arrEmployee[$isture],$row); 
                        }
                        else
                        {
                            
                            $currentId = Count($arrEmployee) ;
                            $arrEmployee[$currentId] = array();
                            $arrEmployee[$currentId]["id"] = $row["EmployeeId"];
                            $arrEmployee[$currentId]["EmpName"] = $row["userfullname"];
                            $arrEmployee[$currentId]["emailaddress"] = $row["emailaddress"];
                            $arrEmployee[$currentId]["EmpId"] = $row["EmpCode"];
                            $arrEmployee[$currentId]["Deductions"] = array();
                            $arrEmployee[$currentId]["Earnings"] = array();
                            $arrEmployee[$currentId] = $this->AddEarningDeduction($arrEmployee[$currentId],$row);
                        }
                    }
                    else
                    {
                        $arrEmployee[0] = array();
                        $arrEmployee[0]["id"] = $row["EmployeeId"];
                        $arrEmployee[0]["EmpName"] = $row["userfullname"];
                        $arrEmployee[0]["emailaddress"] = $row["emailaddress"];
                        $arrEmployee[0]["EmpId"] = $row["EmpCode"];
                        $arrEmployee[0][$row["shortName"]] = $row["Amount"];
                        $arrEmployee[0]["Deductions"] = array();
                        $arrEmployee[0]["Earnings"] = array();
                        
                        $arrEmployee[0] = $this->AddEarningDeduction($arrEmployee[0],$row);
                    }
                }
               // print_r($response);exit;
                
                foreach($response as $pdfdata) 
                {
                    $userfullname = $pdfdata["userfullname"];
                    $emailaddress = $pdfdata["emailaddress"];
                    $empcode = $pdfdata["EmpCode"];
                    $PeriodId = $pdfdata["PeriodId"];
                    $ammount=$pdfdata["Amount"];
                    $payrollshortname=$pdfdata["shortName"];
                    $data  =$reponse;
                    
                    require_once('application\modules\default\library\FPDF\html_table.php');

                    //create a FPDF object
                    $pdf=new PDF();
                    //set document properties
                    $pdf->SetAuthor('VHRIS');
                    $pdf->SetTitle('Payslip');
                    //set font for the entire document
                    $pdf->SetFont('Courier','B',12);
                
                //set up a page
                $pdf->AddPage('P');
                $pdf->SetDisplayMode(real,'default');
    //print_r('Add Page');
      //          insert an image and make it a link
                $orgInfoModel = new Default_Model_Organisationinfo();
                $data = array();
                $helper = new Zend_View_Helper_ServerUrl();
                $baseUrl = $helper->serverUrl(true);
                //$baseUrl = baseUrl.remp("/index.php");
                $orginfodata = $orgInfoModel->getOrganisationInfo();

                if(!empty($orginfodata))
                {
                    $data = $orginfodata[0];
                    $data['address1']=htmlentities($data['address1'],ENT_QUOTES, "UTF-8");
                    $url = BASE_URL.'../public/uploads/organisation/'.$data['org_image'];
                    $pdf->Image($url,10,20,33,0,'jpeg','');

                //display the title with a border around it
                $pdf->SetXY(80,20);
                $pdf->Cell(100,10,$data['organisationname'],0,0,'C',0);
                $pdf->SetXY(40,10);
                $pdf->SetFont('Courier','',8);
                $pdf->Cell(200,40,$data['address1'],0,1,'L',0);
                //$pdf->MultiCell(200,40,$data['address1'],0,'L');
                //$pdf->WordWrap($data['address1'],120);

                $pdf->SetFontSize(10);
                $html='<table border="1" width="80%" align="center"><tr><td width="780" height="30"><strong>';
                        $html .='                                     Payslip of '.$month.'-2016                        ';
                        $html .='</strong></td></tr><tr><td width="200" height="30"><strong>Employee Name:</strong>'.$userfullname.'</td>';
                        $html .='<td width="190" height="30"><strong>Employee Id</strong>'.$empcode.'</td></tr>';
                        $html .='<tr><td width="200" height="30"><strong>Month & Year:</strong>'.$month.'-2016</td><td width="190" height="30"><strong>Designation:</strong>'.$department.'</td></tr>';
                        $html .='<tr><td width="200" height="30"><strong>Department:</strong>'.$department.'</td><td width="190" height="30"><strong>Days In Month:</strong>31</td></tr>';
                        $html .='<tr><td width="200" height="30"><strong>Effective Working Days:</strong>31</td><td width="190" height="30"><strong>LOP:</b>0</td></tr>';
                        $html .='<tr><td width="200" height="30"><strong>Earnings</strong></td><td width="200" height="30"><strong>Actual</strong></td><td width="190" height="30"><strong>Deductions</strong></td><td width="190" height="30" align="right"><strong>Actual</strong></td></tr>';
                        
                        $html .='<tr>';

                        $icount=0;
                        $icountDeductions=0;
                       
                            if(count($arrEmployee[0]["Earnings"])!=0)
                            {
                                for($i=0;$i<count($arrEmployee[0]["Earnings"]);$i++)
                                {
                                $icount++;
                                }
                            }
                           
                           if(count($arrEmployee[0]["Deductions"])!=0) {
                               
                                for($i=0;$i<count($arrEmployee[0]["Deductions"]);$i++)
                                {
                                $icountDeductions++;
                                }
                            
                        }
                        
                        if(count($arrEmployee[0]["Earnings"])==$icount)
                        {
                            
                          
                            for ($index = 0; $index < $icount; $index++) {

                                $html.='<td width="200" height="30"><strong>'.$arrEmployee[0]["Earnings"][$index]["Name"].'</strong></td><td width="200" height="30">'.$arrEmployee[0]["Earnings"][$index]["Amount"].'</td>';
                                if($icountDeductions!=0)
                                {
                                   for ($indexded = 0; $indexded < $icountDeductions; $indexded++) {
                                   
                                $html.='<td width="190" height="30"><strong>'.$arrEmployee[0]["Deductions"][$indexded]["Name"].'</td><td width="190" height="30">'.$arrEmployee[0]["Deductions"][$indexded]["Amount"].'</td>';
                                    
                                    
                                   }

                            }
                              else {
                                        $html.='<td width="380" height="30"><strong>&nbsp;</strong></td>';
                                    }
                        }
                        }
 else {$html.='<td width="400" height="30"><strong>&nbsp;</strong></td><td width="380" height="30"><strong>&nbsp;</strong></td>';}


                      $html.="</tr>";   
                      //$html.='<tr><td width="200" height="30"><strong>Gross Salary</strong></td><td width="200" height="30">'.$arrEmployee[0]["GroSal"].'</td><td width="380" height="30"><strong>&nbsp;</strong></td></tr>';
                      $html.="</table>";   
                        


                $pdf->WriteHTML($html);
                $pdf->Write(30, '* This is Computer generated Slip does not require signature.');

                 $file_name = $empcode.'_'.$PeriodId.'_16'; 
                //Output the document

                 $payslipurl = BASE_URL.'../payslips/'.$file_name.'.pdf'; 
               $savepayslip =  $components->savepayslip($empcode,$PeriodId,$payslipurl);
               $pdf->Output('payslips/'.$file_name.'.pdf','F'); 
            }
          
           }

            }
          
        }
        
        
        public function AddEarningDeduction($empArray , $row)
        {
          
            if($row["Operator"] === "-" ||  $row["Operator"] === "2" )
            {
                if(!$empArray["Deductions"])
                {
                     $deductions = $empArray["Deductions"];
                     
                    $isture = $this->searchForValue($row["shortName"],$deductions,"Name");
                    if($isture > -1 )
                    {
                        $deductions[$isture]['Name'] = $row["shortName"];
                        $deductions[$isture]['Amount'] = $row["Amount"];
                    }
                    else
                    {
                        $currentId = Count($deductions) ;
                        $deductions[$currentId] = array();
                        $deductions[$currentId]['Name'] = $row["shortName"];
                        $deductions[$currentId]['Amount'] = $row["Amount"];
                    }
                }
                else
                {
                    
                    $deductions[0] = array();
                    $deductions[0]['Name'] = $row["shortName"];
                    $deductions[0]['Amount'] = $row["Amount"];
                    
                }
                $empArray["Deductions"] = $deductions;
            }
            else
            {
                if(!$empArray["Earnings"])
                {
                    $earnings = $empArray["Earnings"];
                 
                    $isture = $this->searchForId($row["shortName"],$earnings,"Name");
                    if($isture > -1 )
                    {
                        $earnings[$isture]['Name'] = $row["shortName"];
                        $earnings[$isture]['Amount'] = $row["Amount"];
                    }
                    else
                    {
                        $currentId = Count($earnings) ;
                        $earnings[$currentId]['Name'] = $row["shortName"];
                        $earnings[$currentId]['Amount'] = $row["Amount"];
                    }
                }
                else
                {
                    $earnings[0]['Name'] = $row["shortName"];
                    $earnings[0]['Amount'] = $row["Amount"];
                }
                 $empArray["Earnings"] = $earnings;
            }
            return $empArray;
        }

        public function viewAction()
	{	
               
		$id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		$objName = 'servicedeskdepartment';
		$servicedeskdepartmentform = new Default_Form_servicedeskdepartment();
		$servicedeskdepartmentform->removeElement("submit");
		$elements = $servicedeskdepartmentform->getElements();
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
					$servicedeskdepartmentmodel = new Default_Model_Servicedeskdepartment();	
					$data = $servicedeskdepartmentmodel->getServiceDeskDepartmentDatabyID($id);
					if(!empty($data))
					{
						$data = $data[0]; 
						$servicedeskdepartmentform->populate($data);
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
		$this->view->form = $servicedeskdepartmentform;
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
			$this->_helper->layout->disableLayout();
		
		$servicedeskdepartmentform = new Default_Form_servicedeskdepartment();
		$servicedeskdepartmentmodel = new Default_Model_Servicedeskdepartment();
		$servicedeskdepartmentform->submit->setLabel('Update');
		try
        {		
			if($id)
			{
			    if(is_numeric($id) && $id>0)
				{
					$data = $servicedeskdepartmentmodel->getServiceDeskDepartmentDatabyID($id);
					if(!empty($data))
					{
						  $data = $data[0];
						$servicedeskdepartmentform->populate($data);
						$servicedeskdepartmentform->setAttrib('action',BASE_URL.'servicedeskdepartment/edit/id/'.$id);
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
		$this->view->form = $servicedeskdepartmentform;
		if($this->getRequest()->getPost()){
      		$result = $this->save($servicedeskdepartmentform);	
		    $this->view->msgarray = $result; 
		}
		$this->render('form');	
	}
	
	public function save($servicedeskdepartmentform)
	{
	  $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
		} 
	    $servicedeskdepartmentmodel = new Default_Model_Servicedeskdepartment();
		$msgarray = array();
	    
		  if($servicedeskdepartmentform->isValid($this->_request->getPost())){
            try{
            $id = $this->_request->getParam('id');
            $service_desk_name = $this->_request->getParam('service_desk_name');	
			$description = $this->_request->getParam('description');
			$actionflag = '';
			$tableid  = ''; 
			   $data = array('service_desk_name'=>$service_desk_name, 
							 'description'=>($description!=''?trim($description):NULL),
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
				$Id = $servicedeskdepartmentmodel->SaveorUpdateServiceDeskDepartmentData($data, $where);
				if($Id == 'update')
				{
				   $tableid = $id;
				   $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Category updated successfully."));
				}   
				else
				{
				   $tableid = $Id; 	
					$this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Category added successfully."));					   
				}   
				$menuID = SERVICEDESKDEPARTMENT;
				$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);
				$this->_redirect('servicedeskdepartment');	
                  }
        catch(Exception $e)
          {
             $msgarray['service_desk_name'] = "Something went wrong, please try again.";
             return $msgarray;
          }
		}else
		{
			$messages = $servicedeskdepartmentform->getMessages();
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
            if($auth->hasIdentity())
            {
                $loginUserId = $auth->getStorage()->read()->id;
            }
            $id = $this->_request->getParam('objid');
            $messages['message'] = '';
            $messages['msgtype'] = '';
            $count = 0;
            $actionflag = 3;
            if($id)
            {
                $servicedeskdepartmentmodel = new Default_Model_Servicedeskdepartment();
                $servicedeskrequestmodel = new Default_Model_Servicedeskrequest();
                $servicedeskconfmodel = new Default_Model_Servicedeskconf();
                $pendingRequestdata = $servicedeskconfmodel->getServiceReqDeptCount($id,1);
                if(!empty($pendingRequestdata))
                    $count = $pendingRequestdata[0]['count'];
                $service_req_cnt = $servicedeskrequestmodel->getReqCnt($id);
                if($service_req_cnt > 0)
                    $count = $count + $service_req_cnt;
                if($count < 1)
                {	
                    $servicedeskdepartmentdata = $servicedeskdepartmentmodel->getServiceDeskDepartmentDatabyID($id);
                    $data = array('isactive'=>0,'modifieddate'=>gmdate("Y-m-d H:i:s"));
                    $where = array('id=?'=>$id);
                    $reqwhere = array('service_desk_id=?'=>$id);
                    $Id = $servicedeskdepartmentmodel->SaveorUpdateServiceDeskDepartmentData($data, $where);
                    $RId = $servicedeskrequestmodel->SaveorUpdateServiceDeskRequestData($data, $reqwhere);
                    if($Id == 'update')
                    {
						$menuID = SERVICEDESKDEPARTMENT;
                        $result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$id);
                        $configmail = sapp_Global::send_configuration_mail('Category',$servicedeskdepartmentdata[0]['service_desk_name']);				   
                        $messages['message'] = 'Category deleted successfully.';
                        $messages['msgtype'] = 'success';
                    }   
                    else
                    {
                        $messages['message'] = 'Category cannot be deleted.';
                        $messages['msgtype'] = 'error';
                    }
                }
                else
                {
                    $messages['message'] = 'Category cannot be deleted.';
                    $messages['msgtype'] = 'error';
                } 				   
            }
            else
            { 
                $messages['message'] = 'Category cannot be deleted.';
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
		$controllername = 'servicedeskdepartment';
		$servicedeskdepartmentform = new Default_Form_servicedeskdepartment();
		$servicedeskdepartmentmodel = new Default_Model_Servicedeskdepartment();
		$servicedeskdepartmentform->setAction(BASE_URL.'servicedeskdepartment/addpopup');
		if($this->getRequest()->getPost()){
			if($servicedeskdepartmentform->isValid($this->_request->getPost())){
			$id = $this->_request->getParam('id');
            $service_desk_name = $this->_request->getParam('service_desk_name');	
			$description = $this->_request->getParam('description');
			$actionflag = '';
			$tableid  = ''; 
			   $data = array('service_desk_name'=>$service_desk_name, 
							 'description'=>$description,
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
				$Id = $servicedeskdepartmentmodel->SaveorUpdateServiceDeskDepartmentData($data, $where);
				$tableid = $Id;
				$menuID = SERVICEDESKDEPARTMENT;
				$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);

				$servicedeskdepartmentData = $servicedeskdepartmentmodel->getSDDepartmentData();
				$opt ='';
				foreach($servicedeskdepartmentData as $record){
					$opt .= sapp_Global::selectOptionBuilder($record['id'], utf8_encode($record['service_desk_name']));
				}
				$this->view->departmentData = $opt;
					
				$this->view->eventact = 'added';
				$close = 'close';
				$this->view->popup=$close;
			}else
			{
				$messages = $servicedeskdepartmentform->getMessages();
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
		$this->view->form = $servicedeskdepartmentform;
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

