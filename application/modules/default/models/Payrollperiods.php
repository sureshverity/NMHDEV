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

class Default_Model_Payrollperiods extends Zend_Db_Table_Abstract
{
    protected $_name = 'payroll_pperiods';
    protected $_primary = 'id';
	
	public function getServiceDeskDepartmentData($sort, $by, $pageNo, $perPage,$searchQuery)
	{
		
                $where = "p.isactive = 1";
		if($searchQuery)
			$where .= " AND ".$searchQuery;
		$db = Zend_Db_Table::getDefaultAdapter();		
		
		$servicedeskDepartmentData = $this->select()
    			   ->setIntegrityCheck(false)	
                           ->from(array('p'=>'payroll_pperiods'),array('p.id','p.name','fromdate'=>'p.fromdate','todate'=>'p.todate'))
                           ->join(array('b'=>'main_businessunits'), 'p.buid=b.id',array('unitcode' => 'b.unitcode'))  
                         
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
			
		$objName = 'payrollperiods';
		
		$tableFields = array('action'=>'Action','name' => 'Name','fromdate' =>'From Date','todate' =>'To Date','unitcode' =>'Business Unit');
		
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
	
	public function getServiceDeskDepartmentDatabyID($id)
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_sd_depts'),array('sd.*'))
					    ->where('sd.isactive = 1 AND sd.id='.$id.' ');
					   
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
	
public function getServiceDeskDepartmentDatabyIDs($id)
	{
		
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'main_sd_depts'),array('sd.service_desk_name'))
					    ->where('sd.isactive = 1 AND sd.id IN ('.$id.') ');
					    
		return $this->fetchAll($select)->toArray();
	
	}
	
	public function checkpayrollperiods($id)
        {
         $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_pperiods'),array('sd.*'))
					    ->where('sd.isactive = 1 AND sd.id='.$id.' ');
					   
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
          public function getPayrollPeriodsbybuid()
        {
            $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_pperiods'),array('sd.*'))
					    ->where('sd.isactive = 1');
                                    //print_r($this->fetchAll($select)->toArray());exit;
		return $this->fetchAll($select)->toArray();

        }
	public function getPayrollPeriodsbyID($id)
	{
	    $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_pperiods'),array('sd.*'))
					    ->where('sd.isactive = 1 AND sd.id='.$id.' ');
					   
		return $this->fetchAll($select)->toArray();
	
	}
        public function getPayrollPeriodsbyIDs($id)
	{
    
	  echo  $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('sd'=>'payroll_pperiods'),array('sd.name'))
					    ->where('sd.isactive = 1 AND sd.id IN ('.$id.') ');
				exit;	    
		return $this->fetchAll($select)->toArray();
	
	}
		public function SaveorUpdateServiceComponentsData($data, $where)
	{
	    if($where != ''){
			$this->update($data, $where);
			return 'update';
		} else {
			echo $this->insert($data);
			$id=$this->getAdapter()->lastInsertId('payroll_pperiods');
                        
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
        
        public function getPayrollPeriodByYear($year , $bunit)
        {
            $options = array();

            if($year != '' && $bunit != '')
            {
                $db = Zend_Db_Table::getDefaultAdapter();
                $query = "select *  from payroll_pperiods where iYear = ".$year ." and buid =".$bunit;

                $result = $db->query($query);
                $options = $result->fetchAll();
            }

            return $options;
        }
		public function getPayrollDays($periodId)
        {
            $options = 0;
            if($periodId != '')
            {
                $db = Zend_Db_Table::getDefaultAdapter();
                $query = "SELECT (datediff(`todate` ,`fromdate` )+1) as Days FROM `payroll_pperiods` where id=".$periodId;
		$result = $db->query($query);
                $options = $result->fetch();
            }
            return $options;
        }
		
}