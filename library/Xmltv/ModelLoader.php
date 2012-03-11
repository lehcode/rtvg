<?php
class Xmltv_ModelLoader extends Zend_Controller_Plugin_Abstract
{
        public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
        {
                set_include_path(
                        rtrim(get_include_path(),':') . ':' . APPLICATION_PATH.'/modules/'.$request->getModuleName().'/models'.':'
                );
        }
}