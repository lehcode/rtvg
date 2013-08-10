--
-- Описание для таблицы rtvg_programs_categories
--
CREATE TABLE rtvg_programs_categories (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  title_single varchar(255) NOT NULL,
  alias varchar(255) NOT NULL,
  movie int(1) UNSIGNED NOT NULL DEFAULT 0,
  series int(1) UNSIGNED NOT NULL DEFAULT 0,
  cartoon int(1) UNSIGNED NOT NULL DEFAULT 0,
  sport int(1) UNSIGNED NOT NULL DEFAULT 0,
  news int(1) UNSIGNED NOT NULL DEFAULT 0,
  params varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX title (title)
)
ENGINE = INNODB
AUTO_INCREMENT = 53
AVG_ROW_LENGTH = 390
CHARACTER SET utf8
COLLATE utf8_general_ci;
