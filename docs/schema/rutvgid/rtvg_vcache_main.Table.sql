--
-- Описание для таблицы rtvg_vcache_main
--
CREATE TABLE rtvg_vcache_main (
  rtvg_id char(15) NOT NULL,
  yt_id char(11) NOT NULL,
  title varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  `desc` text NOT NULL,
  views int(11) UNSIGNED NOT NULL,
  published date NOT NULL,
  duration time NOT NULL,
  category varchar(255) NOT NULL,
  thumbs text NOT NULL,
  delete_at datetime NOT NULL,
  PRIMARY KEY (yt_id),
  INDEX rtvg_id (rtvg_id (9))
)
ENGINE = INNODB
AVG_ROW_LENGTH = 4986
CHARACTER SET utf8
COLLATE utf8_general_ci;
