--
-- Описание для таблицы rtvg_bc_events
--
CREATE TABLE rtvg_bc_events (
  hash char(32) binary CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  date datetime NOT NULL,
  channel int(9) UNSIGNED NOT NULL,
  PRIMARY KEY (hash)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
