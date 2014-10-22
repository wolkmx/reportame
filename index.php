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
    'reportame',
    'reportame'
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

/*Ruta para recibir la peticion ajax para mostrar el formulario de registro*/
$f3->route('POST /login [ajax]',
	function($f3) {
		echo Template::instance()->render('loginform.html');
	}
);

/*Ruta para recibir la peticion ajax para iniciar sesion*/
/*el valor extra [ajax] se utiliza para indicar que es una peticion ajax, si no es una peticion ajax no entrara a esta url, es decir si se coloca iniciarsesion en el navegador no entrara*/
/*Se utiliza use ($db) para que la funcion pueda acceder a la conexion de base de datos definida previamente*/
$f3->route('POST /iniciarsesion [ajax]',
	function($f3) use ($db){
		//Se obtienen los datos enviados por el formulario
		$formulario = $f3->get("REQUEST");
		
		/*Se consulta la base de datos para ver si existe un usuario con este email y clave*/
		$usuario = $db->exec('SELECT * FROM usuario WHERE alias LIKE "'.$formulario['user'].'" OR email LIKE "'.$formulario['user'].'" AND password="'.$formulario['clave'].'"');
		
		$f3->set('flash',Null);
		
		if(count($usuario)){
			//Si encuentra el usuario lo deberia redireccionar a su panel de control.
			echo "encontrado";
		}else{
		//Si no encuentra el usuario volvera a mostrar el formulario con un mensaje de advertencia llamado flash
			$f3->set('flash','Usuario o clave incorrecta por favor ingrese los datos nuevamente');
			echo Template::instance()->render('loginform.html');

		}
		
		//Se obtiene el  mapeo del objeto de tipo usuario desde la base de datos
		/*$user=new DB\SQL\Mapper($db,'usuario');
		$user->load(array('usuario=?','tarzan'));*/
		
		
		/*echo "<pre>";
		print_r($formulario);
		echo "</pre>";*/
		//echo Template::instance()->render('loginform.html');
	}
);

/*Ruta para recibir la peticion ajax para registrar el usuario*/
$f3->route('POST /registrousuario [ajax]',
	function($f3) {
		$formulario = $f3->get("REQUEST");
		echo "<pre>";
		print_r($formulario);
		echo "</pre>";
		//echo Template::instance()->render('loginform.html');
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
