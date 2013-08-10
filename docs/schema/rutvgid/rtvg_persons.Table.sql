--
-- Описание для таблицы rtvg_persons
--
CREATE TABLE rtvg_persons (
  id int(11) NOT NULL AUTO_INCREMENT,
  fullname char(64) NOT NULL,
  type enum ('actor', 'director', 'writer', 'adapter', 'producer', 'composer', 'operator', 'editor', 'presenter', 'commentator') NOT NULL,
  PRIMARY KEY (id),
  INDEX fullname (fullname)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;
