--
-- Описание для таблицы rtvg_torrents
--
CREATE TABLE rtvg_torrents (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  short_link varchar(32) NOT NULL,
  words varchar(255) NOT NULL,
  update_at datetime NOT NULL,
  PRIMARY KEY (id),
  INDEX words (words (32))
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;
