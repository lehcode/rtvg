--
-- Описание для таблицы rtvg_ads
--
CREATE TABLE rtvg_ads (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  link char(255) NOT NULL DEFAULT '',
  image varchar(255) NOT NULL,
  width int(4) UNSIGNED NOT NULL,
  height int(4) UNSIGNED NOT NULL,
  code text NOT NULL,
  alt tinytext NOT NULL,
  hits int(11) UNSIGNED NOT NULL,
  tags varchar(255) NOT NULL,
  client char(16) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  INDEX client (client),
  INDEX height (height),
  INDEX width (width)
)
ENGINE = INNODB
AUTO_INCREMENT = 9
AVG_ROW_LENGTH = 2048
CHARACTER SET utf8
COLLATE utf8_general_ci;
