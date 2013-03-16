<?php
/**
 * 
 * Helper to display notice/alert/error message
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package sosedionline
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/modules/admin/views/helpers/Attic/ShowMessages.php,v $
 * @version $Id: ShowMessages.php,v 1.1 2013-03-16 21:18:38 developer Exp $
 */
class Admin_View_Helper_ShowMessage extends Zend_View_Helper_Abstract
{
    /**
     * Show message on page
     * 
     * @param  string $msg
     * @param  string $type // 'message'|'warning'|'error'
     * @return string
     */
	public function showMessage($msg=null, $type='message'){
		
		switch ($type) {
			default:
			case 'message':
				$heading = 'Сообщение';
				break;
			case 'warning':
				$heading = 'Предупреждение';
				break;
			case 'error':
				$heading = 'Ошибка';
				break;
		}
		
		ob_start();
		foreach ($this->messages as $text) {
			if (is_array($text)) {
				foreach ($text as $string) {
					?>
					<div class="alert alert-block">
						<a class="close" data-dismiss="alert" href="#">×</a>
						<h4 class="alert-heading"><?php echo $heading ?></h4>
						<?php echo $string; ?>
					</div>
					<?php 
				}
			} else {
				?>
				<div class="alert alert-block">
					<a class="close" data-dismiss="alert" href="#">×</a>
					<h4 class="alert-heading"><?php echo $heading ?></h4>
					<?php echo $string; ?>
				</div>
				<?php 
			}
		}
		return ob_get_clean();
	}
}