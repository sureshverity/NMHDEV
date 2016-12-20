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

class Default_Model_Components extends Zend_Db_Table_Abstract
{
    protected $_name = 'payroll_components';
    protected $_primary = 'id';
	
	public function getServiceDeskDepartmentData($sort, $by, $pageNo, $perPage,$searchQuery)
	{
		$where = "p.isactive = 1 and p.isvisible = 0";
		if($searchQuery)
			$where .= " AND ".$searchQuery;
		$db = Zend_Db_Table::getDefaultAdapter();		
		
		 $servicedeskDepartmentData = $this->select()
    					   ->setIntegrityCheck(false)	
                           ->from(array('p'=>'payroll_components'),array('p.id','p.shortname','p.name','p.sequenceno','createddate'=>'DATE_FORMAT(p.createddate,"'.DATEFORMAT_MYSQL.'")'))
                          ->join(array('b'=>'main_businessunits'), 'p.buid=b.id',array('unitcode' => 'b.unitcode'))  
                       
                        ->join(array('g'=>'payroll_comgroup'), 'p.groupid=g.id',array('groupname' => 'g.name'))  
                        ->order('g.name')
                        ->where($where)
    					   ->order("$by $sort") 
                                           
    					   ->limitPage($pageNo, $perPage);
		return $servicedeskDepartmentData;       		
	}
	
	public function getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$dashboardcall,$a='',$b='',$c='',$d='')
	{		
        $searchQuery = '';
        $searchArray = array();
        $data = array();
		
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
			
		$objName = 'components';
		
		$tableFields = array('action'=>'Action','shortname' => 'Short Name','name' => 'Name','sequenceno' =>'Sequence No','groupname' =>'Group Name','createddate' =>'Created Date','unitcode' =>'Business Unit');
		
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
	
	public function getComponentDatabyID($id)
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_components'),array('sd.*'))
					    ->where('sd.isactive = 1 AND sd.id='.$id.' ');
					   
		return $this->fetchAll($select)->toArray();
	
	}
	
	
	
public function getComponentsDatabyIDs($id)
	{
    
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_components'),array('sd.name'))
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
	
	public function SaveorUpdateServiceComponentsData($data, $where)
	{
              $db = $this->getAdapter();
	    if($where != ''){
            
                if($data["shortname"] !=''){
                echo $query = 'UPDATE `payroll_components` SET `name`="'.$data["name"].'", `shortname`="'.$data["shortname"].'",`sequenceno`='.$data["sequenceno"].',`ispayslipcmp`='.$data["ispayslipcmp"].',`isempfixedcmp`='.$data["isempfixedcmp"].',`groupid`='.$data["groupid"].',`isotcomponent`='.$data["isotcomponent"].',`ishotcomponent`=0,`buid`='.$data["buid"].',`modifiedby`='.$data["modifiedby"].',`modifieddate`='.$data["modifieddate"].',`istdsexemptedcmp`=0 WHERE '.$where ; 
                }
                else{
                       $query = 'UPDATE `payroll_components` SET `isactive`='.$data["isactive"].' WHERE '.$where ;
             
                }
              $res = $db->query($query);
                    return 'update';
		} else {
                  
                     $query = 'insert into `payroll_components` (name,shortname,sequenceno,ispayslipcmp,isempfixedcmp,groupid,isotcomponent,buid,modifiedby,modifieddate,isactive) values ("'.$data["name"].'","'.$data["shortname"].'",'.$data["sequenceno"].','.$data["ispayslipcmp"].','.$data["isempfixedcmp"].','.$data["groupid"].','.$data["isotcomponent"].','.$data["buid"].','.$data["modifiedby"].','.$data["modifieddate"].','.$data["isactive"].')';
                   $res = $db->query($query);
			$id=$this->getAdapter()->lastInsertId('payroll_components');
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
        public function getComponentgroupsbybuid()
        {
            $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_comgroup'),array('sd.*'))
					    ->where('sd.isactive = 1');
            return $this->fetchAll($select)->toArray();

        }
         public function getComponentsforformulafield()
        {
            $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_components'),array('sd.id','sd.shortname'))
					    ->where('sd.isactive = 1 and sd.isempfixedcmp = 0 ');
            return $this->fetchAll($select)->toArray();

        }
        public function getReqCnt($id)
        {
            $cnt = 0;
            if($category_id != '')
            {
                $data = $this->fetchAll("isactive = 1 and id = ".$id);
                $cnt = $data->count();
            }
            return $cnt;
        }
        
        public function getcomponentsCount($id,$flag='')
	{
		$where = 'sr.isactive=1'; 
		if($flag !='')
		{
			if($flag==1)
				$where.= ' AND sr.id ='.$id.' ';
			
		}	
			
	   	$db = Zend_Db_Table::getDefaultAdapter();
		$qry = "select count(*) as count from payroll_components sr where ".$where." ";
		$res = $db->query($qry)->fetchAll();	
		return $res;	
		
	}
        public function checkcomponents($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		 $query = "select count(*) as count from payroll_components where id = ".$id." AND isactive = 1";
		$result = $db->query($query)->fetch();
	    return $result['count'];
	}
        
         public function getEmployeeComponent($emp_id)
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $query = "select * from payroll_components where isempfixedcmp=1 and isactive=1 and isvisible = 0";
            $payrollComp = $db -> fetchAll($query);
            $query1 = "SELECT * FROM payroll_empcomponents where EmployeeId = ".$emp_id;
            $employeeComp = $db -> fetchAll($query1);
            $objArray = array();
            $iIndex = 0;
            foreach ($payrollComp as $value) {
               $result = $this->checkInArray($value['id'],'ComponentId', $employeeComp);
               if($result)
               {
                   $objArray[$iIndex] =  array();
                   $cuurentArray = array();
                   $cuurentArray['id'] = $value['id'];
                   $cuurentArray['checked'] = true;
                   $cuurentArray['name'] = $value['name'];
                   $cuurentArray['shortname'] = $value['shortname'];
                   $cuurentArray['Operator'] = $result['Operator'];
                   $cuurentArray['IsOnDays'] = $result['IsOnDays'];
                   $cuurentArray['FixedAmount'] = $result['FixedAmount'];
                   $objArray[$iIndex] = $cuurentArray;
               }
               else
               {
                   
                   $objArray[$iIndex] =  array();
                   $cuurentArray = array();
                   $cuurentArray['id'] = $value['id'];
                   $cuurentArray['checked'] = false;
                   $cuurentArray['name'] = $value['name'];
                   $cuurentArray['shortname'] = $value['shortname'];
                   $cuurentArray['Operator'] = null;
                   $cuurentArray['IsOnDays'] = false;
                   $cuurentArray['FixedAmount'] = 0;
                   $objArray[$iIndex] = $cuurentArray;
                   
               }
              $iIndex = $iIndex + 1;
            }
            
           return $objArray;
        }
        public function getFormulaComponent($emp_id)
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            $query = "select * from payroll_components where isempfixedcmp=0 and isactive=1";
            $payrollComp = $db -> fetchAll($query);
            $query1 = "SELECT * FROM payroll_empcomponents where EmployeeId = ".$emp_id;
            $employeeComp = $db -> fetchAll($query1);
            $objArray = array();
            $iIndex = 0;
            foreach ($payrollComp as $value) {
               $result = $this->checkInArray($value['id'],'ComponentId', $employeeComp);
               if($result)
               {
                   $objArray[$iIndex] =  array();
                   $cuurentArray = array();
                   $cuurentArray['id'] = $value['id'];
                   $cuurentArray['checked'] = true;
                   $cuurentArray['name'] = $value['name'];
                   $cuurentArray['shortname'] = $value['shortname'];
                   $cuurentArray['Operator'] = $result['Operator'];
                   $cuurentArray['IsOnDays'] = $result['IsOnDays'];
                   $cuurentArray['FixedAmount'] = $result['FixedAmount'];
                   $objArray[$iIndex] = $cuurentArray;
               }
               else
               {
                   
                   $objArray[$iIndex] =  array();
                   $cuurentArray = array();
                   $cuurentArray['id'] = $value['id'];
                   $cuurentArray['checked'] = false;
                   $cuurentArray['name'] = $value['name'];
                   $cuurentArray['shortname'] = $value['shortname'];
                   $cuurentArray['Operator'] = null;
                   $cuurentArray['IsOnDays'] = false;
                   $cuurentArray['FixedAmount'] = 0;
                   $objArray[$iIndex] = $cuurentArray;
                   
               }
              $iIndex = $iIndex + 1;
            }
            
           return $objArray;
        }
        
        public function getComponentByName($compCode)
        {
            $db = Zend_Db_Table::getDefaultAdapter();
		 $query = "select * from payroll_components where shortname = '".$compCode."' AND isactive = 1";

		$result = $db->query($query)->fetch();
                
                return $result;
        }
        public function checkInArray($searchValue , $columnName , $array )
        {
            $returnValue = null;
            
        for ($index = 0;$index < count($array);$index++) {
            $value = $array[$index];
            
            
            if($value[$columnName]== $searchValue)
            {
                
                $returnValue = array();
                $returnValue = $value;
                //return $value;
                break;

            }
                
        }
        //print_r($returnValue);
        return $returnValue;
        }
}