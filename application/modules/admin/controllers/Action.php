<?php
class Xmltv_Controller_Action extends Zend_Controller_Action
{
	public function __call($method, $args) {
		if ('Action' == substr($method, -6)) {
			$controller = $this->getRequest()->getControllerName();
			$url = '/' . $controller . '/index';
			return $this->_redirect($url);
		}
		throw new Exception('Invalid method');
	}
}