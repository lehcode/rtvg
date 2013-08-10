--
-- Описание для таблицы rtvg_programs
--
CREATE TABLE rtvg_programs (
  title varchar(255) NOT NULL,
  sub_title varchar(255) DEFAULT '',
  alias varchar(255) NOT NULL,
  channel int(11) UNSIGNED NOT NULL,
  start datetime NOT NULL,
  end datetime NOT NULL,
  category int(3) UNSIGNED DEFAULT NULL,
  rating int(2) UNSIGNED DEFAULT NULL,
  new bit(1) NOT NULL DEFAULT b'0',
  live bit(1) NOT NULL DEFAULT b'0',
  image char(255) NOT NULL DEFAULT '',
  last_chance bit(1) NOT NULL DEFAULT b'0',
  previously_shown datetime DEFAULT NULL,
  country char(24) DEFAULT NULL,
  actors char(255) NOT NULL DEFAULT '',
  directors char(255) NOT NULL DEFAULT '',
  writers char(255) NOT NULL DEFAULT '',
  adapters char(255) NOT NULL DEFAULT '',
  producers char(255) NOT NULL DEFAULT '',
  composers char(255) NOT NULL DEFAULT '',
  operators char(255) NOT NULL DEFAULT '',
  editors char(255) NOT NULL DEFAULT '',
  presenters char(255) NOT NULL DEFAULT '',
  commentators char(255) NOT NULL DEFAULT '',
  guests char(255) NOT NULL DEFAULT '',
  episode_num int(4) UNSIGNED DEFAULT NULL,
  premiere bit(1) NOT NULL DEFAULT b'0',
  date date DEFAULT NULL,
  length time DEFAULT NULL,
  `desc` text NOT NULL,
  hash char(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  PRIMARY KEY (hash),
  INDEX alias (alias (24)),
  INDEX channel (channel),
  INDEX start (start)
)
ENGINE = INNODB
AVG_ROW_LENGTH = 6536
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'http://xmltv.cvs.sourceforge.net/viewvc/xmltv/xmltv/xmltv.dt';
