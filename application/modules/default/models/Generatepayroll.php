<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2014 Sapplica
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

class Default_Model_Generatepayroll extends Zend_Db_Table_Abstract
{
    protected $_name = 'main_employees';
    protected $_primary = 'id';
	
	public function getServiceDeskDepartmentData($spparams)
	{
//		$where = "e.isactive = 1";
//		if($searchQuery)
//			$where .= " AND ".$searchQuery;
          
		$db = Zend_Db_Table::getDefaultAdapter();
                
                $stored_proc_stmt = "call  SP_GENERATEPAYROLL( $spparams[payrollperiod],-1,$spparams[department])";
                //echo $stored_proc_stmt;exit;
                $stmt = $db->prepare($stored_proc_stmt);                
                $stmt->execute();
                $results = $stmt->fetchAll();
                $data = array();
                foreach($results as $r)
                {
                    $data[] = $r;
                }
             
           
 
		return  $data;       		
	}
	
	public function getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$dashboardcall,$a='',$b='',$c='',$d='')
	{		
        $searchQuery = '';
        $searchArray = array();
        $data = array();
       // print_r($dashboardcall);exit;
		if($searchData != '' && $searchData!='undefined')
			{
				$searchValues = json_decode($searchData);
				foreach($searchValues as $key => $val)
				{
							$searchQuery .= " ".$key." like '%".$val."%' AND ";
                           $searchArray[$key] = $val;
				}
				$searchQuery = rtrim($searchQuery," AND");					
			}
			
		$objName = 'generatepayroll';
		
		$tableFields = array('action'=>'Action','EmpCode' => 'Employee ID','userfullname' => 'User Name','emailaddress' =>'Email','date_of_joining' =>'Date Of Joining');
		
		$tablecontent = $this->getServiceDeskDepartmentData($sort, $by, $pageNo, $perPage,$searchQuery);     
		
		$dataTmp = array(
			'sort' => $sort,
			'by' => $by,
			'pageNo' => $pageNo,
			'perPage' => $perPage,				
			'tablecontent' => $tablecontent,
			'objectname' => $objName,
			'extra' => array(),
			'tableheader' => $tableFields,
			'jsGridFnName' => 'getAjaxgridData',
			'jsFillFnName' => '',
			'searchArray' => $searchArray,
			'add' =>'add',
			'call'=>$call,
			'dashboardcall'=>$dashboardcall,
		);
                
		return $dataTmp;
	}
        public function getBusinessunitList()
        {
           
             $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_businessunits'),array('sd.id','sd.unitname','sd.unitcode'))
					    ->where('sd.isactive = 1')
                    ->order('sd.unitcode'); 
		return $this->fetchAll($select)->toArray(); 
        }
        public function getDepartment($bunit)
        {   
           // print_r($bunit); //exit;
                       $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_departments'),array('sd.id','sd.deptname','sd.deptcode'))
					    ->where('sd.isactive = 1 AND sd.unitid='.$bunit);
                    
		return $this->fetchAll($select)->toArray(); 
        }
//        public function getDepartment($bunit)
//        {   
//              $select = $this->select()
//						->setIntegrityCheck(false)
//						->from(array('sd'=>'main_departments'),array('sd.id','sd.deptname','sd.deptcode'))
//					    ->where('sd.isactive = 1 AND sd.unitid='.$bunit);
//		return $this->fetchAll($select)->toArray(); 
//        }
        
    public function getpayrolldepatment($id) {
          $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_departments'),array('sd.id','sd.deptname','sd.deptcode'))
					    ->where('sd.isactive = 1 AND sd.id='.$id);
                    
		return $this->fetchAll($select)->toArray(); 
        
    }
    public function responsegeneratepayroll($data)
    {
        
               $businessunit = $data['bunit'];
               $department = $data['department'];
               $payrollperiod = $data['payrollperiod'];
                $db = Zend_Db_Table::getDefaultAdapter();
                $stored_proc_stmt = "call SP_UPDATEPAYROLL(".$data['bunit'].",".$data['department'].",".$data['payrollperiod'].",'".$data['data']."')";
             
                $stmt = $db->prepare($stored_proc_stmt);                
            
                $stmt->execute();
                $results = $stmt->fetchAll();
                
                $data = array();
                foreach($results as $r)
                {
                    $data[] = $r;
                }
               if($data[0][1] == '1')
               {
                    $stored_proc_stmt = "call SP_GETPAYROLLDETAILS(".$payrollperiod.",".$businessunit.",".$department.",'-1')";
               
                   $stmt = $db->prepare($stored_proc_stmt);                
            
                $stmt->execute();
                $results = $stmt->fetchAll();
                
                $data1 = array();
                
                foreach($results as $r)
                {
                    $data1[] = $r;
                }
                
                return $data1;
               }
               else {
                   return false;
               }
               
    }
    public function payrollPeriods($id)
        {
             $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_pperiods'),array('sd.id','sd.name','sd.fromdate','sd.todate'))
					    ->where('sd.isactive = 1 AND sd.buid='.$id);
		
		return $this->fetchAll($select)->toArray();
        }

        public function getServiceDeskDepartmentDatabyID($id)
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_sd_depts'),array('sd.*'))
					    ->where('sd.isactive = 1 AND sd.id='.$id.' ');
					   
		return $this->fetchAll($select)->toArray();
	
	}
	
	public function savepayslip($empcode,$PeriodId,$payslipurl)
        {
            $where = '';
            $db = Zend_Db_Table::getDefaultAdapter();
            $isExist = $this->checkpayslip($empcode, $PeriodId);
            
            if(Count($isExist) > 0)
            {
                 $data = array('empid'=>"$empcode",
                            'month' =>"$PeriodId",
                             'year' =>2016,
                            'payrollurl' =>"$payslipurl");
               $id = $isExist[0]['id'];
               $where = 'id='.$id; 
               $db -> update('payroll_payslip',$data, $where);
               return true;
            }
            else
            {
             $data = array('empid'=>"$empcode",
                            'month' =>"$PeriodId",
                             'year' =>2016,
                            'payrollurl' =>"$payslipurl",
                            'createdby' => 1);
                      
           $result =  $db->insert('payroll_payslip', $data);
            }
           if($result)
           {
               return true;
           }else {
               return false;
           }
        }
        
        
        public function checkpayslip($empcode,$PeriodId)
        {
            $result = array();
        $db = Zend_Db_Table::getDefaultAdapter();
        $query = "select * from payroll_payslip where empid = '".$empcode."' and month = $PeriodId";
        $result = $db->query($query)->fetchAll();
        return $result;
        }

                public function getServiceDeskDepartmentDatabyIDs($id)
	{
		
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_sd_depts'),array('sd.service_desk_name'))
					    ->where('sd.isactive = 1 AND sd.id IN ('.$id.') ');
					    
		return $this->fetchAll($select)->toArray();
	
	}
	
	
	
	
	public function getSDDepartmentData()
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_sd_depts'),array('sd.*'))
					    ->where('sd.isactive = 1');
		return $this->fetchAll($select)->toArray();
	
	}
        public function getComponentBasedOnFilter($param)
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_components'),array('sd.shortname','sd.id','sd.name'))
					    ->where('sd.groupid = '.$param);
		return $this->fetchAll($select)->toArray();
	
	}
	
	public function SaveorUpdateServiceDeskDepartmentData($data, $where)
	{
	    if($where != ''){
			$this->update($data, $where);
			return 'update';
		} else {
			$this->insert($data);
			$id=$this->getAdapter()->lastInsertId('main_sd_depts');
			return $id;
		}
		
	
	}
    
    /**
     * This function gives request types based on service_desk id.
     * @param integer $service_desk_id  = id of service desk.
     * @return array Array of service request types and their ids.
     */
    public function getRequestsById($service_desk_id)
    {
        $options = array();
        if($service_desk_id != '')
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $query = "select id,service_request_name from main_sd_reqtypes "
                    . "where isactive = 1 and service_desk_id = ".$service_desk_id." order by service_request_name asc";
            $result = $db->query($query);
            $options = $result->fetchAll();
        }
        return $options;
    }
    
	public function checkDuplicateCategoryName($categoryName)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$qry = "select count(*) as count from main_sd_depts sd where sd.service_desk_name='".$categoryName."' AND sd.isactive=1 ";
		$res = $db->query($qry)->fetchAll();
		return $res;
	}
        
    public function getPayrollyears()
    {
         $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('y'=>'payroll_year'),array('y.*'));
					   // ->where('sd.isactive = 1 AND sd.id='.$id);
                  
		return $this->fetchAll($select)->toArray(); 
    }
}