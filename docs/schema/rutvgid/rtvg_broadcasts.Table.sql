--
-- Описание для таблицы rtvg_broadcasts
--
CREATE TABLE rtvg_broadcasts (
  hash char(32) NOT NULL,
  title char(255) NOT NULL,
  alias char(255) NOT NULL,
  category int(4) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (hash)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
