<?php

$f3=require('lib/base.php');

$f3->set('DEBUG',3);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

$f3->config('config.ini');

//Codificacion
$f3->set('ENCODING','UTF8');

//Se declara la conexion a la base de datos
$db=new DB\SQL(
    'mysql:host=localhost;port=3306;dbname=reportame',
    'root',
    'sqlnetos'
);

//Admin Add
$f3->route('GET /user/new',
  function($f3) {
  }
);

//Home page
$f3->route('GET /',
    function($f3) use  ($db) {
	//Se indica que el contenido del template lo tomara de home.html
	$f3->set('content','home.html');
	
	//Se realiza una consulta a la base de datos
	//$f3->set('result',$db->exec('SELECT alias FROM usuario'));
	
	$user=new DB\SQL\Mapper($db,'usuario');
	$user->load(array('alias=?','wolkmx'));
	
	$f3->set('nombre',$user);
	
	
	echo Template::instance()->render('layout.html');
	
	
    }
);

/*
$f3->route('GET /',
	function($f3) {
		$classes=array(
			'Base'=>
				array(
					'hash',
					'json',
					'session'
				),
			'Cache'=>
				array(
					'apc',
					'memcache',
					'wincache',
					'xcache'
				),
			'DB\SQL'=>
				array(
					'pdo',
					'pdo_dblib',
					'pdo_mssql',
					'pdo_mysql',
					'pdo_odbc',
					'pdo_pgsql',
					'pdo_sqlite',
					'pdo_sqlsrv'
				),
			'DB\Jig'=>
				array('json'),
			'DB\Mongo'=>
				array(
					'json',
					'mongo'
				),
			'Auth'=>
				array('ldap','pdo'),
			'Bcrypt'=>
				array(
					'mcrypt',
					'openssl'
				),
			'Image'=>
				array('gd'),
			'Lexicon'=>
				array('iconv'),
			'SMTP'=>
				array('openssl'),
			'Web'=>
				array('curl','openssl','simplexml'),
			'Web\Geo'=>
				array('geoip','json'),
			'Web\OpenID'=>
				array('json','simplexml'),
			'Web\Pingback'=>
				array('dom','xmlrpc')
		);
		$f3->set('classes',$classes);
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET /userref',
	function($f3) {
		$f3->set('content','userref.htm');
		echo View::instance()->render('layout.htm');
	}
);
*/


$f3->run();
