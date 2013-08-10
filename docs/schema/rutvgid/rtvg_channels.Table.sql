--
-- Описание для таблицы rtvg_channels
--
CREATE TABLE rtvg_channels (
  id int(11) UNSIGNED NOT NULL,
  title char(128) NOT NULL,
  alias char(128) NOT NULL,
  desc_intro text NOT NULL,
  desc_body text NOT NULL,
  category int(4) UNSIGNED DEFAULT NULL,
  featured bit(1) NOT NULL DEFAULT b'0',
  icon char(128) NOT NULL DEFAULT '',
  format enum ('dvb', 'hd', '3d') NOT NULL DEFAULT 'dvb',
  published bit(1) NOT NULL DEFAULT b'1',
  parse bit(1) NOT NULL DEFAULT b'1',
  lang char(3) NOT NULL DEFAULT '',
  url char(255) NOT NULL DEFAULT '',
  country char(3) NOT NULL DEFAULT '',
  adult bit(1) NOT NULL DEFAULT b'0',
  keywords char(255) NOT NULL DEFAULT '',
  metadesc char(255) NOT NULL DEFAULT '',
  video_aspect enum ('4:3', '16:9') NOT NULL DEFAULT '16:9',
  video_quality enum ('720x576', '1920x1080', '3840x2160', '3840x2160') NOT NULL DEFAULT '720x576',
  audio enum ('mono', 'stereo', 'dolby', 'dolby digital', 'bilingual', 'surround') NOT NULL DEFAULT 'stereo',
  added date DEFAULT NULL,
  address char(255) NOT NULL DEFAULT '',
  region char(1) NOT NULL DEFAULT '',
  location char(1) NOT NULL DEFAULT '',
  geo_lt double DEFAULT NULL,
  geo_lg double DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX alias (alias)
)
ENGINE = INNODB
AVG_ROW_LENGTH = 3310
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'http://xmltv.cvs.sourceforge.net/viewvc/xmltv/xmltv/xmltv.dt';
