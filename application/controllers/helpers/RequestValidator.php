<?php
/**
 * 
 * Request validation action helper
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: RequestValidator.php,v 1.22 2013-03-11 13:55:37 developer Exp $
 */
class Zend_Controller_Action_Helper_RequestValidator extends Zend_Controller_Action_Helper_Abstract
{
	
	const ALIAS_REGEX='/^[\p{Common}\p{Cyrillic}\p{Latin}\d_-]+$/ui';
	const VIDEO_ALIAS_REGEX='/^[\p{Common}\p{Cyrillic}\p{Latin}\d_-]+$/ui';
	
	/**
	 * 
	 * Validate and filter
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
	public function isValidRequest($params=null, $options=null) {
		
		
		if (APPLICATION_ENV=='development'){
			//var_dump( $this->getRequest()->getParams() );
			//die(__FILE__.': '.__LINE__);
		}
		
		$filters = array( 
			'*'=>'StringTrim',
			'module'=>'StringToLower',
			'controller'=>'StringToLower', 
			'action'=>'StringToLower', 
			'format'=>array('StringToLower'),);
		$validators = array(
			'module'=>array(
				new Zend_Validate_Regex('/^[\p{Latin}]+$/'),
				'presence'=>'required'
			),
			'controller'=>array(
				new Zend_Validate_Regex('/^[\p{Latin}]+$/'),
				'presence'=>'required'
			),
			'action'=>array(
				new Zend_Validate_Regex('/^[\p{Latin}-]+$/'),
				'presence'=>'required'
			)
		);
		
		$profile = (bool)Zend_Registry::get('site_config')->site->get('profile');
		if (isset($_GET['RTVG_PROFILE'])){
			$validators['RTVG_PROFILE'] = array(new Zend_Validate_Regex( '/^(0|1)$/u' ));
		}
		if (isset($_GET['XDEBUG_PROFILE'])){
			$validators['XDEBUG_PROFILE'] = array(new Zend_Validate_Regex( '/^(0|1)$/u' ));
		}
		$module	 = $params['module'];
		$controller = $params['controller'];
		$action	 = $params['action'];
		
		switch ($module){
			/*
			 * default module
			 */
			case 'default':
			default:
				
				switch ($controller){
					
				    /*
				     * ##############################################
				     * Search frontend controller
				     * ##############################################
				     */
					case 'search':
						
						switch ($action) {
							
							case'search':
								$validators['searchinput'] = array( new Zend_Validate_Regex( '/^[\s\p{Cyrillic}\p{Latin}\p{Common}\d_-]+$/u' ));
								$validators['submit'] = array( new Zend_Validate_Regex( '/^>$/'));
								$validators['type'] = array( new Zend_Validate_Regex( '/^(channel)$/u' ));
								break;
								
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
						
					break;
						
						
					
					/*
					 * ##############################################
					 * Channels frontend controller
					 * ##############################################
					 */
					case 'channels':
					    
						switch ($action) {
						    
							case 'list': break;
							
							case 'typeahead':
								$validators['format'] = array( new Zend_Validate_Regex('/^html|json$/'));
								if ($this->getRequest()->getParam('c')) {
									$validators['c'] = array( new Zend_Validate_Regex('/^.+$/'));
								}
								
								if (!$this->getRequest()->isXmlHttpRequest()){
									throw new Zend_Exception( Rtvg_Message::ERR_WRONG_PARAM, 500 );
								}
								
							break;
								
							case 'category':
								$validators['category'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}-]+/ui' ));
							break;
								
							case 'channel-week':
								$validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
								/* $validators['week_start'] = array( 
									new Zend_Validate_Date( array(
										'format'=>'dd.MM.yyyy',
										'locale'=>'ru' ))
								); */
							break;
								
							case 'search':
								
							    $validators['id']	= array( new Zend_Validate_Regex( '/^[\w\d]+/u' ));
								$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
								$filters['alias']	= 'StringToLower';
								
							break;
								
							case 'new-comments':
								$validators['format']  = array( new Zend_Validate_Regex('/^html|json$/'));
								$validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
							break;
								
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
						
					break;
					
					/*
					 * ##############################################
					 * Listings frontend controller
					 * ##############################################
					 */
					case 'listings':
						
						$validators['channel'] = array(new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
						
						switch ($action) {
							
							case 'category':
								foreach ($options['vars']['programsCategories'] as $c){
									$cats[]=$c->alias;
								}
								$validators['category'] = array( new Zend_Validate_Regex( '/^('.implode('|', $cats).')$/u' ));
								$validators['timespan'] = array( new Zend_Validate_Regex( '/^(сегодня|неделя)$/u' ));
								
							break;
							
							case 'program-day':
								
								$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
								if ($this->getRequest()->getParam('date')) {
									$d = $this->getRequest()->getParam('date');
									if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d)) {
										$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-YYYY')), 'presence'=>'required');
									} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d)) {
										$validators['date'] = array( new Zend_Validate_Date( array('format'=>'YYYY-MM-dd')), 'presence'=>'required');
									} else{
										$validators['date'] = array( new Zend_Validate_Regex( '/^(сегодня|неделя)$/u' ));
									}
								}
								
							break;
								
							case 'program-week':
								$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
								if ($this->getRequest()->getParam('date')) {
									$d = $this->getRequest()->getParam('date');
									if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d)) {
										$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-YYYY')), 'presence'=>'required');
									} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d)) {
										$validators['date'] = array( new Zend_Validate_Date( array('format'=>'YYYY-MM-dd',)), 'presence'=>'required');
									} else {
										if ($d=='сегодня' || $d=='неделя') {
											$validators['date'] = array( new Zend_Validate_Alpha(false) );
										} else {
											throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
										}
									}
								}
								
							break;
								
							case 'day-date':
							case 'day-listing':
								
								//if ($this->getRequest()->getParam('date')) {
									$d = $this->getRequest()->getParam('date');
									if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d))
										$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-YYYY')),
											'presence'=>'required');
									if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d))
										$validators['date'] = array( new Zend_Validate_Date( array('format'=>'YYYY-MM-dd')),
											'presence'=>'required');

								//}
								
								if ( null !== ($ts = $this->getRequest()->getParam('ts', null))) {
								    $validators['ts'] = array( new Zend_Validate_Digits());
								}
									
								//$tz = $this->getRequest()->getParam('tz', null);
								//if ($tz && $tz!=0 && !empty($tz)) {
									$validators['tz'] = array( new Zend_Validate_Regex( '/^msk|-?[0-9]{1,2}$/' ));
								//}
								
							break;
								
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
						
					break;

					/*
					 * ##############################################
					 * Videos frontend controller
					 * ##############################################
					 */
					case 'videos':
					    
						$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
						$validators['id']	 = array( new Zend_Validate_Regex( '/^[a-z\d]+$/i' ));
						
					break;
					
					
					/*
					 * ##############################################
					 * Frontpage controller
					 * ##############################################
					 */
					case'frontpage':
							
						switch ($action){
							case 'single-channel':
								$validators['format'] = array( new Zend_Validate_Alpha());
								$validators['id']	 = array( new Zend_Validate_Digits());
							break;
									
							default: 
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
							
					break;

					
					/*
					 * ##############################################
					 * Users frontend controller
					 * ##############################################
					 */
					case 'user':
						
						switch ($action){
							
							case 'login':
								$validators['openid'] = array( new Zend_Validate_Regex( '/^[\w\d\._-]{2,128}@[\w\d\._-]{2,128}\.[\w]{2,4}$/ui' ));
								$validators['passwd'] = array( new Zend_Validate_Regex( '/^[\w\d]{6,32}$/ui' ));
								$validators['submit'] = array( new Zend_Validate_Alpha(false));
								
							break;

							case 'logout':
								$validators['submit'] = array( new Zend_Validate_Alpha(false));
								
							break;

							case 'profile':
								die(__FILE__.': '.__LINE__);
								
							break;
							
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
					break;

					/*
					 * ##############################################
					 * Wrong frontend controller
					 * ##############################################
					 */
					default: 
						throw new Zend_Exception( Rtvg_Message::ERR_WRONG_CONTROLLER, 404);
					
				}
				break;
			
			/*
			 * Administrator interface
			 */
			case 'admin':
				
				switch ($controller){
					
					/*
					 * ##############################################
					 * Backend archive controller
					 * ##############################################
					 */
					case 'archive':
					    
						switch ($action){
						    
							case 'store';
								$validators['start_date'] = array( new Zend_Validate_Date( array('format'=>'dd.MM.YYYY' )), 'presence'=>'required');
								$validators['end_date']   = array( new Zend_Validate_Date( array('format'=>'dd.MM.YYYY' )), 'presence'=>'required');
								$validators['format']	 = array( new Zend_Validate_Regex('/^html|json$/'));
								
							break;
								
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
						
					break;

					
					/*
					 * ##############################################
					 * Backend import controller
					 * ##############################################
					 */
					case 'import':
						
						switch ($action){
						    
							case 'remote':
								if ($this->getRequest()->getParam('site')) {
									$validators['site'] = array( new Zend_Validate_Alnum());
								}
								if ($this->getRequest()->getParam('format')) {
									$validators['format'] = array( new Zend_Validate_Regex('/^(html|json)$/'));
								}
								
							break;
								
							case 'xml-parse-channels':
							case 'xml-parse-programs':
								if ($this->getRequest()->getParam('xml_file')) {
									$validators['xml_file'] = array( new Zend_Validate_File_Exists( $this->getRequest()->getParam('xml_file')));
								}
							break;
								
							default:
								throw new Zend_Exception(self::ERR_WRONG_ACTION, 404);
								
						}
						
					break;
					
					/*
					 * ##############################################
					 * Backend programs controller
					 * ##############################################
					 */
					case 'programs':
						
						switch ($action) {
							
							case 'delete-programs':
								$validators['delete_start']   = array( new Zend_Validate_Regex( '/^[\d]{2}\.[\d]{2}\.[\d]{4}$/'));
								$validators['delete_end']	 = array( new Zend_Validate_Regex( '/^[\d]{2}\.[\d]{2}\.[\d]{4}$/'));
								$validators['deleteprograms'] = array( new Zend_Validate_Regex( '/^(0|1)$/'));
								$validators['deleteinfo']	 = array( new Zend_Validate_Regex( '/^(0|1)$/'));
								$validators['format']		 = array( new Zend_Validate_Regex( '/^(html|json)$/'));
								$validators['submit']		 = array( new Zend_Validate_Regex( '/^Старт$/u'));
								
							break;
							
							case 'processing':
								$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
								return $input;
								
							break;
								
							default: 
								throw new Zend_Exception(self::ERR_WRONG_ACTION, 404);
						}
						
					break;
					
					case 'auth':
					    
					    switch ($action){
					    	case 'login':
					    		$validators['openid'] = array( new Zend_Validate_Regex( Rtvg_Regex::EMAIL_REGEX ));
					    		$validators['passwd'] = array( new Zend_Validate_Regex( Rtvg_Regex::PASSWORD_REGEX ));
					    		$validators['submit'] = array( new Zend_Validate_Regex( '/\p{Cyrillic}+/u' ));
					    	break;
					    	
					    	default:
					    	case 'logout':
					    	    $validators['submit'] = array( new Zend_Validate_Regex( '/\p{Cyrillic}+/u' ));
					    	    break;
					    	    
					    }
					    
					    break;
					
				}
				
			break;
		}
				
		
		try {
			$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
		} catch (Zend_Filter_Exception $e) {
			throw new Zend_Exception($e->getMessage(), $e->getCode(), $e);
		}
		
		return $input;
		
	}
	
	/**
	 * Strategy pattern: call helper as broker method
	 */
	public function direct($params=array()) {
	
		if (strtolower($params[0])=='isvalidrequest') {
			if (isset($params['vars']) && !empty($params['vars']) && is_array($params['vars'])){
				$o = array();
				$p  = array();
				$p['module']	 = $params['vars']['module'];
				$p['controller'] = $params['vars']['controller'];
				$p['action']	 = $params['vars']['action'];
				unset($params['module']);
				unset($params['controller']);
				unset($params['action']);
				$options = $params;
				$params = $p;
				return $this->isValidRequest($params, $options);
			}
		}
		return false;
	}
	
}