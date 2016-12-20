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

class Default_Model_Payrolladvanceemi extends Zend_Db_Table_Abstract
{
	/**
	 * The advance table name
	 */
    protected $_name = 'payroll_advance_emi';
    protected $_primary = 'id';
	
	
	public function SaveAdvanceEmiData($data, $where)
	{
		if($where != ''){
			$this->update($data, $where);
			return 'update';
		} else {
			$this->insert($data);
			$id=$this->getAdapter()->lastInsertId('payroll_advance_emi');
			return $id;
		}
	}
	
	/**
	 * This method is used to fetch advance summary of employ  based on employ id.
	 * 
	 * @param number $id
	 */
        
	public function getAdvanceDetailsByEmployeeId($emp_id)
	{
		$select = $this->select()
						->setIntegrityCheck(false)
						->from(array('c'=>$this->_name),array('c.*'))						
						->where('c.emp_id='.$emp_id.' ');
						
		return $this->fetchAll($select)->toArray();
	}
        public function getPaidEmiByAdvanceId($adv_id)
	{
		$select = $this->select()
						->setIntegrityCheck(false)
						->from(array('c'=>$this->_name),array('c.*'))						
						->where('c.advance_id='.$adv_id.' and c.pay_status = "1" ');
						
		return $this->fetchAll($select)->toArray();
	}
        
        public function deleteEmiByAdvanceId($adv_Id)
        {
            $db = $this->getAdapter();
            $where = 'where advance_id  = '. $adv_Id;
            $query = "delete from payroll_advance_emi ". $where."";
            $res = $db->query($query);
            return $res;
        }
        public function getEmiByAdvanceId($adv_id)
        {
            $select = $this->select()
						->setIntegrityCheck(false)
						->from(array('c'=>$this->_name),array('c.*'))						
						->where('c.advance_id='.$adv_id.' ');
						
		return $this->fetchAll($select)->toArray();
        }
        
        
        public function SaveAdvanceEMIByDate($months ,$user_id, $emp_id , $advance_id , $amount , $loginUserId)
        {
            $monthNames = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
            
            if($amount != null && $amount > 0 && $months != null && $months > 0)
            {
                
                
                $currentDate = strtotime(gmdate('Y-m-d'));
               $emiDate = $currentDate;
               $actualMonth = $months;
               $perMonth = round($amount /$actualMonth);
               
               for ($i = 0; $i < $actualMonth; $i++) {
                   
                   $currentDate = strtotime("+1 month", $currentDate);
                   $emiDate = $currentDate;
                   $month=date("m",$emiDate);
                   $year=date("Y",$emiDate);
                   $currentData = array('advance_id'=>$advance_id,
                       'user_id'=>$user_id,
                       'emp_id'=>$emp_id,
                       'month' => $month,
                       'year'=> $year,
                       'amount'=> $perMonth,
                       'pay_status'=> '0',
                       'paid_date'=> null,
                       'createddate'=> $loginUserId,
                       'createdby'=> gmdate("Y-m-d H:i:s")
);           
                   $where = '';
                   $Lastid = $this->SaveAdvanceEmiData($currentData, $where);
               }
            }
        }
        
}