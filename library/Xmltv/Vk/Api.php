<?php
/**
 * 
 * Vkontakte API Connector
 * @author Ivan Shumov <iashumov@gmail.com>
 * @version 0.1
 *
 */
class Xmltv_Vk_Api
{
    private $applicationId  = null;
    private $secretKey      = "";
    private $version        = "3.0";
    private $format         = "JSON";
    private $url            = "http://api.vkontakte.ru/api.php";

    function __construct($applicationId = 0, $secretKey = '')
    {
        $this->applicationId = $applicationId;
        $this->secretKey = $secretKey;
    }
    
    
    /**
     * video.search
     * 
     * Получает настройки текущего пользователя в данном приложении. 
     *
     * @var $uid string
     *
     */
    function videoSearch( $query = array() )
    {
        $method = "video.search";
        $data = array(
            "q"    => implode( ' ', $query ),
        );
        $r = $this->process($method, $data);
        
        //var_dump($r);
        //die( __FILE__.': '.__LINE__ );
        
        if($r['response'] !== false)
        {
            $r['response'] = explode(",", $r['response']);
        }
        return $r;
    }
    
    
    /**
     * getUserSettings
     * 
     * Получает настройки текущего пользователя в данном приложении. 
     *
     * @var $uid string
     *
     */
    function getUserSettings( $uid ='' )
    {
        $method = "getUserSettings";
        $data = array(
            "uid"    => $uid,
        );
        $r = $this->process($method, $data);
        if($r['response'] !== false)
        {
            $r['response'] = explode(",", $r['response']);
        }
        return $r;
    }
    
    
    /**
     * sendNotification
     * 
     * отправляет уведомление пользователю
     *
     * @var $uids Array
     * @var $message String
     *
     */
    function sendNotification($uids = array(), $message = '')
    {
        $method = "secure.sendNotification";
        $data = array(
            "uids"    => implode(",", $uids),
            "message" => $message
        );
        $r = $this->process($method, $data);
        if($r['response'] !== false)
        {
            $r['response'] = explode(",", $r['response']);
        }
        return $r;
    }

    /**
     * saveAppStatus
     *
     * сохраняет строку статуса приложения для последующего вывода в общем списке приложений на странице пользоваетеля
     *
     * @var $uid Integer
     * @var $status String maxLength 32
     */
    function saveAppStatus($uid = 0, $status = '')
    {
        $method = "secure.saveAppStatus";
        $data = array(
            "uid"    => intval($uid),
            "status" => $status
        );
        return $this->process($method, $data);
    }

    /**
     * getAppStatus
     *
     * возвращает строку статуса приложения, сохранённую при помощи saveAppStatus
     *
     * @var $uid Integer
     */
    function getAppStatus($uid = 0)
    {
        $method = "secure.getAppStatus";
        $data = array(
            "uid"    => intval($uid)
        );
        return $this->process($method, $data);
    }

    /**
     * getAppBalance
     *
     * возвращает платежный баланс приложения
     */
    function getAppBalance()
    {
        $method = "secure.getAppBalance";
        $data = array();
        return $this->process($method, $data);
    }

    /**
     * getBalance
     *
     * возвращает баланс пользователя на счету приложения
     *
     * @var $uid Integer
     */
    function getBalance($uid = 0)
    {
        $method = "secure.getBalance";
        $data = array(
            "uid"    => intval($uid)
        );
        return $this->process($method, $data);
    }

    /**
     * withdrawVotes
     *
     * списывает голоса со счета пользователя на счет приложения
     *
     * @var $uid Integer
     * @var $votes Integer
     */
    function withdrawVotes($uid = 0, $votes = 0)
    {
        $method = "secure.withdrawVotes";
        $data = array(
            "uid"    => intval($uid),
            "votes"  => intval($votes)*100
        );
        return $this->process($method, $data);
    }

    /**
     * getTransactionsHistory
     *
     * возвращает историю транзакций внутри приложения
     *
     * @var $type Integer (0 – все транзакции, 1 – транзакции типа "пользователь → приложение", 2 – транзакции типа "приложение → пользователь", 3 – транзакции типа "пользователь → пользователь")
     * @var $uid_from Integer
     * @var $uid_to Integer
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $limit Integer
     */
    function getTransactionsHistory($type = 0, $uid_from = 0, $uid_to = 0, $date_from = 0, $date_to = 0, $limit = 1000)
    {
        $method = "secure.getTransactionsHistory";
        $data = array(
            "type"      => $type,
            "uid_from"  => $uid_from,
            "uid_to"    => $uid_to,
            "date_from" => $date_from,
            "date_to"   => $date_to,
            "limit"     => $limit
        );
        return $this->process($method, $data);
    }

    /**
     * getTransactionsHistoryAll
     *
     * возвращает историю транзакций внутри приложения Все
     *
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $limit Integer
     */
    function getTransactionsHistoryAll($date_from = 0, $date_to = 0, $limit = 1000)
    {
        $method = "secure.getTransactionsHistory";
        return $this->getTransactionsHistory(0, 0, 0, $date_from, $date_to, $limit);
    }

    /**
     * getTransactionsHistoryUserToApplication
     *
     * возвращает историю транзакций внутри приложения "пользователь → приложение"
     *
     * @var $uid_from Integer
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $limit Integer
     */
    function getTransactionsHistoryUserToApplication($uid_from = 0, $date_from = 0, $date_to = 0, $limit = 1000)
    {
        return $this->getTransactionsHistory(1, $uid_from, 0, $date_from, $date_to, $limit);
    }

    /**
     * getTransactionsHistoryApplicationToUser
     *
     * возвращает историю транзакций внутри приложения "приложение → пользователь"
     *
     * @var $uid_to Integer
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $limit Integer
     */
    function getTransactionsHistoryApplicationToUser($uid_to = 0, $date_from = 0, $date_to = 0, $limit = 1000)
    {
        return $this->getTransactionsHistory(2, 0, $uid_to, $date_from, $date_to, $limit);
    }

    /**
     * getTransactionsHistoryUserToUser
     *
     * возвращает историю транзакций внутри приложения "пользователь → пользователь"
     *
     * @var $uid_from Integer
     * @var $uid_to Integer
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $limit Integer
     */
    function getTransactionsHistoryUserToUser($uid_from = 0, $uid_to = 0, $date_from = 0, $date_to = 0, $limit = 1000)
    {
        return $this->getTransactionsHistory(3, $uid_from, $uid_to, $date_from, $date_to, $limit);
    }

    /**
     * addRating
     *
     * поднимает пользователю рейтинг от имени приложения
     *
     * @var $uid Integer
     * @var $rate Integer
     * @var $message String maxLength 512 (wiki supports)
     */
    function addRating($uid = 0, $rate = 0, $message = '')
    {
        $method = "secure.addRating";
        $data = array(
            "uid"       => (int)$uid,
            "rate"      => (int)$rate,
            "message"   => $message
        );
        return $this->process($method, $data);
    }

    /**
     * setCounter
     *
     * устанавливает счетчик, который выводится пользователю жирным шрифтом в левом меню, если он добавил приложение в левое меню
     *
     * @var $uid Integer
     * @var $counter Integer
     */
    function setCounter($uid = 0, $counter = 0)
    {
        $method = "secure.setCounter";
        $data = array(
            "uid"       => intval($uid),
            "counter"      => intval($counter)
        );
        return $this->process($method, $data);
    }

    /**
     * getSMSHistory
     *
     * возвращает список SMS-уведомлений, отосланных приложением
     *
     * @var $uid Integer
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $limit Integer
     */
    function getSMSHistory($uid = 0, $date_from = 0, $date_to = 0, $limit = 1000)
    {
        $method = "secure.getSMSHistory";
        $data = array(
            "uid"       => $uid,
            "date_from" => $date_from,
            "date_to"   => $date_to,
            "limit"     => $limit
        );
        return $this->process($method, $data);
    }

    /**
     * sendSMSNotification
     *
     * отправляет SMS-уведомление на телефон пользователя
     *
     * @var $uid Integer
     * @var $message String maxLength 160
     */
    function sendSMSNotification($uid = 0, $message = '')
    {
        $method = "secure.sendSMSNotification";
        $data = array(
            "uid"       => intval($uid),
            "message"   => $message
        );
        return $this->process($method, $data);
    }

    /**
     * getSMS
     *
     * возвращает тексты SMS, полученные от пользователей приложения
     *
     * @var $date_from Integer Timestamp
     * @var $date_to Integer Timestamp
     * @var $uid Integer (необязательный параметр)
     */
    function getSMS($date_from = 0, $date_to = 0, $uid = 0)
    {
        $method = "secure.getSMS";
        $data = array(
            "date_from" => $date_from,
            "date_to"   => $date_to
        );
        if(isset($uid)) $data['uid'] = $uid;
        return $this->process($method, $data);
    }

    /**
     * process
     *
     * обработчих всех методов, непосредственно отправляет запросы к Api Вконтакте
     * @var $method String
     * @var data Array
     */
    private function process($method = '',$data = array())
    {
    	
   		//var_dump($this);
   		//die(__FILE__.': '.__LINE__);
    	
        $timestamp = time();
        $random = rand(0, 100);
        $api_secret = (string)$this->secretKey;
        $staticData = array(
            "api_id"    => (string)$this->applicationId,
            "format"    => $this->format,
            "method"    => $method,
            "random"    => $random,
            "timestamp" => $timestamp,
            "v"         => $this->version
        );
        $data = array_merge($data, $staticData);
        ksort($data);
        $sigText = "";
        foreach($data as $k=>$v) $sigText .= $k."=".$v;
        $sig = md5($sigText.(string)$this->secretKey);
        $data["sig"] = $sig;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        curl_close($ch);
        
        $response = json_decode($response);
        
        //var_dump($response);
        //die(__FILE__.': '.__LINE__);
        
        $responseData = array();
        if(isset($response->error))
        {
            $responseData['error'] = array(
                'code'      => $response->error->error_code,
                'message'   => $response->error->error_msg,
                'params'    => array()
                );
            foreach($response->error->request_params as $k => $param)
            {
                $responseData['error']['params'][$param->key] = $param->value;
            }
            $responseData['response'] = false;
        }
        else
        {
            $responseData['response'] = $response->response;
        }
        return $responseData;
    }
}
