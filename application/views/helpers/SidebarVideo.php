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

        <h4><i class="icon-plus icon-white"></i><?= $data['title'] ?></h4>

        <div class="info">

            <p class="viewcount">
                Посмотрели <?= (int)$data['views'] ?>
            </p>

            <a href="<?= $data['link_href'] ?>" title="Клик чтобы смотреть видео <?= $data['link_title'] ?>" target="_blank">
                <img src="<?= $data['thumb']['url']; ?>" alt="<?= $data['link_title'] ?>" />
            </a>

            <a class="btn btn-inverse btn-mini" href="<?= $data['link_href'] ?>" title="<?= $data['link_title'] ?> видео" target="_blank">
                Смотреть
            </a>

            <div class="date">
                Загружено: <?= $data['published'] ?>
            </div>

            <div class="duration">
                Длительность: <?= $data['duration'] ?>
            </div>

            <?php  if (!empty($data['desc'])): ?>
            <p class="desc">
                <?= $data['desc'] ?>
            </p>
            <?php endif; ?>

        </div>
        
        <?php
        return ob_get_clean();
    }
    
}
?>
