--
-- Описание для таблицы rtvg_directors
--
CREATE TABLE rtvg_directors (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  complete_name varchar(255) NOT NULL,
  f_name varchar(128) NOT NULL,
  m_name varchar(128) NOT NULL,
  s_name varchar(128) NOT NULL,
  rank varchar(24) NOT NULL,
  fullname_en varchar(128) NOT NULL,
  photo varchar(255) NOT NULL,
  image varchar(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX f_name (f_name),
  INDEX m_name (m_name),
  INDEX s_name (s_name)
)
ENGINE = INNODB
AUTO_INCREMENT = 8154
AVG_ROW_LENGTH = 196
CHARACTER SET utf8
COLLATE utf8_general_ci;
