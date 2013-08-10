--
-- Описание для таблицы rtvg_actors_bios
--
CREATE TABLE rtvg_actors_bios (
  actor int(10) UNSIGNED NOT NULL,
  bio text NOT NULL,
  UNIQUE INDEX actor (actor)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
