--
-- Описание для таблицы rtvg_programs_descs
--
CREATE TABLE rtvg_programs_descs (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  checksum char(16) NOT NULL,
  channel int(9) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  INDEX channel (channel),
  INDEX hash (checksum)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;
