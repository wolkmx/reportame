<?php

$f3=require('lib/base.php');

$f3->set('DEBUG',3);
if ((float)PCRE_VERSION<7.9)
	trigger_error('PCRE version is out of date');

$f3->config('config.ini');

//Codificacion
$f3->set('ENCODING','UTF8');
//Se inicializa el cache
$f3->set('CACHE','memcache=localhost');

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
$f3->route('GET @home: /',
    function($f3) use  ($db) {
	//Se indica que el contenido del template lo tomara de home.html
	$f3->set('content','home.html');
	
	/*@todo crear if inicio sesion para que vaya a su panel*/
	
	//Se realiza una consulta a la base de datos
	//$f3->set('result',$db->exec('SELECT alias FROM usuario'));
	
	$user=new DB\SQL\Mapper($db,'Usuario');
	$user->load(array('alias=?','wolkmx'));
	
	/*Se obtiene el arreglo de la sesion para saber si existe el key user*/
	$sesion = $f3->get('SESSION');
	/*Si no existe se declara nula*/
	if(!array_key_exists("user",$sesion)){
		$f3->set('usuario','');
	}
	
	/*echo "---<pre>";
	print_r($sesion);
	echo "</pre>---";*/
	
	$f3->set('nombre',$user);
	
	//Consulta a la base de datos para obtener una lista con los eventos
	
	$fechainicial = date ("Y-m-d H:i:s",time());
	$fechaanterior = date('Y-m-d H:i:s', strtotime('-1 day', strtotime( date("Y-m-d H:i:s",time()) )));
	
	//Se hace una consulta para recuperar el evento, el perfil, la categoria y la enfermedad, asi como el usuario
	//Esta consulta se debe simplificar solo obtener la informacion del evento y luego con ajax hacer una consulta especifica cuando se de clic en el evento.
	$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial.'")');
	
	$f3->set('eventosrecientes',$eventos);
	
	//echo count($eventos);
	/*foreach($eventos as $evento):
		echo $evento['idEvento'];
	endforeach;
	
	
	echo "<pre>";
	print_r($eventos);
	echo "</pre>";
	die();*/

	echo Template::instance()->render('layout.html');
	
	
    }
);

/*Ruta para recibir la peticion ajax para mostrar el formulario de registro*/
$f3->route('POST @login: /login [ajax]',
	function($f3) {
	
		//Se declara la variable flash como nula para que no aparesca la primera vez que se muestra el formulario pero si la segunda vez si es que hay un error.
		$f3->set('flash',null);
		echo Template::instance()->render('loginform.html');
	}
);

/*Ruta para cerrar la seccion quienes somos */
$f3->route('GET  @descarga: /descarga',
	function($f3) {
	
		//Se indica que el contenido del template lo tomara de home.html
		$f3->set('content','descargar.html');
		
		/*Se obtiene el arreglo de la sesion para saber si existe el key user*/
	$sesion = $f3->get('SESSION');
	/*Si no existe se declara nula*/
	if(!array_key_exists("user",$sesion)){
		$f3->set('usuario','');
	}
		
		echo Template::instance()->render('layout.html');
	}
);

/*Ruta para cerrar la seccion quienes somos */
$f3->route('GET  @email: /email',
	function($f3) {
	
		//Se indica que el contenido del template lo tomara de home.html
		$f3->set('content','email.html');
		
		/*Se obtiene el arreglo de la sesion para saber si existe el key user*/
	$sesion = $f3->get('SESSION');
	/*Si no existe se declara nula*/
	if(!array_key_exists("user",$sesion)){
		$f3->set('usuario','');
	}
		
		echo Template::instance()->render('layout.html');
	}
);

/*Ruta para recibir la peticion ajax para iniciar sesion*/
/*el valor extra [ajax] se utiliza para indicar que es una peticion ajax, si no es una peticion ajax no entrara a esta url, es decir si se coloca iniciarsesion en el navegador no entrara*/
/*Se utiliza use ($db) para que la funcion pueda acceder a la conexion de base de datos definida previamente*/
$f3->route('POST @iniciarsesion: /iniciarsesion [ajax]',
	function($f3) use ($db){
		//Se obtienen los datos enviados por el formulario
		$formulario = $f3->get("REQUEST");
		
		/*echo "<pre>";
		print_r($formulario);
		echo "</pre>";*/
		
		/*Se consulta la base de datos para ver si existe un usuario con este email y clave*/
		$usuario = $db->exec('SELECT * FROM Usuario WHERE (alias LIKE "'.$formulario['user'].'" OR email LIKE "'.$formulario['user'].'") AND password="'.$formulario['clave'].'"');
		
		$f3->set('flash',Null);
		
		if(count($usuario)){
			//Si encuentra el usuario se inicia la sesion con los datos del usuario.
			new Session();
			
			$f3->set('SESSION.user',$usuario[0]['alias']);
			$f3->set('SESSION.id',$usuario[0]['idUsuario']);
			
			//echo "--".$f3->get('SESSION.user')."--";
			
			//Se redirige al usuario a la ruta de panel de control
			//$f3->reroute('@miPanel'); 
			echo '<script language="javascript" type="text/javascript">window.location.href="mipanel/";</script>';
			
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
$f3->route('POST @registrousuario: /registrousuario [ajax]',
	function($f3) use ($db) {
		$formulario = $f3->get("REQUEST");
		
		/*echo "<pre>";
		print_r($formulario);
		echo "</pre>";
		die();*/
		$user=new DB\SQL\Mapper($db,'Usuario');
		$user->set('alias',$formulario['user']);
		$user->set('email',$formulario['email']);
		$user->set('password',$formulario['clave']);
		//Se guarda el usuario creado
		$user->save();
		
		/*@todo falta agregar la logica para verificar si ya existe le usuario o si se pudo guardar correctamente por ejemplo que no exista el usuario y el email antes*/
		
		//Si encuentra el usuario se inicia la sesion con los datos del usuario.
			new Session();
			
			$f3->set('SESSION.user',$user->get('alias'));
			$f3->set('SESSION.id',$user->get('idUsuario'));

		//Se redirecciona al panel de control
		echo '<script language="javascript" type="text/javascript">window.location.href="mipanel/";</script>'; 
	}
);

/*Ruta para recibir la peticion ajax para buscar si existe el perfil del usuario*/
$f3->route('POST @existeMiPerfil: /existeMiPerfil [ajax]',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	new Session();
	//echo "--".$f3->get('SESSION.user')."--";
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			$f3->set('usuario',$f3->get('SESSION.user'));
			
			/*Se consulta la base de datos para ver si existe un perfil propio para este usuario*/
		$usuario = $db->exec('SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND owner = "1"');
		//Si trae un registro debe regresar el valor de este perfil
		if(count($usuario)){
		//if(false){
		
		/*echo "<pre>";
		print_r($usuario[0]);
		echo "</pre>";*/
			//Se inicia la cadena con el formato de json
			$objetojson = '{"objeto": [{';
			$lastKey = count($usuario[0])-1;
			$aux = 0;
			foreach($usuario[0] as $key => $value):
					//Se inicializa
					$objetojson.='"'.$key.'":"'.$value.'"';
					if($lastKey != $aux){
						$objetojson.= ',';
					}
					$aux++;
			endforeach;
			$objetojson .= '}],"existe":"1"}';
			
			echo $objetojson;
			
		}else{
			$objetojson = '{"existe": "0"}';
			echo $objetojson;
		}
		
		}
	}

);

/*Ruta para recibir la peticion ajax para buscar una los perfiles del usuario*/
$f3->route('POST @existenPerfiles: /existenPerfiles [ajax]',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	new Session();
	//echo "--".$f3->get('SESSION.user')."--";
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			$f3->set('usuario',$f3->get('SESSION.user'));
			
			/*Se consulta la base de datos para ver si existe un perfil propio para este usuario*/
		$perfiles = $db->exec('SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND owner = "0"');
		//Si trae un registro debe regresar el valor de este perfil
		if(count($perfiles)){
		//if(false){
		//echo 'SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND owner = "0"';
		/*echo "<pre>";
		print_r($usuarios);
		echo "</pre>";
		die();*/
			//Se inicia la cadena con el formato de json
			$objetojson = '{"objeto": [{';
			$lastKey = count($perfiles)-1;
			
			foreach($perfiles as $k => $perfil):
				$lastKeyinner = count($perfil)-1;
				$aux = 0;
				$objetojson.= '"'.$k.'":[{';
				foreach($perfil as $key => $value):
						//Se inicializa
						$objetojson.='"'.$key.'":"'.$value.'"';
						if($lastKeyinner != $aux){
							$objetojson.= ',';
						}
						$aux++;
				endforeach;
				$objetojson.= '}]';
				if($k != $lastKey){
							$objetojson.= ',';
						}
			endforeach;
			$objetojson .= '}],"existe":"1", "length": "'.count($perfiles).'"}';
			
			echo $objetojson;
			
		}else{
			$objetojson = '{"existe": "0", "lenght" : "0"}';
			echo $objetojson;
		}
		
		}
	}

);


/*Ruta para recibir la peticion ajax para buscar una enfermedad en especifico y refrescar el mapa*/
$f3->route('POST @busquedaHome: /busquedaHome [ajax]',
	function($f3) use ($db) {
		$busqueda = $f3->get("REQUEST");
		
		/*echo "<pre>";
		print_r($busqueda);
		echo "</pre>";
		die();*/

		//Consulta a la base de datos para obtener una lista con los eventos
	
		$fechainicial = date ("Y-m-d H:i:s",time());
		$fechaanterior = date('Y-m-d H:i:s', strtotime('-1 day', strtotime( date("Y-m-d H:i:s",time()) )));
//echo $busqueda['busquedaajax'];
//die();
		
		//Se revisa si se buscara reportes solo de ciudadano y gobierno
		$tipousuario = "";
		
		if($busqueda['ciudadano'] && $busqueda['gobierno']){
			$tipousuario .= 'AND (u.tipoUsuario = 2 OR u.tipoUsuario = 3) ';
		}else{
			if($busqueda['ciudadano']){
			$tipousuario .= 'AND u.tipoUsuario = 2 ';
			}
			if($busqueda['gobierno']){
				$tipousuario .= 'AND u.tipoUsuario = 3 ';
			}
		}
		
		$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial.'") AND UPPER(en.name) LIKE "'.$busqueda['busquedaajax'].'" '.$tipousuario);
	
	//Se inicia la cadena con el formato de json
	$objetojson = '{"objeto": [{';
	//Se obitnene la longitud de la columna
	$lastKey = count($eventos)-1;

	foreach($eventos as $k => $evento):
		$objetojson.= '"'.$k.'":[{';
		//Se obtiene el total de keys
		$lastKeyinner = count($evento)-1;
		$aux = 0;
		foreach($evento as $key => $event ):
			//Se inicializa
			$objetojson.='"'.$key.'":"'.$event.'"';
			if($lastKeyinner != $aux){
				$objetojson.= ',';
			}
			$aux++;
		endforeach;
		$objetojson.= '}]';
		if($k != $lastKey){
			$objetojson.= ',';
		}
	endforeach;
	$objetojson .= '}],"length":"'.count($eventos).'"}';
//die();
	echo $objetojson;

	}
);

/*----*/


/*Ruta para mostrar al usuario su panel de control*/
$f3->route('GET  @miPanel: /mipanel',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	new Session();
	//echo "--".$f3->get('SESSION.user')."--";
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			$f3->set('content','panel/paneldecontrol.html');
			$f3->set('formularioreporte','panel/formularioreporte.html');
			$f3->set('menu','menu.html');
			$f3->set('usuario',$f3->get('SESSION.user'));
			
			//Consulta a la base de datos para obtener una lista con los eventos
			$fechainicial = date ("Y-m-d H:i:s",time());
			$fechaanterior = date('Y-m-d H:i:s', strtotime('-1 day', strtotime( date("Y-m-d H:i:s",time()) )));
			
			//Se hace una consulta para recuperar el evento, el perfil, la categoria y la enfermedad, asi como el usuario
			//Esta consulta se debe simplificar solo obtener la informacion del evento y luego con ajax hacer una consulta especifica cuando se de clic en el evento.
			$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial.'")');
			
			$f3->set('eventosrecientes',$eventos);
			
			//Se buscan los perfiles asociados al usuario
			$perfiles = $db->exec('SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND owner = "0"');
			
			foreach($perfiles as $key => $perfil):
				$per[$perfil['idPerfil']] = $perfil['firtsName'].' '.$perfil['lastName'];
			endforeach;
	

$f3->set('perfiles',$per);
			
			/*Se cargan enfermedades*/
			$en = $db->exec('SELECT en.idEnfermedad, en.name FROM Enfermedad AS en ORDER BY en.name ASC');
			
			foreach($en as $e):
				$enfermedades[$e["idEnfermedad"]] = $e["name"];
			endforeach;
			$f3->set('enfermedades',$enfermedades);
			
			echo Template::instance()->render('layout.html');
		}else{
			/*Si no se reenvia al home*/
			$f3->reroute('@home'); 
		}
		
	}
);

/*Ruta para cerrar la sesion*/
$f3->route('GET  @logout: /logout',
	function($f3) {
	
		//Se inicializa la sesion para poder acceder a los valores anteriores
		new Session();
		//Se destruye la sesion del usuario
		$f3->clear('SESSION.user');
		$f3->clear('SESSION.id');
		$f3->clear('SESSION');
		//session_destroy();
		$f3->reroute('@home'); 
		//echo Template::instance()->render('layout.html');
	}
);

/*Ruta para cerrar la seccion quienes somos */
$f3->route('GET  @quienessomos: /quienessomos',
	function($f3) {
	
		//Se indica que el contenido del template lo tomara de home.html
		$f3->set('content','quienessomos.html');
		
		/*Se obtiene el arreglo de la sesion para saber si existe el key user*/
	$sesion = $f3->get('SESSION');
	/*Si no existe se declara nula*/
	if(!array_key_exists("user",$sesion)){
		$f3->set('usuario','');
	}
		
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
