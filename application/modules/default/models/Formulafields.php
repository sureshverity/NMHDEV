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

class Default_Model_Formulafields extends Zend_Db_Table_Abstract
{
    protected $_name = 'main_employees';
    protected $_primary = 'id';
	
	public function getServiceDeskDepartmentData($sort, $by, $pageNo, $perPage,$searchQuery)
	{
		$where = "e.isactive = 1";
		if($searchQuery)
			$where .= " AND ".$searchQuery;
		$db = Zend_Db_Table::getDefaultAdapter();		
		//$db = Zend_Db_Table::getDefaultAdapter();	
		$servicedeskDepartmentData = $this->select()
    					   ->setIntegrityCheck(false)	
                           ->from(array('e'=>'payroll_empcomformula'),array('e.id','e.ComponentId','e.Operator','e.FormulaType','e.DomainFormula'))
                          ->join(array('u'=>'payroll_components'), 'u.id=e.ComponentId',array('shortname' => 'u.shortname'))  
                          
                         
                        ->where($where)
    					   ->order("$by $sort") 
    					   ->limitPage($pageNo, $perPage);
		
      // echo $servicedeskDepartmentData;       exit;
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
			
		$objName = 'formulafields';
		
		$tableFields = array('action'=>'Action','shortname' =>'Component','Operator' =>'Operator','DomainFormula' =>'Formula');
		
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
	
	public function getFormulacomponents()
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_empcomformula'),array('sd.ComponentId'))
					    ->where('sd.isactive = 1');
					   
		return $this->fetchAll($select)->toArray();
	
	}
        
	
	
	
public function getServiceDeskDepartmentDatabyIDs($id)
	{
		
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_sd_depts'),array('sd.service_desk_name'))
					    ->where('sd.isactive = 1 AND sd.id IN ('.$id.') ');
					    
		return $this->fetchAll($select)->toArray();
	
	}
	
	
	
	
	public function getEmpcomponentData()
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_empcomponents'),array('sd.*'));
					   // ->where('sd.isactive = 1');
		return $this->fetchAll($select)->toArray();
	
	}
	
	public function SaveorUpdateEmpcomponentData($data, $where)
	{
	    if($where != ''){
			$this->update($data, $where);
			return 'update';
		} else {
			$this->insert($data);
			$id=$this->getAdapter()->lastInsertId('payroll_empcomponents');
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
        public function getFormulaID($id)
	{
           
            
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_empcomformula'),array('sd.*'))
                      ->from(array('e'=>'payroll_empcomformula'),array('e.id','e.ComponentId','e.Operator','e.FormulaType','e.DomainFormula'))
                          ->join(array('u'=>'payroll_components'), 'u.id=e.ComponentId',array('shortname' => 'u.shortname'))  
                          
					    ->where('sd.isactive = 1 AND sd.id='.$id.' ');
					   
		return $this->fetchAll($select)->toArray();
	
	}
        public function getFormulaDatabyID($id)
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_empcomformula'),array('sd.*'))
					    ->where('sd.isactive = 1  AND sd.id='.$id);
					   
		return $this->fetchAll($select)->toArray();
	
	}
        public function getmaxFormulaid()
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_empcomformula'),array('max(sd.FormulaId) as FormulaId'))
					    ->where('sd.isactive = 1 ');
					   
		return $this->fetchAll($select)->toArray();
	
	}
        public function SaveorUpdateEmployeecomponentData($data, $where)
	{
           
	    if($where != ''){
			$this->update($data, $where);
			return 'update';
		} else {
			$this->insert($data);
			$id=$this->getAdapter()->lastInsertId('main_users');
			return $id;
		}
		
	
	}
         public function saveEmployeeComponent($data)
        {
            
           $db = Zend_Db_Table::getDefaultAdapter();

                        foreach($data as $insert){
                        $db->insert('payroll_empcomponents', $insert);
                        }
			$id=$db->getAdapter()->lastInsertId('payroll_empcomponents');
                       return $id;
        }
        public function saveFormulaComponent($data)
        {
            $db = Zend_Db_Table::getDefaultAdapter();
            foreach($data as $insert){
               // print_r($insert);exit;
                        $db->insert('payroll_empcomformula', $insert);
                        }
                        
			$id=$db->getAdapter()->lastInsertId('payroll_empcomformula');
                        return $id;
        }

        public function deleteEmployeeComponent($empid)
        {
            
            $db = Zend_Db_Table::getDefaultAdapter();
            $delete = $db->delete('payroll_empcomponents', 'EmployeeId = '.$empid);
           
			return 'delete';
        }
         public function checkformulas($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		 $query = "select count(*) as count from payroll_empcomformula where id = ".$id." AND isactive = 1";
		
                $result = $db->query($query)->fetch();
	    return $result['count'];
	}
        public function SaveorUpdateformulaData($data, $where)
	{
           $db = Zend_Db_Table::getDefaultAdapter();
	    if($where != ''){
			 $db->update('payroll_empcomformula',$data, $where);
			return 'update';
		} else {
                     $query = "insert into payroll_empcomformula (ComponentId,FormulaType,Operator,IsOnDays,DomainFormula,CreatedBy,ModifiedBy,ModifiedDate,CreatedDate,isactive) values('$data[ComponentId]','$data[FormulaType]','$data[Operator]','$data[IsOnDays]','$data[DomainFormula]','$data[CreatedBy]','$data[ModifiedBy]','$data[ModifiedDate]','$data[CreatedDate]','$data[isactive]')";
		
                     $result = $db->query($query);
			//$this->insert($data);
			$id=$this->getAdapter()->lastInsertId('payroll_empcomformula');
			return $id;
		}
		
	}
}