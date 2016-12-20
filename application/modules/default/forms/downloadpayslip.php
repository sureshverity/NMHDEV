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

class Default_Form_downloadpayslip extends Zend_Form
{
	public function init()
	{
		$this->setMethod('post');
		$this->setAttrib('id', 'formid');
		$this->setAttrib('name', 'downloadpayslip');

        $postid = Zend_Controller_Front::getInstance()->getRequest()->getParam('id');
        
        
        $fname = new Zend_Form_Element_Text("firstname");
        $fname->setLabel("First Name");
        
        $lastname = new Zend_Form_Element_Text("lastname");
        $lastname->setLabel("Last Name");
        
        $contact = new Zend_Form_Element_Text("contactnumber");
        $contact->setLabel("Contact No");
        
        
        $email = new Zend_Form_Element_Text("emailaddress");
        $email->setLabel("Email");
        
        $Componentid = new Zend_Form_Element_Checkbox("ComponentId");
        $Componentid->setLabel("ComponentId");
        
              
        $submit = new Zend_Form_Element_Submit("submit");
	$submit->setAttrib("id", "submitbutton");
	$submit->setLabel("Save");
        
        $this->addElements(array($id, $fname, $lastname, $contact, $email,$Componentid,$submit));
         $this->setElementDecorators(array('ViewHelper')); 
	}
}