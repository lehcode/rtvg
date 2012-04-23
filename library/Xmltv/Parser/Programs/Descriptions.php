<?php
class Xmltv_Parser_Programs_Descriptions extends Xmltv_Parser_ProgramInfoParser {
	
	public function getProgram(){
		
		$this->_program->title = $this->_title;
		$this->_program->alias = $this->_alias;
		//$this->_program->sub_title = $this->_sub_title;
		//var_dump($this->_program);
		return $this->_program;
		
	}
	
	protected function setTitle(){
		
		$this->cleanTitle( 'Перерыв' );
		
	}
	
	protected function matches($input=null){
		
		if(  !$input ) throw new Exception( 
		"Не указан параметр для " . __METHOD__, 500 );
		
		if( preg_match( '/^.*Внимание(.+)до(.+)вещание.+$/iu', $input )
		|| Xmltv_String::stristr( $input, 'профилактика' ) 
		|| Xmltv_String::stristr( $input, 'канал заканчивает' )
		|| Xmltv_String::stristr( $input, 'перерыв' ) ) {
			return true;
		}
		
	}
	
}