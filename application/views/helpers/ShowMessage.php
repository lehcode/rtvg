<?php
/**
 *
 * Helper to display notice/alert/error message
 *
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @package rutvgid
 * @version $Id: ShowMessage.php,v 1.1 2013-03-04 20:35:08 developer Exp $
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