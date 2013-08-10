--
-- Описание для таблицы rtvg_ads_stats
--
CREATE TABLE rtvg_ads_stats (
  ad int(11) NOT NULL,
  hits int(11) NOT NULL DEFAULT 0,
  e_cpm double NOT NULL DEFAULT 0,
  PRIMARY KEY (ad)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
