--
-- Описание для таблицы rtvg_content_categories
--
CREATE TABLE rtvg_content_categories (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  keywords varchar(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX alias (alias),
  UNIQUE INDEX title (title)
)
ENGINE = INNODB
AUTO_INCREMENT = 7
AVG_ROW_LENGTH = 2730
CHARACTER SET utf8
COLLATE utf8_general_ci;
