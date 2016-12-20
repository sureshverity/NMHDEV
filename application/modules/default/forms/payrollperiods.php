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

class Default_Form_payrollperiods extends Zend_Form
{
	public function init()
	{
		$this->setMethod('post');
		$this->setAttrib('id', 'formid');
		$this->setAttrib('name', 'payrollperiods');


        $id = new Zend_Form_Element_Hidden('id');
        $postid = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
		$name = new Zend_Form_Element_Text("name");
        $name->setLabel("Name");
        $fromdate = new Zend_Form_Element_Text("fromdate");
        $fromdate->setLabel("From Date");
        $todate = new Zend_Form_Element_Text("todate");
        $todate->setLabel("To Date");
        $buid = new Zend_Form_Element_Select("buid");
        $buid->setLabel("Business Unit");
         $submit = new Zend_Form_Element_Submit("submit");
	$submit->setAttrib("id", "submitbutton");
	$submit->setLabel("Save");
         $this->addElements(array($id, $name , $fromdate ,$todate ,$buid, $submit));
         $this->setElementDecorators(array('ViewHelper')); 

}
}