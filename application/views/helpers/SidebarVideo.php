<?php
/**
 * Render sidebar video HTML
 */
class Zend_View_Helper_SidebarVideo extends Zend_View_Helper_Abstract
{
    
    public function sidebarVideo($data=array(), $url=null){
        
        if (!count($data)){
            throw new Zend_Exception("Wrong video data");
        }
        
        if (!$url){
            throw new Zend_Exception("Channel url not defined");
        }
        
        ob_start();
        ?>

        <h4><strong><?php echo $data['title'] ?></strong></h4>

        <div class="info">

            <?php  if (!empty($data['desc'])): ?>
            <p class="desc">
                <?php echo $data['desc'] ?>
            </p>
            <?php endif; ?>
            
            <a href="<?php echo $data['link_href'] ?>" 
               title="Клик чтобы смотреть видео <?php echo $data['link_title'] ?>" 
               target="_blank">
                <img src="<?php echo $data['thumb']['url']; ?>" 
                     alt="<?php echo $data['link_title'] ?>" />
            </a>

            <a href="<?php echo $data['link_href'] ?>"
               class="btn btn-inverse btn-mini" 
               title="<?php echo $data['link_title'] ?> видео" 
               target="_blank">
                Смотреть
            </a>

            <div class="viewcount">
                Посмотрели <?php echo (int)$data['views'] ?>
            </div>
            
            <?php /*
            <div class="date">
                Добавлено <?php echo $data['published']->toString( 'd MMMM YYYY') ?>
            </div>
            */ ?>
            
            <div class="duration">
                Длительность <?php echo $data['duration']->toString("HH:mm:ss") ?>
            </div>

        </div>
        
        <?php
        return ob_get_clean();
    }
    
}
?>
