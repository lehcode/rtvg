--
-- Описание для таблицы rtvg_programs_ratings
--
CREATE TABLE rtvg_programs_ratings (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  alias char(255) NOT NULL,
  channel int(11) UNSIGNED DEFAULT NULL,
  hits int(11) UNSIGNED NOT NULL DEFAULT 0,
  star_rating double UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 5369
AVG_ROW_LENGTH = 604
CHARACTER SET utf8
COLLATE utf8_general_ci;
