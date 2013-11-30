<?php
/**
 * 
 * Request validation action helper
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: RequestValidator.php,v 1.29 2013-04-11 05:21:11 developer Exp $
 */
class Zend_Controller_Action_Helper_RequestValidator extends Zend_Controller_Action_Helper_Abstract
{
	
	const VIDEO_ALIAS_REGEX = '/^[^&\/][\p{Common}\p{Cyrillic}\p{Latin}\d_-]+$/ui';
	const ALIAS_REGEX = '/^[\p{Cyrillic}\p{Latin}\d_-]+$/ui';
	const TITLE_REGEX = '/^[\p{Cyrillic}\p{Latin}\d\s\(\)\.\+"-]+$/ui';
	
	protected $regexMessages = array( 
		Zend_Validate_Regex::NOT_MATCH => "Название не указано или содержит неверные символы.",
	);
	
	/**
	 * 
	 * Validate and filter
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 */
	public function isValidRequest($params=null, $options=null) {
				
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
		
		$module	 = $params['module'];
		$controller = $params['controller'];
		$action	 = $params['action'];
        
        if (isset($_GET['XDEBUG_SESSION_START'])){
			$validators['XDEBUG_SESSION_START'] = array(new Zend_Validate_InArray( array('netbeans-xdebug')));
		}
		
		switch ($module){
			
            //default module
			case 'default':
			default:
				
				switch ($controller){
					
					//Search frontend controller
					case 'search':
						
						switch ($action) {
							
							case'search':
								$validators['searchinput'] = array( new Zend_Validate_Regex( '/^[\s\p{Cyrillic}\p{Latin}\p{Common}\d_-]+$/u' ));
								$validators['submit'] = array( new Zend_Validate_Regex( '/^>$/'));
								$validators['type'] = array( new Zend_Validate_Regex( '/^(channel)$/u' ));
								break;
								
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
						}
						
					break;
						
						
					
					//Channels frontend controller
					case 'channels':
						
						switch ($action) {
							
							case 'list': break;
							
							case 'typeahead':
								$validators['format'] = array( new Zend_Validate_Regex('/^html|json$/'));
								if ($this->getRequest()->getParam('c')) {
									$validators['c'] = array( new Zend_Validate_Regex('/^[\w\d-]+$/'));
								}
								
								if (!$this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV=='production'){
									throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404 );
								}
								
							break;
								
							case 'category':
								$validators['category'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}-]+/ui' ));
							break;
								
							case 'channel-week':
								$validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
							break;
							
                            case 'live':
								$validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
							break;
								
							case 'search':
								$validators['id'] = array( new Zend_Validate_Regex( '/^[\w\d]+/u' ));
								$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
								$filters['alias'] = 'StringToLower';
							break;

							case 'alias':
								$validators['t'] = array( new Zend_Validate_Regex( self::TITLE_REGEX ));
								$filters['t'] = 'StringToLower';
								$validators['format'] = array( new Zend_Validate_InArray( array('json', 'html') ) );
							break;
								
							case 'new-comments':
								$validators['format'] = array( new Zend_Validate_Regex('/^html|json$/'));
								$validators['channel'] = array( new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
							break;
								
							default:
							    throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
								
						}
						
					break;
					
					//Listings frontend controller
					case 'listings':
						
						$validators['channel'] = array(new Zend_Validate_Regex( '/^[\p{Cyrillic}\p{Latin}\d-]+$/u' ));
						
						switch ($action) {
							
							case 'category':
								foreach ($options['vars']['programsCategories'] as $c){
									$cats[]=$c->alias;
								}
								$validators['category'] = array( new Zend_Validate_Regex( '/^('.implode('|', $cats).')$/u' ));
								$validators['timespan'] = array( new Zend_Validate_InArray( array('сегодня','неделя' ) ));
								
							break;
							
							case 'broadcast-day':
								$validators['alias'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ));
								$validators['date']  = array( new Zend_Validate_Regex( '/^сегодня|неделя|[0-9]{2}-[0-9]{2}-[0-9]{4}$/u' ));
							break;
								
							case 'broadcast-week':
								$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
								$validators['date'] = array( new Zend_Validate_Alpha(false) );
							break;
								
							case 'day-date':
							    $d = $this->getRequest()->getParam('date');
							    if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d)) {
							    	$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-YYYY')));
							    } elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d)) {
							    	$validators['date'] = array( new Zend_Validate_Date( array('format'=>'YYYY-MM-dd')));
							    }
							    $validators['date']['presence'] = 'required';
							    
							    $validators['channel'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ) );
							    $validators['ts'] = new Zend_Validate_Digits();
							    
							break;
							
							case 'day-listing':
								
								$d = $this->getRequest()->getParam('date');
								if (preg_match('/^[\d]{2}-[\d]{2}-[\d]{4}$/', $d)) {
									$validators['date'] = array( new Zend_Validate_Date( array('format'=>'dd-MM-YYYY')));
								} elseif (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $d)) {
									$validators['date'] = array( new Zend_Validate_Date( array('format'=>'YYYY-MM-dd')));
								}
								$validators['date']['presence'] = 'optional';

								$validators['channel'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ) );
								
								$validators['ts'] = array( new Zend_Validate_Digits(),
									'presence'=>'optional'
								);
								
								$tz = array();
								for ($i = -12; $i<12; $i++){
								    $tz[] = $i;
								}
								$validators['tz'] = array( new Zend_Validate_InArray($tz) );
								
							break;
								
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
						}
						
					break;

					//Videos frontend controller
					case 'videos':
						
						$validators['alias'] = array( new Zend_Validate_Regex( self::VIDEO_ALIAS_REGEX ));
						$validators['id']	 = array( new Zend_Validate_Regex( '/^[a-z\d]+$/i' ));
						
					break;
					
					
					//Frontpage controller
					case'frontpage':
							
						switch ($action){
							case 'single-channel':
								$validators['format'] = array( new Zend_Validate_Alpha());
								$validators['id']	 = array( new Zend_Validate_Digits());
							break;
									
							default: 
								throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
						}
							
					break;

					
					//Users frontend controller
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
								throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
						}
					break;
					
					case 'content':
					    
					    switch ($action){
					    	
					    	case 'article':
					    	    $validators['category'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ) );
					    	    $validators['article_alias'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ) );
					    	break;
					        
					    	case 'article-tag':
					    	    $validators['tag'] = array( new Zend_Validate_Regex( self::ALIAS_REGEX ) );
					    	break;
					    	
					    	case 'blog':
					    	    $validators['channel'] = array( new Zend_Validate_Digits() );
					    	    break;
					    	
                            case 'index':
					    	    break;
					    	    
				    	    default:
				    	    	throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
					        
					    }
					break;
					
					case 'feed':
					    $validators['channel'] = array(
					    	new Zend_Validate_Int(),
					        'presence'=>'required');
						$validators['timespan'] = array( new Zend_Validate_InArray(array('неделя','сегодня')), 
							'presence'=>'optional' );
					    
					break;

					//Wrong frontend controller
					default: 
						throw new Zend_Exception( Rtvg_Message::ERR_NOT_FOUND, 404);
					
				}
				break;
			
			/* ########################################################################################
			 * Administrator interface
			 * ########################################################################################
			 */
			case 'admin':
				
				switch ($controller){
					
					//Backend archive controller
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

					
					//Backend import controller
					case 'import':
						
						switch ($action){
							
							case 'listings':
								$validators['site']   = array( new Zend_Validate_Alnum());
								$validators['format'] = array( new Zend_Validate_InArray( array( 'html', 'json' ) ));
								$validators['debug']  = array( new Zend_Validate_InArray( array( 0, 1 ) ));
								
							break;
								
							case 'xml-parse-channels':
							case 'xml-parse-programs':
								if ($this->getRequest()->getParam('xml_file')) {
									$validators['xml_file'] = array( new Zend_Validate_File_Exists( $this->getRequest()->getParam('xml_file')));
								}
							break;
							
							default:
							case 'upload':
								break;
                            
						}
						
					break;
					
					//Backend programs controller
					case 'programs':
						
						switch ($action) {
							
							case 'delete-programs':
								$validators['delete_start']   = array( new Zend_Validate_Regex( '/^[\d]{2}\.[\d]{2}\.[\d]{4}$/'));
								$validators['delete_end']	  = array( new Zend_Validate_Regex( '/^[\d]{2}\.[\d]{2}\.[\d]{4}$/'));
								$validators['deleteprograms'] = array( new Zend_Validate_Regex( '/^(0|1)$/'));
								$validators['deleteinfo']	  = array( new Zend_Validate_Regex( '/^(0|1)$/'));
								$validators['format']		  = array( new Zend_Validate_Regex( '/^(html|json)$/'));
								$validators['submit']		  = array( new Zend_Validate_Regex( '/^Старт$/u'));
								
							break;
							
							case 'processing':
								$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams());
								return $input;
								
							break;
								
							default: 
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
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
					
					case 'content':
						
						switch ($action){
							
							case 'edit':
							    
								if (isset($_REQUEST['idx']) && (int)$_REQUEST['idx']!==0) {
									if (is_array($_REQUEST['idx'])){
										$validators['idx'] = array( new Zend_Validate_Digits( array(
											'Zend_Controller_Action_Helper_RequestValidator', 
											'IdxArray')));
									} else {
										$validators['idx'] = array( new Zend_Validate_Digits() );
									}
									
								}
								
								$validators['do'] = array( 
									new Zend_Validate_InArray( array( 'haystack'=>array('delete','new','edit','toggle','save','save-plus','apply'))),
									'presence' => 'required',
									'allowEmpty' => true,
									'messages' => array(Zend_Filter_Input::PRESENCE_REQUIRED=>"Заполните поле '%field'")
								);
								
								$validators['title'] = array( 
									new Zend_Validate_Regex('/[\p{Common}\p{Cyrillic}\p{Latin}]+/ui'),
									'messages' => array( "Не указано название статьи." )
								);
								
								$validators['alias'] = array( 
									new Zend_Validate_Regex('/[^\s]+/ui'),
									'presence' => 'optional',
									'allowEmpty' => true
								);
								
								$validators['published'] = array( 
									new Zend_Validate_Digits(),
									new Zend_Validate_StringLength(1),
									'allowEmpty' => true
								);
								
								$validators['prog_cat'] = array( 
									new Zend_Validate_Digits(),
									new Zend_Validate_StringLength(1),
								);
								
								$validators['content_cat'] = array( 
									new Zend_Validate_Digits(),
									new Zend_Validate_StringLength(1),
									'presence' => 'optional',
									'allowEmpty' => true,
								);
								
								$validators['channel_cat'] = array( 
									new Zend_Validate_Digits(),
									new Zend_Validate_StringLength(1),
								);
								
								$validators['tags'] = array( 
									new Zend_Validate_Regex( '/[\p{Common}\p{Cyrillic}\p{Latin}]+/ui' ),
									new Zend_Validate_StringLength( array(3,254) ),
									'presence' => 'optional',
									'allowEmpty' => true,
								);
								
								$validators['intro'] = array( 
									new Zend_Validate_Regex( '/[\p{Common}\p{Cyrillic}\p{Latin}]+/ui' ),
								);
								
								$validators['body'] = array( 
									new Zend_Validate_Regex('/[\p{Common}\p{Cyrillic}\p{Latin}]+/ui'),
								);
								
								$validators['author'] = array( 
									new Zend_Validate_Digits(),
									new Zend_Validate_StringLength(array(1,11)),
								);
								
								$validators['added'] = array(
									new Zend_Validate_Date( 'dd.MM.YYYY' ),
								);
								
								$validators['publish_up'] = array( 
									new Zend_Validate_Date( 'dd.MM.YYYY' ),
								);
								$validators['publish_down'] = array( 
									new Zend_Validate_Date( 'dd.MM.YYYY' ), 
									'presence' => 'optional',
									'allowEmpty' => true,
								);
								$validators['income'] = array( 
									new Zend_Validate_InArray( array('is_cpa', 'is_ref', 'is_paid') ) );
								
								$validators['hits'] = array( 
									new Zend_Validate_Digits(), 
									'allowEmpty' => true );
								
								$validators['metadesc'] = array( 
									new Zend_Validate_NotEmpty(),
									new Zend_Validate_StringLength( array(24,254) ),
									'allowEmpty'=>true );
								
							break;
							
							case 'index': break;
							case 'articles': break;
							
							default:
								throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION, 404);
						}
					break;
					
					case 'system':
						default: break;
					break;
					
					case 'grab':
					    switch ($action){
					    	case 'do':
					    	    $validators['site'] = array( 
					    	    	new Zend_Validate_Alpha(),
					    	    	new Zend_Validate_StringLength(array(4,255))
					    	    );
					    	break;
					    	default:
					    		throw new Zend_Exception( Rtvg_Message::ERR_WRONG_ACTION.': '.$action, 404);
					    }
					break;
					
					default: break;
				}
			break;
			
			default:
				throw new Zend_Exception( Rtvg_Message::ERR_WRONG_MODULE, 404);
			break;
		}
				
		
		$input = new Zend_Filter_Input($filters, $validators, $this->getRequest()->getParams(), array(
			'notEmptyMessage' => "Поле <b>'%field%'</b> должно быть заполнено."
		));
        
        return $input;
		
	}
	
	public static function IdxArray($value=null){
		
		$filter = new Zend_Filter_Digits();
		$r = $filter->filter($value);
		if (!is_int($r)){
			return false;
		}
		return true;
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