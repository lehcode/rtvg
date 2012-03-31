<?php

/**
 * Content block core class
 * 
 * @author  Antony Repin
 * @package rutvgid
 * @version $Id: Block.php,v 1.1 2012-03-31 23:01:32 dev Exp $
 *
 */
class Xmltv_Block
{

	protected $css;
	protected $html;
	protected $js;


	public function __construct () {

	}


	/**
	 * @return string
	 */
	public function getCss () {

		return $this->css;
	}
	
	/**
	 * @return string
	 */
	public function getHtml () {

		return $this->html;
	}
	
	/**
	 * @return string
	 */
	public function getJs () {

		return $this->js;
	}
}