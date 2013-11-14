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
    public function direct($amt=1, $output='random', $width=null, $height=null) {
    
        if (!$width && !$height){
            throw new Zend_Exception("Width and height of ad are both missing. At least one is required.", 500);
        }
        
        $options = array(
            'amt'=>(int)$amt,
            'output'=>$output,
            'width'=>(int)$width,
            'height'=>(int)$height,
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
            ->where("`AD`.`width` <= ".(int)$options['width'])
            ->where("`AD`.`height` <= ".(int)$options['height'])
            //->limit((int)$options['amt'])
        ;
        
        $result = $db->fetchAll($select, null, Zend_Db::FETCH_ASSOC);
        
        foreach ($result as $k=>$v){
            $result[$k]['id'] = (int)$v['id'];
            $result[$k]['width'] = (int)$v['width'];
            $result[$k]['height'] = (int)$v['height'];
            $result[$k]['tags'] = explode(',', $v['tags']);
            //$result[$k]['is_script'] = (bool)$v['is_script'];
            $result[$k]['active'] = (bool)$v['active'];
            $result[$k]['content_cat_id'] = (int)$v['content_cat_id'];
            $result[$k]['channel_cat_id'] = (int)$v['channel_cat_id'];
            $result[$k]['bc_cat_id'] = (int)$v['bc_cat_id'];
        }
        
        if ($options['output'] == 'random'){
            $idx = array_rand($result, 1);
            return array($result[$idx]);
        }
        
        return $result;
		
	}
	
}