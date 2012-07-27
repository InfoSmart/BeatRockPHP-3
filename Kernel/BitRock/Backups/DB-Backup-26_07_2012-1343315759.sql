DROP TABLE IF EXISTS site_cache;

CREATE TABLE IF NOT EXISTS `site_cache` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `page` varchar(50) NOT NULL,
  `time` int(100) NOT NULL DEFAULT '12',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Caché';




DROP TABLE IF EXISTS site_config;

CREATE TABLE IF NOT EXISTS `site_config` (
  `var` varchar(100) NOT NULL,
  `result` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Configuración';

INSERT INTO site_config VALUES("site_name","BeatRock");
INSERT INTO site_config VALUES("site_separation","~");
INSERT INTO site_config VALUES("site_slogan","Una nueva versiÃ³n");
INSERT INTO site_config VALUES("site_charset","iso-8859-15");
INSERT INTO site_config VALUES("site_language","es");
INSERT INTO site_config VALUES("site_locale","es_LA");
INSERT INTO site_config VALUES("site_description","");
INSERT INTO site_config VALUES("site_keywords","infosmart, beatrock");
INSERT INTO site_config VALUES("site_status","open");
INSERT INTO site_config VALUES("site_visits","1");
INSERT INTO site_config VALUES("site_favicon","");
INSERT INTO site_config VALUES("site_logo","");
INSERT INTO site_config VALUES("site_version","1.0");
INSERT INTO site_config VALUES("site_revision","de 2012");
INSERT INTO site_config VALUES("site_publisher","InfoSmart");
INSERT INTO site_config VALUES("site_sitemap","false");
INSERT INTO site_config VALUES("site_rss","false");
INSERT INTO site_config VALUES("site_translate","");
INSERT INTO site_config VALUES("site_notes","");
INSERT INTO site_config VALUES("site_type","website");
INSERT INTO site_config VALUES("site_og","");
INSERT INTO site_config VALUES("site_compress","false");
INSERT INTO site_config VALUES("site_recovery","true");
INSERT INTO site_config VALUES("site_header_javascript","");
INSERT INTO site_config VALUES("site_optimized_javascript","false");
INSERT INTO site_config VALUES("cpu_limit","0");
INSERT INTO site_config VALUES("apache_limit","0");
INSERT INTO site_config VALUES("session_alias","beat_");
INSERT INTO site_config VALUES("cookie_alias","beat_");
INSERT INTO site_config VALUES("cookie_duration","300");
INSERT INTO site_config VALUES("cookie_domain","");



DROP TABLE IF EXISTS site_countrys;

CREATE TABLE IF NOT EXISTS `site_countrys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isoNum` smallint(6) DEFAULT NULL,
  `code2` char(2) DEFAULT NULL,
  `code3` char(3) DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=241 DEFAULT CHARSET=latin1;

INSERT INTO site_countrys VALUES("1","4","AF","AFG","Afganistán");
INSERT INTO site_countrys VALUES("2","248","AX","ALA","Islas Gland");
INSERT INTO site_countrys VALUES("3","8","AL","ALB","Albania");
INSERT INTO site_countrys VALUES("4","276","DE","DEU","Alemania");
INSERT INTO site_countrys VALUES("5","20","AD","AND","Andorra");
INSERT INTO site_countrys VALUES("6","24","AO","AGO","Angola");
INSERT INTO site_countrys VALUES("7","660","AI","AIA","Anguilla");
INSERT INTO site_countrys VALUES("8","10","AQ","ATA","Antártida");
INSERT INTO site_countrys VALUES("9","28","AG","ATG","Antigua y Barbuda");
INSERT INTO site_countrys VALUES("10","530","AN","ANT","Antillas Holandesas");
INSERT INTO site_countrys VALUES("11","682","SA","SAU","Arabia Saudí");
INSERT INTO site_countrys VALUES("12","12","DZ","DZA","Argelia");
INSERT INTO site_countrys VALUES("13","32","AR","ARG","Argentina");
INSERT INTO site_countrys VALUES("14","51","AM","ARM","Armenia");
INSERT INTO site_countrys VALUES("15","533","AW","ABW","Aruba");
INSERT INTO site_countrys VALUES("16","36","AU","AUS","Australia");
INSERT INTO site_countrys VALUES("17","40","AT","AUT","Austria");
INSERT INTO site_countrys VALUES("18","31","AZ","AZE","Azerbaiyán");
INSERT INTO site_countrys VALUES("19","44","BS","BHS","Bahamas");
INSERT INTO site_countrys VALUES("20","48","BH","BHR","Bahréin");
INSERT INTO site_countrys VALUES("21","50","BD","BGD","Bangladesh");
INSERT INTO site_countrys VALUES("22","52","BB","BRB","Barbados");
INSERT INTO site_countrys VALUES("23","112","BY","BLR","Bielorrusia");
INSERT INTO site_countrys VALUES("24","56","BE","BEL","Bélgica");
INSERT INTO site_countrys VALUES("25","84","BZ","BLZ","Belice");
INSERT INTO site_countrys VALUES("26","204","BJ","BEN","Benin");
INSERT INTO site_countrys VALUES("27","60","BM","BMU","Bermudas");
INSERT INTO site_countrys VALUES("28","64","BT","BTN","Bhután");
INSERT INTO site_countrys VALUES("29","68","BO","BOL","Bolivia");
INSERT INTO site_countrys VALUES("30","70","BA","BIH","Bosnia y Herzegovina");
INSERT INTO site_countrys VALUES("31","72","BW","BWA","Botsuana");
INSERT INTO site_countrys VALUES("32","74","BV","BVT","Isla Bouvet");
INSERT INTO site_countrys VALUES("33","76","BR","BRA","Brasil");
INSERT INTO site_countrys VALUES("34","96","BN","BRN","Brunéi");
INSERT INTO site_countrys VALUES("35","100","BG","BGR","Bulgaria");
INSERT INTO site_countrys VALUES("36","854","BF","BFA","Burkina Faso");
INSERT INTO site_countrys VALUES("37","108","BI","BDI","Burundi");
INSERT INTO site_countrys VALUES("38","132","CV","CPV","Cabo Verde");
INSERT INTO site_countrys VALUES("39","136","KY","CYM","Islas Caimán");
INSERT INTO site_countrys VALUES("40","116","KH","KHM","Camboya");
INSERT INTO site_countrys VALUES("41","120","CM","CMR","Camerún");
INSERT INTO site_countrys VALUES("42","124","CA","CAN","Canadá");
INSERT INTO site_countrys VALUES("43","140","CF","CAF","República Centroafricana");
INSERT INTO site_countrys VALUES("44","148","TD","TCD","Chad");
INSERT INTO site_countrys VALUES("45","203","CZ","CZE","República Checa");
INSERT INTO site_countrys VALUES("46","152","CL","CHL","Chile");
INSERT INTO site_countrys VALUES("47","156","CN","CHN","China");
INSERT INTO site_countrys VALUES("48","196","CY","CYP","Chipre");
INSERT INTO site_countrys VALUES("49","162","CX","CXR","Isla de Navidad");
INSERT INTO site_countrys VALUES("50","336","VA","VAT","Ciudad del Vaticano");
INSERT INTO site_countrys VALUES("51","166","CC","CCK","Islas Cocos");
INSERT INTO site_countrys VALUES("52","170","CO","COL","Colombia");
INSERT INTO site_countrys VALUES("53","174","KM","COM","Comoras");
INSERT INTO site_countrys VALUES("54","180","CD","COD","República Democrática del Congo");
INSERT INTO site_countrys VALUES("55","178","CG","COG","Congo");
INSERT INTO site_countrys VALUES("56","184","CK","COK","Islas Cook");
INSERT INTO site_countrys VALUES("57","408","KP","PRK","Corea del Norte");
INSERT INTO site_countrys VALUES("58","410","KR","KOR","Corea del Sur");
INSERT INTO site_countrys VALUES("59","384","CI","CIV","Costa de Marfil");
INSERT INTO site_countrys VALUES("60","188","CR","CRI","Costa Rica");
INSERT INTO site_countrys VALUES("61","191","HR","HRV","Croacia");
INSERT INTO site_countrys VALUES("62","192","CU","CUB","Cuba");
INSERT INTO site_countrys VALUES("63","208","DK","DNK","Dinamarca");
INSERT INTO site_countrys VALUES("64","212","DM","DMA","Dominica");
INSERT INTO site_countrys VALUES("65","214","DO","DOM","República Dominicana");
INSERT INTO site_countrys VALUES("66","218","EC","ECU","Ecuador");
INSERT INTO site_countrys VALUES("67","818","EG","EGY","Egipto");
INSERT INTO site_countrys VALUES("68","222","SV","SLV","El Salvador");
INSERT INTO site_countrys VALUES("69","784","AE","ARE","Emiratos Árabes Unidos");
INSERT INTO site_countrys VALUES("70","232","ER","ERI","Eritrea");
INSERT INTO site_countrys VALUES("71","703","SK","SVK","Eslovaquia");
INSERT INTO site_countrys VALUES("72","705","SI","SVN","Eslovenia");
INSERT INTO site_countrys VALUES("73","724","ES","ESP","España");
INSERT INTO site_countrys VALUES("74","581","UM","UMI","Islas ultramarinas de Estados Unidos");
INSERT INTO site_countrys VALUES("75","840","US","USA","Estados Unidos");
INSERT INTO site_countrys VALUES("76","233","EE","EST","Estonia");
INSERT INTO site_countrys VALUES("77","231","ET","ETH","Etiopía");
INSERT INTO site_countrys VALUES("78","234","FO","FRO","Islas Feroe");
INSERT INTO site_countrys VALUES("79","608","PH","PHL","Filipinas");
INSERT INTO site_countrys VALUES("80","246","FI","FIN","Finlandia");
INSERT INTO site_countrys VALUES("81","242","FJ","FJI","Fiyi");
INSERT INTO site_countrys VALUES("82","250","FR","FRA","Francia");
INSERT INTO site_countrys VALUES("83","266","GA","GAB","Gabón");
INSERT INTO site_countrys VALUES("84","270","GM","GMB","Gambia");
INSERT INTO site_countrys VALUES("85","268","GE","GEO","Georgia");
INSERT INTO site_countrys VALUES("86","239","GS","SGS","Islas Georgias del Sur y Sandwich del Sur");
INSERT INTO site_countrys VALUES("87","288","GH","GHA","Ghana");
INSERT INTO site_countrys VALUES("88","292","GI","GIB","Gibraltar");
INSERT INTO site_countrys VALUES("89","308","GD","GRD","Granada");
INSERT INTO site_countrys VALUES("90","300","GR","GRC","Grecia");
INSERT INTO site_countrys VALUES("91","304","GL","GRL","Groenlandia");
INSERT INTO site_countrys VALUES("92","312","GP","GLP","Guadalupe");
INSERT INTO site_countrys VALUES("93","316","GU","GUM","Guam");
INSERT INTO site_countrys VALUES("94","320","GT","GTM","Guatemala");
INSERT INTO site_countrys VALUES("95","254","GF","GUF","Guayana Francesa");
INSERT INTO site_countrys VALUES("96","324","GN","GIN","Guinea");
INSERT INTO site_countrys VALUES("97","226","GQ","GNQ","Guinea Ecuatorial");
INSERT INTO site_countrys VALUES("98","624","GW","GNB","Guinea-Bissau");
INSERT INTO site_countrys VALUES("99","328","GY","GUY","Guyana");
INSERT INTO site_countrys VALUES("100","332","HT","HTI","Haití");
INSERT INTO site_countrys VALUES("101","334","HM","HMD","Islas Heard y McDonald");
INSERT INTO site_countrys VALUES("102","340","HN","HND","Honduras");
INSERT INTO site_countrys VALUES("103","344","HK","HKG","Hong Kong");
INSERT INTO site_countrys VALUES("104","348","HU","HUN","Hungría");
INSERT INTO site_countrys VALUES("105","356","IN","IND","India");
INSERT INTO site_countrys VALUES("106","360","ID","IDN","Indonesia");
INSERT INTO site_countrys VALUES("107","364","IR","IRN","Irán");
INSERT INTO site_countrys VALUES("108","368","IQ","IRQ","Iraq");
INSERT INTO site_countrys VALUES("109","372","IE","IRL","Irlanda");
INSERT INTO site_countrys VALUES("110","352","IS","ISL","Islandia");
INSERT INTO site_countrys VALUES("111","376","IL","ISR","Israel");
INSERT INTO site_countrys VALUES("112","380","IT","ITA","Italia");
INSERT INTO site_countrys VALUES("113","388","JM","JAM","Jamaica");
INSERT INTO site_countrys VALUES("114","392","JP","JPN","Japón");
INSERT INTO site_countrys VALUES("115","400","JO","JOR","Jordania");
INSERT INTO site_countrys VALUES("116","398","KZ","KAZ","Kazajstán");
INSERT INTO site_countrys VALUES("117","404","KE","KEN","Kenia");
INSERT INTO site_countrys VALUES("118","417","KG","KGZ","Kirguistán");
INSERT INTO site_countrys VALUES("119","296","KI","KIR","Kiribati");
INSERT INTO site_countrys VALUES("120","414","KW","KWT","Kuwait");
INSERT INTO site_countrys VALUES("121","418","LA","LAO","Laos");
INSERT INTO site_countrys VALUES("122","426","LS","LSO","Lesotho");
INSERT INTO site_countrys VALUES("123","428","LV","LVA","Letonia");
INSERT INTO site_countrys VALUES("124","422","LB","LBN","Líbano");
INSERT INTO site_countrys VALUES("125","430","LR","LBR","Liberia");
INSERT INTO site_countrys VALUES("126","434","LY","LBY","Libia");
INSERT INTO site_countrys VALUES("127","438","LI","LIE","Liechtenstein");
INSERT INTO site_countrys VALUES("128","440","LT","LTU","Lituania");
INSERT INTO site_countrys VALUES("129","442","LU","LUX","Luxemburgo");
INSERT INTO site_countrys VALUES("130","446","MO","MAC","Macao");
INSERT INTO site_countrys VALUES("131","807","MK","MKD","ARY Macedonia");
INSERT INTO site_countrys VALUES("132","450","MG","MDG","Madagascar");
INSERT INTO site_countrys VALUES("133","458","MY","MYS","Malasia");
INSERT INTO site_countrys VALUES("134","454","MW","MWI","Malawi");
INSERT INTO site_countrys VALUES("135","462","MV","MDV","Maldivas");
INSERT INTO site_countrys VALUES("136","466","ML","MLI","Malí");
INSERT INTO site_countrys VALUES("137","470","MT","MLT","Malta");
INSERT INTO site_countrys VALUES("138","238","FK","FLK","Islas Malvinas");
INSERT INTO site_countrys VALUES("139","580","MP","MNP","Islas Marianas del Norte");
INSERT INTO site_countrys VALUES("140","504","MA","MAR","Marruecos");
INSERT INTO site_countrys VALUES("141","584","MH","MHL","Islas Marshall");
INSERT INTO site_countrys VALUES("142","474","MQ","MTQ","Martinica");
INSERT INTO site_countrys VALUES("143","480","MU","MUS","Mauricio");
INSERT INTO site_countrys VALUES("144","478","MR","MRT","Mauritania");
INSERT INTO site_countrys VALUES("145","175","YT","MYT","Mayotte");
INSERT INTO site_countrys VALUES("146","484","MX","MEX","México");
INSERT INTO site_countrys VALUES("147","583","FM","FSM","Micronesia");
INSERT INTO site_countrys VALUES("148","498","MD","MDA","Moldavia");
INSERT INTO site_countrys VALUES("149","492","MC","MCO","Mónaco");
INSERT INTO site_countrys VALUES("150","496","MN","MNG","Mongolia");
INSERT INTO site_countrys VALUES("151","500","MS","MSR","Montserrat");
INSERT INTO site_countrys VALUES("152","508","MZ","MOZ","Mozambique");
INSERT INTO site_countrys VALUES("153","104","MM","MMR","Myanmar");
INSERT INTO site_countrys VALUES("154","516","NA","NAM","Namibia");
INSERT INTO site_countrys VALUES("155","520","NR","NRU","Nauru");
INSERT INTO site_countrys VALUES("156","524","NP","NPL","Nepal");
INSERT INTO site_countrys VALUES("157","558","NI","NIC","Nicaragua");
INSERT INTO site_countrys VALUES("158","562","NE","NER","Níger");
INSERT INTO site_countrys VALUES("159","566","NG","NGA","Nigeria");
INSERT INTO site_countrys VALUES("160","570","NU","NIU","Niue");
INSERT INTO site_countrys VALUES("161","574","NF","NFK","Isla Norfolk");
INSERT INTO site_countrys VALUES("162","578","NO","NOR","Noruega");
INSERT INTO site_countrys VALUES("163","540","NC","NCL","Nueva Caledonia");
INSERT INTO site_countrys VALUES("164","554","NZ","NZL","Nueva Zelanda");
INSERT INTO site_countrys VALUES("165","512","OM","OMN","Omán");
INSERT INTO site_countrys VALUES("166","528","NL","NLD","Países Bajos");
INSERT INTO site_countrys VALUES("167","586","PK","PAK","Pakistán");
INSERT INTO site_countrys VALUES("168","585","PW","PLW","Palau");
INSERT INTO site_countrys VALUES("169","275","PS","PSE","Palestina");
INSERT INTO site_countrys VALUES("170","591","PA","PAN","Panamá");
INSERT INTO site_countrys VALUES("171","598","PG","PNG","Papúa Nueva Guinea");
INSERT INTO site_countrys VALUES("172","600","PY","PRY","Paraguay");
INSERT INTO site_countrys VALUES("173","604","PE","PER","Perú");
INSERT INTO site_countrys VALUES("174","612","PN","PCN","Islas Pitcairn");
INSERT INTO site_countrys VALUES("175","258","PF","PYF","Polinesia Francesa");
INSERT INTO site_countrys VALUES("176","616","PL","POL","Polonia");
INSERT INTO site_countrys VALUES("177","620","PT","PRT","Portugal");
INSERT INTO site_countrys VALUES("178","630","PR","PRI","Puerto Rico");
INSERT INTO site_countrys VALUES("179","634","QA","QAT","Qatar");
INSERT INTO site_countrys VALUES("180","826","GB","GBR","Reino Unido");
INSERT INTO site_countrys VALUES("181","638","RE","REU","Reunión");
INSERT INTO site_countrys VALUES("182","646","RW","RWA","Ruanda");
INSERT INTO site_countrys VALUES("183","642","RO","ROU","Rumania");
INSERT INTO site_countrys VALUES("184","643","RU","RUS","Rusia");
INSERT INTO site_countrys VALUES("185","732","EH","ESH","Sahara Occidental");
INSERT INTO site_countrys VALUES("186","90","SB","SLB","Islas Salomón");
INSERT INTO site_countrys VALUES("187","882","WS","WSM","Samoa");
INSERT INTO site_countrys VALUES("188","16","AS","ASM","Samoa Americana");
INSERT INTO site_countrys VALUES("189","659","KN","KNA","San Cristóbal y Nevis");
INSERT INTO site_countrys VALUES("190","674","SM","SMR","San Marino");
INSERT INTO site_countrys VALUES("191","666","PM","SPM","San Pedro y Miquelón");
INSERT INTO site_countrys VALUES("192","670","VC","VCT","San Vicente y las Granadinas");
INSERT INTO site_countrys VALUES("193","654","SH","SHN","Santa Helena");
INSERT INTO site_countrys VALUES("194","662","LC","LCA","Santa Lucía");
INSERT INTO site_countrys VALUES("195","678","ST","STP","Santo Tomé y Príncipe");
INSERT INTO site_countrys VALUES("196","686","SN","SEN","Senegal");
INSERT INTO site_countrys VALUES("197","891","CS","SCG","Serbia y Montenegro");
INSERT INTO site_countrys VALUES("198","690","SC","SYC","Seychelles");
INSERT INTO site_countrys VALUES("199","694","SL","SLE","Sierra Leona");
INSERT INTO site_countrys VALUES("200","702","SG","SGP","Singapur");
INSERT INTO site_countrys VALUES("201","760","SY","SYR","Siria");
INSERT INTO site_countrys VALUES("202","706","SO","SOM","Somalia");
INSERT INTO site_countrys VALUES("203","144","LK","LKA","Sri Lanka");
INSERT INTO site_countrys VALUES("204","748","SZ","SWZ","Suazilandia");
INSERT INTO site_countrys VALUES("205","710","ZA","ZAF","Sudáfrica");
INSERT INTO site_countrys VALUES("206","736","SD","SDN","Sudán");
INSERT INTO site_countrys VALUES("207","752","SE","SWE","Suecia");
INSERT INTO site_countrys VALUES("208","756","CH","CHE","Suiza");
INSERT INTO site_countrys VALUES("209","740","SR","SUR","Surinam");
INSERT INTO site_countrys VALUES("210","744","SJ","SJM","Svalbard y Jan Mayen");
INSERT INTO site_countrys VALUES("211","764","TH","THA","Tailandia");
INSERT INTO site_countrys VALUES("212","158","TW","TWN","Taiwán");
INSERT INTO site_countrys VALUES("213","834","TZ","TZA","Tanzania");
INSERT INTO site_countrys VALUES("214","762","TJ","TJK","Tayikistán");
INSERT INTO site_countrys VALUES("215","86","IO","IOT","Territorio Británico del Océano Índico");
INSERT INTO site_countrys VALUES("216","260","TF","ATF","Territorios Australes Franceses");
INSERT INTO site_countrys VALUES("217","626","TL","TLS","Timor Oriental");
INSERT INTO site_countrys VALUES("218","768","TG","TGO","Togo");
INSERT INTO site_countrys VALUES("219","772","TK","TKL","Tokelau");
INSERT INTO site_countrys VALUES("220","776","TO","TON","Tonga");
INSERT INTO site_countrys VALUES("221","780","TT","TTO","Trinidad y Tobago");
INSERT INTO site_countrys VALUES("222","788","TN","TUN","Túnez");
INSERT INTO site_countrys VALUES("223","796","TC","TCA","Islas Turcas y Caicos");
INSERT INTO site_countrys VALUES("224","795","TM","TKM","Turkmenistán");
INSERT INTO site_countrys VALUES("225","792","TR","TUR","Turquía");
INSERT INTO site_countrys VALUES("226","798","TV","TUV","Tuvalu");
INSERT INTO site_countrys VALUES("227","804","UA","UKR","Ucrania");
INSERT INTO site_countrys VALUES("228","800","UG","UGA","Uganda");
INSERT INTO site_countrys VALUES("229","858","UY","URY","Uruguay");
INSERT INTO site_countrys VALUES("230","860","UZ","UZB","Uzbekistán");
INSERT INTO site_countrys VALUES("231","548","VU","VUT","Vanuatu");
INSERT INTO site_countrys VALUES("232","862","VE","VEN","Venezuela");
INSERT INTO site_countrys VALUES("233","704","VN","VNM","Vietnam");
INSERT INTO site_countrys VALUES("234","92","VG","VGB","Islas Vírgenes Británicas");
INSERT INTO site_countrys VALUES("235","850","VI","VIR","Islas Vírgenes de los Estados Unidos");
INSERT INTO site_countrys VALUES("236","876","WF","WLF","Wallis y Futuna");
INSERT INTO site_countrys VALUES("237","887","YE","YEM","Yemen");
INSERT INTO site_countrys VALUES("238","262","DJ","DJI","Yibuti");
INSERT INTO site_countrys VALUES("239","894","ZM","ZMB","Zambia");
INSERT INTO site_countrys VALUES("240","716","ZW","ZWE","Zimbabue");



DROP TABLE IF EXISTS site_errors;

CREATE TABLE IF NOT EXISTS `site_errors` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL DEFAULT '000',
  `title` varchar(100) NOT NULL,
  `response` text NOT NULL,
  `file` varchar(300) NOT NULL,
  `function` varchar(100) NOT NULL,
  `line` int(100) NOT NULL,
  `out_file` varchar(300) NOT NULL,
  `more` text NOT NULL,
  `date` int(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Errores producidos';




DROP TABLE IF EXISTS site_logs;

CREATE TABLE IF NOT EXISTS `site_logs` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `logs` text NOT NULL,
  `phpid` varchar(300) NOT NULL,
  `path` varchar(300) NOT NULL,
  `date` int(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Logs guardados';




DROP TABLE IF EXISTS site_maps;

CREATE TABLE IF NOT EXISTS `site_maps` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `page` varchar(100) NOT NULL,
  `lastmod` varchar(50) NOT NULL,
  `changefrec` enum('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'always',
  `priority` varchar(10) NOT NULL DEFAULT '1.0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='Mapa del sitio';

INSERT INTO site_maps VALUES("1","index","","always","1.0");



DROP TABLE IF EXISTS site_news;

CREATE TABLE IF NOT EXISTS `site_news` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `sub_content` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(300) NOT NULL,
  `images` varchar(1000) NOT NULL,
  `author` varchar(60) NOT NULL,
  `date` varchar(100) NOT NULL,
  `comments` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Noticias';




DROP TABLE IF EXISTS site_timers;

CREATE TABLE IF NOT EXISTS `site_timers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL,
  `time` int(100) NOT NULL,
  `nexttime` int(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='Cronometros';

INSERT INTO site_timers VALUES("1","optimize_db","1440","1343402142");
INSERT INTO site_timers VALUES("2","backup_db","1440","1343315739");
INSERT INTO site_timers VALUES("3","maintenance_db","1440","1343315739");
INSERT INTO site_timers VALUES("4","maintenance_backups","2880","1343317179");



DROP TABLE IF EXISTS site_visits;

CREATE TABLE IF NOT EXISTS `site_visits` (
  `ip` varchar(100) NOT NULL,
  `host` varchar(150) NOT NULL,
  `agent` text NOT NULL,
  `browser` varchar(100) NOT NULL,
  `referer` varchar(300) NOT NULL,
  `phpid` varchar(300) NOT NULL,
  `type` enum('desktop','mobile','bot') NOT NULL DEFAULT 'desktop',
  `date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Visitas por IP';

INSERT INTO site_visits VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/Setup/step2.php","dc66csaed35qufme71o0vuir71","desktop","1343314205");



DROP TABLE IF EXISTS site_visits_total;

CREATE TABLE IF NOT EXISTS `site_visits_total` (
  `ip` varchar(100) NOT NULL,
  `host` varchar(150) NOT NULL,
  `agent` text NOT NULL,
  `browser` varchar(100) NOT NULL,
  `path` varchar(300) NOT NULL,
  `referer` varchar(300) NOT NULL,
  `phpid` varchar(300) NOT NULL,
  `type` enum('desktop','mobile','bot') NOT NULL DEFAULT 'desktop',
  `date` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Visitas totales';

INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/Setup/step3.php","http://localhost/beat/Setup/step2.php","dc66csaed35qufme71o0vuir71","desktop","1343314205");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/Setup/step4.php","http://localhost/beat/Setup/step3.php","dc66csaed35qufme71o0vuir71","desktop","1343314225");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/Setup/actions/save_step4.php","http://localhost/beat/Setup/step4.php","dc66csaed35qufme71o0vuir71","desktop","1343314299");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/Setup/finish.php","http://localhost/beat/Setup/step4.php","dc66csaed35qufme71o0vuir71","desktop","1343314299");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343314304");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343314661");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343314689");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343314708");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343314722");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343314739");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315197");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315225");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315411");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315421");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315439");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315453");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315486");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315502");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315513");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315560");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315741");
INSERT INTO site_visits_total VALUES("::1","sbravo67","Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11","Google Chrome","http://localhost/beat/","","dc66csaed35qufme71o0vuir71","desktop","1343315758");



DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(300) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(48) NOT NULL,
  `photo` varchar(300) NOT NULL,
  `rank` int(2) NOT NULL DEFAULT '1',
  `status` varchar(48) NOT NULL,
  `birthday` varchar(100) NOT NULL,
  `account_birthday` varchar(100) NOT NULL,
  `lastaccess` varchar(100) NOT NULL,
  `lastonline` varchar(100) NOT NULL DEFAULT '0',
  `ip_address` varchar(100) NOT NULL,
  `reg_ip_address` varchar(100) NOT NULL,
  `newsletter` enum('0','1') NOT NULL DEFAULT '1',
  `emailVerified` enum('0','1') NOT NULL DEFAULT '0',
  `browser` varchar(100) NOT NULL,
  `agent` varchar(500) NOT NULL,
  `os` varchar(100) NOT NULL,
  `lasthost` varchar(200) NOT NULL,
  `country` varchar(3) NOT NULL,
  `cookie_session` varchar(60) NOT NULL,
  `user_hash` varchar(80) NOT NULL,
  `service` varchar(50) NOT NULL,
  `banned` enum('true','false') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Usuarios';




DROP TABLE IF EXISTS users_services;

CREATE TABLE IF NOT EXISTS `users_services` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `identification` varchar(100) NOT NULL,
  `service` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `user_hash` varchar(80) NOT NULL,
  `info` text NOT NULL,
  `date` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Servicios de los usuarios';




DROP TABLE IF EXISTS wordsfilter;

CREATE TABLE IF NOT EXISTS `wordsfilter` (
  `word` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Filtro de Palabras';

INSERT INTO wordsfilter VALUES("puto");
INSERT INTO wordsfilter VALUES("puta");
INSERT INTO wordsfilter VALUES("pendejo");
INSERT INTO wordsfilter VALUES("pendeja");
INSERT INTO wordsfilter VALUES("pito");
INSERT INTO wordsfilter VALUES("verga");
INSERT INTO wordsfilter VALUES("pene");
INSERT INTO wordsfilter VALUES("vagina");
INSERT INTO wordsfilter VALUES("cabron");
INSERT INTO wordsfilter VALUES("cabrona");
INSERT INTO wordsfilter VALUES("chingada");
INSERT INTO wordsfilter VALUES("chingados");
INSERT INTO wordsfilter VALUES("mierda");
INSERT INTO wordsfilter VALUES("popo");
INSERT INTO wordsfilter VALUES("shit");
INSERT INTO wordsfilter VALUES("motherfucker");
INSERT INTO wordsfilter VALUES("motherfuck");
INSERT INTO wordsfilter VALUES("p.u.t.o");
INSERT INTO wordsfilter VALUES("p.u.t.h.o");
INSERT INTO wordsfilter VALUES("putho");
INSERT INTO wordsfilter VALUES("mamawebo");
INSERT INTO wordsfilter VALUES("mamahuevo");
INSERT INTO wordsfilter VALUES("mmg");
INSERT INTO wordsfilter VALUES("putha");
INSERT INTO wordsfilter VALUES("putho");
INSERT INTO wordsfilter VALUES("pija");
INSERT INTO wordsfilter VALUES("dick");
INSERT INTO wordsfilter VALUES("bitch");
INSERT INTO wordsfilter VALUES("b!tch");
INSERT INTO wordsfilter VALUES("dumbass");
INSERT INTO wordsfilter VALUES("joto");
INSERT INTO wordsfilter VALUES("jotho");
INSERT INTO wordsfilter VALUES("asshole");
INSERT INTO wordsfilter VALUES("culo");



