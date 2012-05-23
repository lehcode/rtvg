<?
/**
 * Класс обертка, для VkApi. Здесь описываются методы для запросов.
 *
 * @package	VkApi
 * @author	Michael Elovskih <wronglink@gmail.com>
 */


/**
 * Класс VkApi. Для каждого запроса создается экземпляр данного класса,
 * задаются метод API и параметры запроса и вызывается getQuery метод.  
 *
 * @package	VkApi
 * @author	Michael Elovskih <wronglink@gmail.com>
 */
class Xmltv_Vk_Api
{	
	/**
	 * Конструктор класса.
	 * 
	 * @param	integer	$api_id		Id приложения.
	 * @param	string	$method		Название метода API.
	 * @param	string	$secret		Секретный код приложения.
	 * @param	string	$format		Формат ответа (XML или JSON).
	 * @param	string	$version	Версия API.
	 * @param	string	$server_url	Адрес сервера API.
	 * @param	mixed	$timestamp	Timestamp сервера.
	 * @param	mixed	$random		Случайное значение.
	 */
	public function __construct ($api_id,
									$method,
									$secret, 
									$format,
									$version,
									$server_url,
									$timestamp,
									$random)
	{
		$this->api_id = $api_id;
		$this->method = $method;
		$this->secret = $secret;
		$this->format = $format;
		$this->version = $version;
		$this->server_url = $server_url;
		$this->timestamp = $timestamp;
		$this->random = $random;


		$this->parameters = array();

		$this->parameters[] = array('name' => 'api_id', 'value' => $this->api_id);
		$this->parameters[] = array('name' => 'method', 'value' => $this->method);
		$this->parameters[] = array('name' => 'format', 'value' => $this->format);
		$this->parameters[] = array('name' => 'v', 'value' => $this->version);
		$this->parameters[] = array('name' => 'timestamp', 'value' => $this->timestamp);
		$this->parameters[] = array('name' => 'random', 'value' => $this->random);
		
		//var_dump($this->parameters);
		//die(__FILE__.': '.__LINE__);
		
	}

	/**
	 * __toString метод класса.
	 *
	 * @return  string	Строка запроса.	
	 */
	public function __toString ()
	{
		//$this->parameters[] = array('name' => 'sig', 'value' => $this->getSig());

		foreach($this->parameters as $p) 
			$query[] = $p['name'].'='.rawurlencode($p['value']);


		return $this->server_url . join('&', $query);
	}

	/**
	 * Добавляет параметр запроса.
	 * 
	 * @param	string	$p_name		Название параметра
	 * @param	string	$p_value	Значение параметра
	 */
	public function addParameter ($p_name, $p_value)
	{
		$this->parameters[] = array('name' => $p_name, 'value' => $p_value);
	}
	
	/**
	 * Возвращает строку запроса.
	 *
	 * @return  string	Строка запроса.	
	 */
	public function getQuery ()
	{
		return $this->__toString();
	}
	
	/**
	 * Считает Sig-подпись приложения.
	 * 
	 * @return	string	Sig-подпись приложения.
	 */
	private function getSig ()
	{
		sort($this->parameters);
				
		$sig=array();
		foreach($this->parameters as $p) {
			if (is_array($p['value'])) {
				$sig[] = $p['name'].'='. implode( ',', $p['value'] );
			} else {
				$sig[] = implode( '=', $p );
			}
		}
		
		//var_dump($sig);
		//die(__FILE__.': '.__LINE__);
		
		return md5( implode('', $sig) . $this->secret);
	}
	
	
}

?>