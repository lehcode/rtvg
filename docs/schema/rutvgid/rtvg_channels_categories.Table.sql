--
-- Описание для таблицы rtvg_channels_categories
--
CREATE TABLE rtvg_channels_categories (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  image varchar(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 27
AVG_ROW_LENGTH = 655
CHARACTER SET utf8
COLLATE utf8_general_ci;
