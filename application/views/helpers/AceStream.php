<?php
/*
 * @author Antony Repin <egeshisolutions@gmail.com>
 */
class Rtvg_View_Helper_AceStream extends Zend_View_Helper_Abstract
{
    
    private $url = 'http://1ttv.net/iframe.php';
    
	public function aceStream($site_id=null, $stream_id=null, $w=540, $h=420){
		
		if (!$site_id) {
		    throw new Zend_Exception("Site ID is undefined");
		}
		if (!$stream_id) {
		    throw new Zend_Exception("Stream ID is undefined");
		}
        
        ob_start();
		?>
		<div id="vframe">
            <iframe src="<?= $this->url ?>?site=<?= (int)$site_id ?>&channel=<?= (int)$stream_id ?>"
                scrolling="no" frameborder="0" width="<?= (int)$w ?>" height="<?= (int)$h ?>"
                bgcolor="#FFFFFF" allowtransparency="true" allowfullscreen="true" 
                allowscriptaccess="always">Your browser doesn not support floating frames!</iframe>
        </div>
        <?php
        $html = ob_get_clean();
		return $html;
		
	}
	
}
?>
