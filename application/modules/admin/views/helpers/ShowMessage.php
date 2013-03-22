<?php
/**
 * 
 * Helper to display notice/alert/error message
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package sosedionline
 * @filesource $Source: /home/developer/cvs/rutvgid.ru/application/modules/admin/views/helpers/ShowMessage.php,v $
 * @version $Id: ShowMessage.php,v 1.4 2013-03-22 17:51:44 developer Exp $
 */
class Zend_View_Helper_ShowMessage extends Zend_View_Helper_Abstract
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
		?>
		<div class="alert alert-block">
			<a class="close" data-dismiss="alert" href="#">×</a>
			<h4 class="alert-heading"><?php echo $heading ?></h4>
			<?php echo $msg; ?>
		</div>
		<?php 
		return ob_get_clean();
		
	}
}