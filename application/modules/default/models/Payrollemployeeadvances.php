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

class Default_Model_Payrollemployeeadvances extends Zend_Db_Table_Abstract
{
    protected $_name = 'payroll_advance';
    protected $_primary = 'id';
	
	
	
	
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
				if($key=='userfullname')
				{
					$searchQuery .= " u.".$key." like '%".$val."%' AND ";
				}else
				{
					$searchQuery .= " ".$key." like '%".$val."%' AND ";
				}					
				
				$searchArray[$key] = $val;
			}
			$searchQuery = rtrim($searchQuery," AND");
		}
		
		$objName = 'payrollemployeeadvances';
		
		$tableFields = array(
					'action'=>'Action',
					'userfullname' => 'Employee',
					'total' => 'Amount',
                                        'joining' => 'Date of Joining',
                                        'emi' => 'Emi Months'
		);
               
		$tablecontent = $this->getEmployeeAdvancesData($sort, $by, $pageNo, $perPage,$searchQuery);
                
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
			'call'=>$call,
			'dashboardcall'=>$dashboardcall,
			'menuName' => 'Employee Advances'
			);
                
			return $dataTmp;
	}

	/**
	 * This will fetch all the employee advances details.
	 *
	 * @param string $sort
	 * @param string $by
	 * @param number $pageNo
	 * @param number $perPage
	 * @param string $searchQuery
	 *
	 */
	public function getEmployeeAdvancesData($sort, $by, $pageNo, $perPage,$searchQuery,$loginUserId='')
	{
	$db = Zend_Db_Table::getDefaultAdapter();
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
		 	$login_UserId = $auth->getStorage()->read()->id;
			$loginUserGroup = $auth->getStorage()->read()->group_id;
			$loginUserRole = $auth->getStorage()->read()->emprole;

		}
		if($loginUserId=='')
		{
			$loginUserId = $login_UserId;
		}	  
		//$loginUserId=4; 
		
		if($loginUserGroup == HR_GROUP  || $loginUserRole == SUPERADMINROLE )
			$where = "  c.isactive = 1 AND mes.user_id != ".$loginUserId." ";
		else	
			$where = "  c.isactive = 1 AND mes.reporting_manager = ".$loginUserId." ";
		
		
				//$where = "c.isactive = 1 AND mes.reporting_manager = ".$loginUserId." ";

		if($searchQuery)
		$where .= " AND ".$searchQuery;
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$employeeadvancesData = 
		  $this->select()
		->setIntegrityCheck(false)
		->from(array('c' => 'payroll_advance'),array('id'=>'c.id','total'=>'c.amount','employee_id'=>'c.to_id'))
	    ->joinInner(array('u'=>'main_users'), "u.id = c.to_id and u.isactive = 1",array('userfullname'=>'u.userfullname')) 
	    ->joinInner(array('mes'=>'main_employees_summary'), "mes.user_id = u.id and mes.isactive = 1 " , array('joining'=>'mes.date_of_joining'))
           ->joinInner(array('e'=>'payroll_emimonths'), "e.id = c.emi_months" ,array('emi'=>'e.emi_months') ) 
	   ->where($where)
		->order("$by $sort")
		->limitPage($pageNo, $perPage); 
                 
             //exit;
		return $employeeadvancesData;
	
	}
	
	public function saveOrUpdateAdvanceData($data, $where)
	{
		if($where != ''){
			$id = $this->update($data, $where);
			return 'update';
		} else {
			$this->insert($data);
                        $id=$this->getAdapter()->lastInsertId('payroll_advance');
			return $id;
		}
	} 
	
	public function getsingleEmployeeadvancesData($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$employeeadvance = $this->select()
		->setIntegrityCheck(false)
		->from(array('adv' => 'payroll_advance'))
		->where(' adv.id = '.$id.' and adv.isactive = 1'.$cond);
                $result = $this->fetchAll($employeeadvance)->toArray();
		return $result;
		
	}	
		
	public function getIndividualEmployeeadvancesData($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$where = "adv.isactive = 1";
		  $employeeadvance = $this->select()
		->setIntegrityCheck(false)
		->from(array('adv' => 'payroll_advance'))
		->joinInner(array('c'=>'main_currency'), "c.id = adv.currency_id",array('currencycode'=>'c.currencycode'))
		->joinInner(array('mc'=>'main_users'), "mc.id = adv.createdby",array('userfullname'=>'mc.userfullname'))
		->joinInner(array('mcu'=>'main_users'), "mcu.id = adv.to_id",array('to_name'=>'mcu.userfullname'))
                         ->joinInner(array('emiadv'=>'payroll_advance_emi'), "emiadv.advance_id = adv.id",array('Pay_status'=>'emiadv.pay_status','Paid_date'=>'emiadv.paid_date','emiammount'=>'emiadv.amount'))
		->where($where.' and adv.isactive = 1 and adv.type="advance" and adv.id = '.$id); 
		return $this->fetchAll($employeeadvance)->toArray();
	}	
	public function getEmpAdvance($id)
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$where = "ep.isactive = 1 and ep.id=".$id;
		$employeeadvance = $this->select()
		->setIntegrityCheck(false)
		->from(array('ep' => 'payroll_advacne'))
		->where($where); 
		return $this->fetchAll($employeeadvance)->toArray();
	}
        public function getEmiMonths()
	{
		
                $db = Zend_Db_Table::getDefaultAdapter();

                 $months = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('ep' => 'payroll_emimonths'));
                return $this->fetchAll($months)->toArray();
	}
        public function getEmiMonthsById($id)
	{
		
		$db = Zend_Db_Table::getDefaultAdapter();
		
		 $months = $this->select()
		->setIntegrityCheck(false)
		->from(array('ep' => 'payroll_emimonths'))
                ->where('ep.id = '.$id);
                 
		return $this->fetchAll($months)->toArray();
	}
	public function getEmpAdvanceReturn($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$where = "ep.isactive = 1 and type='return' and ep.from_id=".$id;
		$employeereturnadvance = $this->select()
		->setIntegrityCheck(false)
		->from(array('ep' => 'payroll_advance'))
		->joinInner(array('mc'=>'main_users'), "ep.to_id = mc.id",array('userfullname'=>'mc.userfullname'))
		->where($where);
		return $this->fetchAll($employeereturnadvance)->toArray();
	}
        
	
}
	