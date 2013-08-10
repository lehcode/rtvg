--
-- Описание для таблицы rtvg_users
--
CREATE TABLE rtvg_users (
  id int(11) NOT NULL AUTO_INCREMENT,
  email char(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  display_name char(64) NOT NULL,
  real_name char(64) NOT NULL,
  online bit(1) NOT NULL DEFAULT b'0',
  last_login datetime DEFAULT NULL,
  login_source enum ('site', 'vk', 'fb', 'gg') NOT NULL,
  hash char(255) NOT NULL,
  role enum ('guest', 'member', 'publisher', 'editor', 'admin', 'god') NOT NULL DEFAULT 'guest',
  created datetime NOT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX display_name (display_name),
  INDEX email (email),
  INDEX hash (hash)
)
ENGINE = INNODB
AUTO_INCREMENT = 50
AVG_ROW_LENGTH = 5461
CHARACTER SET utf8
COLLATE utf8_general_ci;
