<?php
class Xmltv_VkApiNode
{
	/**
	 * Конструктор класса. Принимает обязательные $api_id (Id приложения) и
	 * $secret (секретный код приложения). 
	 * 
	 * @param	integer	$api_id		Id приложения.
	 * @param	string	$secret		Секретный код приложения.
	 * @param	string	$format		Формат ответа (XML или JSON). По умолчанию, 'XML'.
	 * @param	string	$version	Версия API. По умолчанию, '2.0'.
	 * @param	string	$server_url	Адрес сервера API. По умолчанию 'http://api.vkontakte.ru/api.php?'.
	 * @param	mixed	$timestamp	Timestamp сервера. Если не задано - берется системное.
	 * @param	mixed	$random		Случайное значение. Если не задано - задается через rand().
	 */
	public function __construct ($api_id,
									$secret, 
									$format = 'XML',
									$version = '2.0',
									$server_url = 'http://api.vkontakte.ru/api.php?',
									$timestamp = false,
									$random = false)
	{
		$this->api_id = $api_id;
		//$this->method = $method;
		$this->secret = $secret;
		$this->format = $format;
		$this->version = $version;
		$this->server_url = $server_url;
		$this->timestamp = $timestamp ? $timestamp : time();
		$this->random = $random ? $random : rand();
	}
	
	/**
	 * Отправляет уведомления пользователям. Для того, чтобы пользователь получил уведомление
	 * необходимо, чтобы у него было разрешено получение уведомлений в настройках.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.sendNotification	
	 * 
	 * @param	mixed	$uids		Id пользователей (массив до 100 чисел или число).
	 * @param	string	$message	Текст сообщения.
	 *
	 * @return  string	Строка запроса.	
	 */
	public function sendNotification ($uids, $message)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.sendNotification',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uids', join(',', $uids));
		$api->addParameter('message', $message);
		return $api->getQuery();
	}
	
	/**
	 * Сохраняет строку статуса приложения для последующего вывода в
	 * общем списке приложений на странице пользоваетеля.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.saveAppStatus
	 * 
	 * @param	integer	$uid	Id пользователя.
	 * @param	string	$status	Текст статуса. Строка до 32 символов.
	 *
	 * @return  string	Строка запроса.	
	 */
	public function saveAppStatus ($uid, $status)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.saveAppStatus',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uid', $uid);
		$api->addParameter('status', $status);
		return $api->getQuery();
	}

	/**
	 * Возвращает платежный баланс приложения.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.getAppBalance
	 *
	 * @return  string	Строка запроса.	
	 */
	public function getAppBalance ()
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.getAppBalance',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		return $api->getQuery();
	}
	
	/**
	 * получает уровень пользователя в приложении.
	 * @see	http://vk.com/developers.php?oid=-1&p=secure.getUserLevel
	 *
	 * @param array $uids //идентификаторы пользователей, разделённые через запятую, игровые уровни которых необходимо получить. 
	 * @return  string	Строка запроса.	
	 */
	public function getGroups ( $uids=array() )
	{
		$api = new Xmltv_VkApi($this->api_id,
							'getGroups',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uids', implode(',', $uids));
		return $api->getQuery();
	}
	
	
	public function videoSearch ( $search='' ) {
		
		$api = new Xmltv_VkApi($this->api_id,
							'video.search',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('q', $search);
		return $api->getQuery();
	}

	/**
	 * Возвращает баланс пользователя на счету приложения.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.getBalance
	 * 
	 * @param	integer	$uid	Id пользователя.
	 *
	 * @return  string	Строка запроса.	
	 */
	public function getBalance ($uid)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.getBalance',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uid', $uid);
		return $api->getQuery();
	}

	/**
	 * Переводит голоса со счета приложения на счет пользователя.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.addVotes
	 * 
	 * @param	integer	$uid	Id пользователя.
	 * @param	integer	$votes	Количество голосов (в 100 долях).
	 *
	 * @return  string	Строка запроса.	
	 */
	public function addVotes ($uid, $votes)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.addVotes',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uid', $uid);
		$api->addParameter('votes', $votes);
		return $api->getQuery();
	}

	/**
	 * Списывает голоса со счета пользователя на счет приложения.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.withdrawVotes
	 * 
	 * @param	integer	$uid	Id пользователя.
	 * @param	integer	$votes	Количество голосов (в 100 долях).
	 *
	 * @return  string	Строка запроса.	
	 */
	public function withdrawVotes ($uid, $votes)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.withdrawVotes',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uid', $uid);
		$api->addParameter('votes', $votes);
		return $api->getQuery();
	}

	/**
	 * Переводит голоса со счета одного пользователя на счет другого в рамках приложения.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.transferVotes
	 * 
	 * @param	integer	$uid_from	Id пользователя, от которого переводятся голоса.
	 * @param	integer	$uid_to		Id пользователя, которому переводятся голоса.
	 * @param	integer	$votes		Количество голосов (в 100 долях).
	 * 
	 * @return  string	Строка запроса.	
	 */
	public function transferVotes ($uid_from, $uid_to, $votes)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.transferVotes',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		$api->addParameter('uid_from', $uid_from);
		$api->addParameter('uid_to', $uid_to);
		$api->addParameter('votes', $votes);
		return $api->getQuery();
	}

	/**
	 * Возвращает историю транзакций внутри приложения.
	 * @see	http://vkontakte.ru/pages.php?o=-1&p=secure.getTransactionsHistory
	 * 
	 * @param	integer	$type	Тип возвращаемых транзакций. 
	 * 0 – все транзакции, 1 – транзакции типа "пользователь → приложение",
	 * 2 – транзакции типа "приложение → пользователь", 3 – транзакции типа "пользователь → пользователь" 
	 * @param	integer	$uid_from	Фильтр по Id пользователя, с баланса которого снимались голоса.
	 * @param	integer	$uid_to		Фильтр по Id пользователя, на баланс которого начислялись голоса.
	 * @param	integer	$date_from	Фильтр по дате начала. Задается в виде UNIX-time.
	 * @param	integer	$date_to	фильтр по дате конца. Задается в виде UNIX-time.
	 * @param	integer	$limit		Количество возвращаемых записей. По умолчанию 1000.
	 *
	 * @return  string	Строка запроса.	
	 */
	public function getTransactionsHistory ($type = 0, $uid_from = null, $uid_to = null, $date_from = null, $date_to = null, $limit = 1000)
	{
		$api = new Xmltv_VkApi($this->api_id,
							'secure.getTransactionsHistory',
							$this->secret,
							$this->format,
							$this->version,
							$this->server_url,
							$this->timestamp,
							$this->random);
		if($type)
			$api->addParameter('type', $type);
		if($uid_from)
			$api->addParameter('uid_from', $uid_from);
		if($uid_to)
			$api->addParameter('uid_to', $uid_to);
		if($date_from)
			$api->addParameter('date_from', $date_from);
		if($date_to)
			$api->addParameter('date_to', $date_to);
		if($limit)
			$api->addParameter('limit', $limit);
		return $api->getQuery();
	}
	
}