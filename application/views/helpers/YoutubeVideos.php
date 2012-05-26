<?php
class Zend_View_Helper_YoutubeVideos extends Zend_View_Helper_Abstract
{
	public function youtubeVideos($data=array(), $config=array(), $output='content') {
		
		try {
		
			$yt = new Zend_Gdata_YouTube();
			$yt->setMajorProtocolVersion(2);
			$query = $yt->newVideoQuery();
			
			$thumb_width = isset($config['thumb_width']) && !empty($config['thumb_width']) ? (int)$config['thumb_width'] : 120 ;
			$show_tags   = isset($config['show_tags']) && !empty($config['show_tags']) ? (int)$config['show_tags'] : false ;
			$show_date   = isset($config['show_date']) && !empty($config['show_date']) ? (bool)$config['show_date'] : false ;
			$collapse    = isset($config['collapse']) && !empty($config['collapse']) ? (bool)$config['collapse'] : false ;
			$debug       = isset($config['debug']) && !empty($config['debug']) ? (bool)$config['debug'] : false ;
			$order       = isset($config['order']) && !empty($config['order']) ? (string)$config['order'] : 'relevance_lang_ru' ;
			$safesearch  = isset($config['safe_search']) && !empty($config['safe_search']) ? (string)$config['safe_search'] : 'moderate' ;
			
			$max_results = isset($config['max_results']) && !empty($config['max_results']) ? (int)$config['max_results'] : 5 ;
			$query->setMaxResults($max_results);
			$start_index = isset($config['start_index']) && !empty($config['start_index']) ? (int)$config['start_index'] : 1 ;
			$query->setStartIndex ($start_index);
			
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
			
			if (Xmltv_Config::getDebug()===true) {
				var_dump($query);
			}
			
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
					if ($videoThumbnail['width'] >= $thumb_width) {
						
						$vid = $this->_videoId($videoEntry->getVideoId());
						
						$title = $videoEntry->getVideoTitle();
						$title = $trim->filter($title);
						$alias = $this->view->videoAlias($title);
						
						if ($collapse === true){
							
							$html .= '<h4><a href="#">'.$title.'</a></h4>';
									
									$html .= '<div class="info">';
									
									$html .= '<a href="/видео/онлайн/'.$alias.'/'.$vid.'">
										<img align="right" src="'.$videoThumbnail['url'].'" alt="'.$videoEntry->getVideoTitle().'" />
									</a>';
									
									if ($config['show_date']===true) {
										$d = new Zend_Date($videoEntry->getUpdated(), Zend_Date::ISO_8601);
										$d->addHour(3);
										$html .= '<div class="date">'.Xmltv_String::ucfirst( $d->toString("EEEE, d MMMM YYYY, H:mm", 'ru')).'</div>';
									}
									
									if (isset($config['truncate_description']) && $config['truncate_description']>0) {
										$html .= '<p class="desc">'.$this->_truncateString($videoDescription, 50, 'words').'</p>';
									} else {
										$html .= '<p class="desc">'.$videoDescription.'</p>';
									}
									
									if ($config['show_duration']===true) {
										$d = new Zend_Date($videoEntry->getVideoDuration(), Zend_Date::TIMESTAMP);
										$html .= '<div class="duration">Длина видео: '.$d->toString("mm мин ss сек").'</div>';
									}
									
									if ((int)$config['show_tags']>0) {
										
										try {
											$tags = $videoEntry->getVideoTags();
										} catch (Exception $e) {
											echo $e->getMessage();
										}
										$html .= '<ul class="tags_list">';
										for ($i=0; $i<count($tags); $i++){
											if ($i<(int)$config['show_tags']) {
												$safe_tag = $this->view->safeTag($tags[$i]);
												$html .= '<li><a href="/видео/тема/'.$safe_tag.'" title="">'.$tags[$i].'</a></li>';
											}
										}
										$html .= "</ul>";
										
									}
								$html.='
									</div>';
							
						} else {
							
							$html .= '<h4>'.$title.'</h4>
							<div class="thumb">';
							$html .= '<a href="/видео/онлайн/'.$alias.'/'.$vid.'"><img src="'.$videoThumbnail['url'].'" /></a>';
							$html .= '</div>';
							
							if (isset($config['truncate_description']) && $config['truncate_description']>0) {
								$html .= '<p class="desc">'.$this->_truncateString($videoDescription, 50, 'words').'</p>';
							} else {
								$html .= '<p class="desc">'.$videoDescription.'</p>';
							}
							
							if ($config['show_duration']===true) {
								$html .= '<div class="duration">'.$videoEntry->getVideoDuration().'</div>';
							}
							
							if ((int)$config['show_tags']>0 || $config['show_tags']=='all') {
								try {
									$tags = $videoEntry->getVideoTags();
								} catch (Exception $e) {
									echo $e->getMessage();
								}
								$html .= '<h4>Темы этого видео</h4>';
								$html .= '<ul class="tags_list">';
								for ($i=0; $i<count($tags); $i++){
									if ($i<(int)$config['show_tags'] || $config['show_tags']=='all') {
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