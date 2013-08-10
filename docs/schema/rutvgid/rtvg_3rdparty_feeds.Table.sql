--
-- Описание для таблицы rtvg_3rdparty_feeds
--
CREATE TABLE rtvg_3rdparty_feeds (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;
