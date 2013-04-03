<?php
/**
 * Upload form for admin backend
 *
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: UploadForm.php,v 1.3 2013-04-03 04:08:15 developer Exp $
 *
 */
class Xmltv_Form_UploadForm extends Zend_Form
{
    public function __construct($options = null)
    {
		parent::__construct($options);
		
	  	// setting name, action and encryption type
		$this->setName('document');
		$this->setAction("");
		$this->setAttrib('enctype', 'multipart/form-data');
		
		 // creating object for Zend_Form_Element_File
		 $fileupload = new Zend_Form_Element_File('doc_path');
		 $fileupload->setLabel('Выбор файла:')
		 	->setRequired(true);

		 // creating object for submit button
		 $submit = new Zend_Form_Element_Submit('submit');
		 $submit->setLabel('Загрузить')
		 	->setAttrib('id', 'submitbutton');

		// adding elements to form Object
		$this->addElements( array($fileupload, $submit) );
		
    }
}