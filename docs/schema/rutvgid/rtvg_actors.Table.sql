--
-- Описание для таблицы rtvg_actors
--
CREATE TABLE rtvg_actors (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  complete_name varchar(255) NOT NULL,
  f_name varchar(32) NOT NULL,
  m_name varchar(32) NOT NULL,
  s_name varchar(32) NOT NULL,
  rank varchar(3) NOT NULL,
  fullname_en varchar(255) NOT NULL,
  image varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX complete_name (complete_name),
  INDEX f_name (f_name),
  INDEX fullname_en (fullname_en),
  INDEX m_name (m_name),
  INDEX s_name (s_name)
)
ENGINE = INNODB
AUTO_INCREMENT = 52562
AVG_ROW_LENGTH = 89
CHARACTER SET utf8
COLLATE utf8_general_ci;
