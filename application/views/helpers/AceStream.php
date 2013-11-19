<?php
/*
 * Genrate online TV player HTML codes
 * @author Antony Repin <egeshisolutions@gmail.com>
 */
class Rtvg_View_Helper_AceStream extends Zend_View_Helper_Abstract
{
    /**
     * Entry point
     * @param int|null $site_id
     * @param int|string $stream_id
     * @param int $w
     * @param int $h
     * @param string $provider
     * @return string
     * @throws Zend_Exception
     */ 
	public function aceStream($site_id=null, $stream_id=null, $w=530, $h=352, $provider=null){
		
		if (!$provider) {
		    throw new Zend_Exception("Provider is undefined");
		}
		if (!$stream_id) {
		    $stream_id='';
		}
        
        switch ($provider){
            default:
            case 'torrenttv':
                return $this->torrentvPlayer($site_id, $stream_id, $w, $h);
                break;
            
            case 'tvforsite':
                return $this->tvforsitePlayer($stream_id, $w, $h);
                break;
        }
        
        
        
		
	}
    
    /**
     * Torrent-tv player HTML
     * 
     * @param int $id
     * @param int $stream
     * @param int $w
     * @param int $h
     * @return string
     */
    private function torrentvPlayer($id, $stream, $w, $h){
        
        ob_start();
		?>
		<div id="torrenttv" class="player">
            <iframe src="http://1ttv.net/iframe.php?site=<?php echo (int)$id ?>&channel=<?php echo (int)$stream ?>"
                scrolling="no" frameborder="0" 
                width="<?php echo (int)$w ?>" height="<?php echo (int)$h ?>"
                bgcolor="#FFFFFF" allowtransparency="true" allowfullscreen="true" 
                allowscriptaccess="always">Your browser doesn not support floating frames!
            </iframe>
        </div>
        <?php
        return ob_get_clean();
		
    }
    
    /**
     * Tvforsite Player HTML
     * 
     * @param string $stream
     * @param int $w
     * @param int $h
     * @return string
     */
    private function tvforsitePlayer($stream, $w, $h){
        ob_start();
        ?>
        <div id="tvforsite" class="player">
            <iframe scrolling="no" frameborder="0"
                src="http://tvforsite.net/channel.php?channel=<?php echo $stream ?>&autostart=0&quality=high&bgcolor=000000&donate=1&adult=0"
                width="<?php echo $w ?>" height="<?php echo $h ?>">
                Your browser doesn not support floating frames!
            </iframe>
        </div>
        <?php
        return ob_get_clean();
        
    }


}
?>
