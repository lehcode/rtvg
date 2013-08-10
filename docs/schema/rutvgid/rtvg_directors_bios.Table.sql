--
-- Описание для таблицы rtvg_directors_bios
--
CREATE TABLE rtvg_directors_bios (
  director int(11) UNSIGNED NOT NULL,
  bio text NOT NULL,
  born datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  dead datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE INDEX director (director)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
