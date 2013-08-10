--
-- Описание для таблицы rtvg_articles
--
CREATE TABLE rtvg_articles (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title char(255) NOT NULL,
  alias char(255) NOT NULL,
  intro text NOT NULL,
  body text NOT NULL,
  tags char(255) NOT NULL,
  image char(1) NOT NULL,
  metadesc char(255) DEFAULT NULL,
  content_cat int(3) UNSIGNED DEFAULT NULL,
  channel_cat int(3) UNSIGNED DEFAULT NULL,
  prog_cat int(3) UNSIGNED DEFAULT NULL,
  video_cat char(16) DEFAULT NULL,
  hits int(10) UNSIGNED NOT NULL DEFAULT 0,
  published int(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 0,
  publish_up date NOT NULL,
  publish_down date DEFAULT NULL,
  added date NOT NULL,
  author int(11) UNSIGNED DEFAULT NULL,
  is_ref tinyint(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 0,
  is_paid tinyint(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 0,
  is_cpa tinyint(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  INDEX tags (tags)
)
ENGINE = INNODB
AUTO_INCREMENT = 19
AVG_ROW_LENGTH = 7447
CHARACTER SET utf8
COLLATE utf8_general_ci;
