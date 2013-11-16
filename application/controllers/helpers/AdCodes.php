<?php
/**
 * 
 * @author  Antony Repin <egeshisolutions@gmail.com>
 * @version $Id: AdCode.php,v 1.4 2013-04-11 05:21:23 developer Exp $
 *
 */
class Zend_Controller_Action_Helper_AdCodes extends Zend_Controller_Action_Helper_Abstract
{
    
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;
    
    /**
     * Constructor: initialize plugin loader
     *
     * @return void
     */
    public function __construct()
    {
    	$this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    /**
     * 
     * @param type $amt //Amount of ads
     * @param type $output // random|stack
     * @param type $width // Filter ads of desired width or more
     * @param type $height // Filter ads of desired width or more
     * @return string
     */
    public function direct($amt=1, $max_width=null, $min_width=null, $output='random') {
    
        if (!$max_width || !$min_width){
            throw new Zend_Exception("Width or Min width of ad is missing. Both are required.", 500);
        }
        
        $options = array(
            'amt'=>(int)$amt,
            'output'=>$output,
            'max_width'=>(int)$max_width,
            'min_width'=>(int)$min_width,
        );
        
        $result = $this->getCodes($options);
        return $result;
    
    }
    
    /**
     * Get ad code
     * 
     * @param array $options
     * @return string
     */
	private function getCodes($options = array()){
		
        $db = Zend_Registry::get('db_local');
        Zend_Db_Table::setDefaultAdapter($db);
        $select = $db->select()
            ->from(array('AD'=>'rtvg_ads'), '*')
            ->where("`AD`.`active` IS TRUE")
            ->where("`AD`.`width` >= ".(int)$options['min_width'])
            ->where("`AD`.`width` <= ".(int)$options['max_width'])
        ;
        
        $result = $db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
        $ads = array();
        if (count($result)>1){
            $idx = array_rand($result, $options['amt']);
            foreach ($idx as $k=>$ad){
                $ads[] = $result[$ad];
            }
        }
        
        foreach ($ads as $k=>$v){
            $ads[$k]['id'] = (int)$v['id'];
            $ads[$k]['width'] = (int)$v['width'];
            $ads[$k]['height'] = (int)$v['height'];
            $ads[$k]['tags'] = explode(',', $v['tags']);
            $ads[$k]['active'] = (bool)$v['active'];
            $ads[$k]['content_cat_id'] = (int)$v['content_cat_id'];
            $ads[$k]['channel_cat_id'] = (int)$v['channel_cat_id'];
            $ads[$k]['bc_cat_id'] = (int)$v['bc_cat_id'];
        }
        
        return $ads;
		
	}
	
}