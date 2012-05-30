<?php
class Zend_View_Helper_YoutubeVideos extends Zend_View_Helper_Abstract
{
	public function youtubeVideos($data=array(), $config=array(), $output='content') {
		
		try {
		
			$yt = new Zend_Gdata_YouTube();
			$yt->setMajorProtocolVersion(2);
			$query = $yt->newVideoQuery();
			
			$thumbWidth  = isset($config['thumb_width']) && !empty($config['thumb_width']) ? (int)$config['thumb_width'] : 120 ;
			$showTags    = isset($config['show_tags']) && !empty($config['show_tags']) ? (int)$config['show_tags'] : 5 ;
			$showDate    = isset($config['show_date']) && !empty($config['show_date']) ? (bool)$config['show_date'] : false ;
			$collapse    = isset($config['collapse']) && !empty($config['collapse']) ? (bool)$config['collapse'] : false ;
			$debug       = isset($config['debug']) && !empty($config['debug']) ? (bool)$config['debug'] : false ;
			$order       = isset($config['order']) && !empty($config['order']) ? (string)$config['order'] : 'relevance_lang_ru' ;
			$safesearch  = isset($config['safe_search']) && !empty($config['safe_search']) ? (string)$config['safe_search'] : 'moderate' ;
			$linkTitle   = isset($config['link_title']) && !empty($config['link_title']) ? (bool)$config['link_title'] : false ;
			$descLinks   = isset($config['description_links']) && !empty($config['description_links']) ? (string)$config['description_links'] : 'convert' ;
			
			$maxResults = isset($config['max_results']) && !empty($config['max_results']) ? (int)$config['max_results'] : 5 ;
			$query->setMaxResults($maxResults);
			$startIndex = isset($config['start_index']) && !empty($config['start_index']) ? (int)$config['start_index'] : 1 ;
			$query->setStartIndex ($startIndex);
			
			$query->setParam('lang', 'ru');
			
			/*
			 * Cleanup query items
			 */
			$colon  = new Zend_Filter_PregReplace(array( 'match'=>'/[:]/', 'replace'=>'|' ));
			$trim   = new Zend_Filter_StringTrim(' ,."\'`|');
			$quotes = new Zend_Filter_Word_SeparatorToSeparator( '"', '' );
			$qs = '';
			foreach ($data as $k=>$d) {
				$data[$k] = $trim->filter($quotes->filter( $colon->filter( $d )));
				$qs.=$trim->filter($data[$k]).'|';
			}
			$qs = $trim->filter($qs);
			
			$query->setVideoQuery($qs);
			$query->orderBy = $order;
			$query->setSafeSearch($safesearch);
			/*
			if (Xmltv_Config::getDebug()===true) {
				var_dump($query);
			}
			*/
			$cacheSub = 'Youtube';
			$cache = new Xmltv_Cache(array('location'=>"/cache/$cacheSub"));
			$hash = $cache->getHash( __FUNCTION__.'_'.md5($qs.$output));
			if (Xmltv_Config::getCaching()){
				if (!$videos = $cache->load($hash, 'Core', $cacheSub)) {
					$videos = $yt->getVideoFeed($query->getQueryUrl(2));
					$cache->save($videos, $hash, 'Core', $cacheSub);
				}
			} else {
				$videos = $yt->getVideoFeed($query->getQueryUrl(2));
			}
			
			$data_parent = "videos_$output";
			$html= '<div class="'.$data_parent.'">';
			
			
			//$regex       = new Zend_Filter_PregReplace(array("match"=>'/["\'\(\)\.`\{\}\[\]]/ui', "replace"=>' '));
			//$toSeparator = new Zend_Filter_Word_SeparatorToSeparator(' ', '-');
			
			$entities = new Zend_Filter_HtmlEntities();
			
			foreach ($videos as $videoEntry) {
				
				$videoDescription = $videoEntry->getVideoDescription();
				if (!preg_match('/\p{Cyrillic}+/ui', $videoDescription))
				break;
				if (preg_match('/порн|эрот|проститут/ui', $videoDescription))
				break;
				
				try {
					$videoThumbnails = $videoEntry->getVideoThumbnails();
				} catch (Exception $e) {
					echo $e->getMessage();
				}
				
				foreach($videoThumbnails as $videoThumbnail) {
					if ($videoThumbnail['width'] >= $thumbWidth) {
						
						$vid = $this->_videoId($videoEntry->getVideoId());
						
						$videoTitle = $videoEntry->getVideoTitle();
						$videoTitle = $trim->filter($videoTitle);
						$alias = $this->view->videoAlias($videoTitle);
						
						if ($collapse === true){
							
							$html .= '<h4><a href="#">'.$videoTitle.'</a></h4>';
									
									$html .= '<div class="info">';
									
									$html .= '<a href="/видео/онлайн/'.$alias.'/'.$vid.'">
										<img align="right" src="'.$videoThumbnail['url'].'" alt="'.$videoTitle.'" />
									</a>';
									
									if ($showDate===true) {
										$d = new Zend_Date($videoEntry->getUpdated(), Zend_Date::ISO_8601);
										$d->addHour(3);
										$html .= '<div class="date">'.Xmltv_String::ucfirst( $d->toString("EEEE, d MMMM YYYY, H:mm", 'ru')).'</div>';
									}
									
									if (isset($config['truncate_description']) && $config['truncate_description']>0) {
										$html .= '<p class="desc">'. $this->_addLinks( $this->_truncateString($videoDescription, 50, 'words'), $videoTitle, $descLinks ).'</p>';
									} else {
										$html .= '<p class="desc">'. $this->_addLinks( $videoDescription, $videoTitle, $descLinks  ).'</p>';
									}
									
									if ($config['show_duration']===true) {
										$d = new Zend_Date($videoEntry->getVideoDuration(), Zend_Date::TIMESTAMP);
										$html .= '<div class="duration">Длина видео: '.$d->toString("mm мин ss сек").'</div>';
									}
									
									if ($showTags>0) {
										
										try {
											$tags = $videoEntry->getVideoTags();
										} catch (Exception $e) {
											echo $e->getMessage();
										}
										$html .= '<ul class="tags_list">';
										for ($i=0; $i<count($tags); $i++){
											if ($i<(int)$showTags) {
												$safe_tag = $this->view->safeTag($tags[$i]);
												$html .= '<li><a href="/видео/тема/'.$safe_tag.'" title="">'.$tags[$i].'</a></li>';
											}
										}
										$html .= "</ul>";
										
									}
								$html.='
									</div>';
							
						} else {
							/*
							 * Title
							 */
							//var_dump($linkTitle);
							if ($linkTitle===true)
							$html .= '<h4><a href="/видео/онлайн/'.$alias.'/'.$vid.'" class="video-link" title="Перейти на страницу видео '.$entities->filter( $videoTitle ).'">'.$videoTitle.'</a></h4>';
							else
							$html .= '<h4>'.$videoTitle.'</h4>';
							
							/*
							 * Thumbnail
							 */
							$html .= '<div class="thumb">';
							$html .= '<a href="/видео/онлайн/'.$alias.'/'.$vid.'" title="Предпросмотр '.$entities->filter( $videoTitle ).'""><img src="'.$videoThumbnail['url'].'" alt="'.$entities->filter( $videoTitle ).'" /></a>';
							$html .= '</div>';
							
							if (isset($config['truncate_description']) && $config['truncate_description']>0) {
								$html .= '<p class="desc">'. $this->_addLinks( $this->_truncateString($videoDescription, 50, 'words'), $videoTitle, $descLinks  ).'</p>';
							} else {
								$html .= '<p class="desc">'. $this->_addLinks( $videoDescription, $videoTitle, $descLinks ).'</p>';
							}
							
							if ($config['show_duration']===true) {
								$d = new Zend_Date($videoEntry->getVideoDuration(), Zend_Date::TIMESTAMP);
								$html .= '<div class="duration">Длина видео: '.$d->toString("mm мин ss сек").'</div>';
							}
							
							if ((int)$showTags>0 || $showTags=='all') {
								try {
									$tags = $videoEntry->getVideoTags();
								} catch (Exception $e) {
									echo $e->getMessage();
								}
								$html .= '<h4>Темы этого видео</h4>';
								$html .= '<ul class="tags_list">';
								for ($i=0; $i<count($tags); $i++){
									if ($i<(int)$showTags || $showTags=='all') {
										$safe_tag = $this->view->safeTag($tags[$i]);
										$html .= '<li class="btn btn-mini"><a href="/видео/тема/'.$safe_tag.'" title="">'.$tags[$i].'</a></li>';
									}
								}
								$html .= "</ul>";
							}
							
						}
						break;
					}
				}
			}
			
			$html .= "</div>";
			
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		return $html;
		
	}
	
	private function _videoId($yt_id=null){
		
		if (!$yt_id)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		return strrev( str_replace( "%3D", "", urlencode( base64_encode( (string)$yt_id))));
		
	}
	
	private function _truncateString ($string, $length=10, $mode='letters') {
				
		switch ($mode) {
			case 'words':
				$parts = explode(' ', $string);
				$c = count($parts)-1;
				if (count($parts)>$length) {
					do {
						unset($parts[$c]);
						$c--;
					} while (count($parts)>$length);
					return implode(' ', $parts).'…';
				} else {
					return $string;
				}
				break;
			default:
				return Xmltv_String::substr($this->_input, 0, $length).'…';
		}
	}
	
	private function _addLinks($text=null, $title='', $do='convert'){
		
		if (!$text)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$entities = new Zend_Filter_HtmlEntities();
		//var_dump(func_get_args());
		preg_match_all('/[w]{3}?\.[\p{L}\p{N}-]{2,128}\.[a-z]{2,3}/', $text, $m);
		//var_dump($m);
		
		foreach ($m as $i) {
			foreach ($i as $link) {
				
				$linkTitle = sprintf('Смотреть видео %s на сайте %s', $entities->filter('"'.$title.'"'), $entities->filter($link));
				$link = $entities->filter( trim( str_replace('http://', '', $link), ' /') );
				//var_dump($do);
				switch ($do) {
					case 'convert':
						$text = str_replace($link, '<a href="http://'.$link.'/" rel="nofollow" title="'.$linkTitle.'" target="_blank">'.$link.'</a>', $text);
						break;
					
					default:
					case 'add':
						$text = str_replace($link, '', $text);
						$text .= '<p><a class="btn" href="http://'.$link.'/" rel="nofollow" title="'.$link.'" target="_blank">'.$linkTitle.'</a></p>';
						//var_dump($text);
						//die(__FILE__.': '.__LINE__);
						break;
				}
			}
		}
		//var_dump($text);
		//die(__FILE__.': '.__LINE__);
		return $text;
		
		
	}
	
	/*
	private function _makeAlias($title=null){
		
		if (!$title)
		throw new Zend_Exception("Не указан один или более параметров для ".__FUNCTION__, 500);
		
		$trim       = new Zend_Filter_StringTrim(' "\'.,:-?!(){}[]`');
		$separator  = new Zend_Filter_Word_SeparatorToDash(' ');
		$regex      = new Zend_Filter_PregReplace(array("match"=>'/["\'.,:-\?\{\}\[\]\!`\(\)]+/', 'replace'=>' '));
		$tolower    = new Zend_Filter_StringToLower();
		$doubledash = new Zend_Filter_PregReplace(array("match"=>'/[-]+/', 'replace'=>'-'));
		
		$result = $tolower->filter( $doubledash->filter( $trim->filter( $separator->filter( $regex->filter($title)))));
		
		return $result;
		
	}
	*/
}