--
-- Описание для таблицы rtvg_channels_ratings
--
CREATE TABLE rtvg_channels_ratings (
  id int(11) UNSIGNED NOT NULL,
  hits int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AVG_ROW_LENGTH = 42
CHARACTER SET utf8
COLLATE utf8_general_ci;
