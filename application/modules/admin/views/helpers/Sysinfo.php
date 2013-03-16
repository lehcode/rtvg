<?php
/**
 * 
 * Helper to display notice/alert/error message
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package sosedionline
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/modules/admin/views/helpers/Sysinfo.php,v $
 * @version $Id: Sysinfo.php,v 1.1 2013-03-16 21:18:38 developer Exp $
 */
class Admin_View_Helper_Sysinfo extends Zend_View_Helper_Abstract
{
    /**
     * Show message on page
     * 
     * @param  string $msg
     * @param  string $type // 'message'|'warning'|'error'
     * @return string
     */
	public function sysinfo($type=null){
		
		$forbiddenRoles = array(
			Xmltv_Model_Acl::ROLE_GUEST,
			Xmltv_Model_Acl::ROLE_USER,
			Xmltv_Model_Acl::ROLE_PUBLISHER,
			Xmltv_Model_Acl::ROLE_EDITOR,
		);
		
		if (in_array($this->user->role, $forbiddenRoles)) {
			return;
		}
		ob_start();
		switch ($type){
			default:
			case 'short':
				?>
				<div class="row-fluid" id="sysinfo">
					<h6>
						APPLICATION_ENV: <?php echo APPLICATION_ENV ?><br />
						memory_limit: <?php echo ini_get('memory_limit') ?><br />
						error_reporting: <?php echo ini_get('error_reporting') ?><br />
						display_errors: <?php echo ini_get('display_errors') ?><br />
					</h6>
				</div>
				<?php 
				break;
		}
		return ob_get_clean();
	}
}