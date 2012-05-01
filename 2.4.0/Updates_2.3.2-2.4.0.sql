INSERT INTO {DB_ALIAS}site_config (
var,
result
)
VALUES (
'site_type',  'website'
);

INSERT INTO {DB_ALIAS}site_config (
var,
result
)
VALUES (
'site_og',  ''
);

INSERT INTO {DB_ALIAS}site_config (
var,
result
)
VALUES (
'site_locale',  'es_MX'
);

ALTER TABLE {DB_ALIAS}site_cache ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}site_countrys ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}site_maps ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}site_news ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}site_pages ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}site_timers ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}site_translate ENGINE = InnoDB;
ALTER TABLE {DB_ALIAS}wordsfilter ENGINE = InnoDB;