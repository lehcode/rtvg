<?php
class Rtvg_Ad_Script extends Zend_Db_Table_Row_Abstract 
{
    
    protected $_primary = array( 'id' );
    
    /**
     * Scripts to output
     * @todo   Convert to rowset
     * @var    array
     */
    private $_sources;
    
    /**
     * Randomization ratio
     * @var float
     */
    protected static $ratio=1;
    
    /**
     * Create script object
     * 
     * @param unknown_type $script
     */
    public function __construct($source, array $options=null){
    	
        if (isset($options['load_folder']) && $options['load_folder']===true && !empty($options['folder'])){
            $this->_loadFolder( $options['folder'] );
        } else {
            $this->_setData($source);
        }
        
        if (isset($options['minify']) && $options['minify']===true){
	        foreach ($this->_sources as $k=>$js){
	        	$this->_sources[$k] = Rtvg_Compressor_JSMin::minify($js);
	        }
        }
        
        parent::__construct(array());
        
    }
    
    private function _setData($source=null){
        
        $this->_sources[] = $source;
        
    }
    
    public function pickRandom(){
    	
        $src = $this->_sources[rand(0, count($this->_sources)-1)];
        return $src;
        
    }
    
    private function _loadFolder( $folder=null ){
    	
        if (APPLICATION_ENV=='development'){
            //var_dump(func_get_args());
            //die(__FILE__.': '.__LINE__);
        }
        
        if ($handle = opendir($folder)) {
            while (false !== ($entry = readdir($handle))) {
	            if ($entry != "." && $entry != ".." && !stristr($entry, "_")) {
	                $this->_setData( file_get_contents($folder.'/'.$entry));
		        }
            }
            closedir($handle);
        }
        
    }
    
    /**
     * Get script code
     * 
     * @return array:
     */
    public function getCodes(){
        
    	return $this->_sources;
    	
    }

    /**
     * 
     * @return multitype:
     */
    public function getFirst(){
        
    	return $this->_sources[0];
    	
    }
    
}