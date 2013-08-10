--
-- Описание для таблицы rtvg_channels_comments
--
CREATE TABLE rtvg_channels_comments (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  author varchar(255) NOT NULL,
  intro text NOT NULL,
  created datetime NOT NULL,
  added datetime NOT NULL,
  published int(1) UNSIGNED NOT NULL DEFAULT 1,
  src_url varchar(255) NOT NULL,
  parent_id int(9) UNSIGNED NOT NULL,
  url_crc char(10) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX author (author),
  INDEX parent_id (parent_id),
  UNIQUE INDEX url_hash (url_crc)
)
ENGINE = INNODB
AUTO_INCREMENT = 107
AVG_ROW_LENGTH = 941
CHARACTER SET utf8
COLLATE utf8_general_ci;
