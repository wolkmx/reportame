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

/*Ruta para mostrar al usuario su panel de control*/
$f3->route('GET  @miPanel: /mipanel',
	function($f3) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	new Session();
	//echo "--".$f3->get('SESSION.user')."--";
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			$f3->set('content','panel/paneldecontrol.html');
			$f3->set('menu','menu.html');
			$f3->set('usuario',$f3->get('SESSION.user'));
			
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

/**
 * @author Oscar Galindez <oscarabreu19@gmail.com>
 * @todo Controlador para el manejo del CRUD de la tabla 'perfiles'
 */
$f3->route('GET|POST @perfil: /perfil',
	function($f3) use ($db) {
    
            //-- Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!
            new Session();
            
            //-- Obtiene la peticion
            $request = $f3->get("REQUEST");
            
            /**
             * Maneja las cuatro opciones 
             * del CRUD
             * ---
             * 1:   Listar: muestra el contenido de la tabla 
             *      con todos los registros
             *      [En el caso de 'perfil' solo los perfiles 
             *      del usuario en sesion] 
             * -
             * 2:   Pre-Agregar: monta un formulario vacio para 
             *      agregar un nuevo registro
             * -
             * 3:   Agregar: carga en DB un nuevo registro
             * -
             * 4:   Consultar: se muestra el contenido
             *      del registro previamente creado
             * -
             * 5:   Editar: modifica en BD el contenido en la 
             *      vista 'Consultar' 
             * -
             * 6:   Pre-Eliminar: se muestra el contenido
             *      del registro previamente creado sin 
             *      opcion para modificar.
             * -
             * 7:   Eliminar: elimina en DB un registro 
             *      seleccionado 
             */
            switch ( $request['menuOpc'] )
            {
                case '1':   //-- Listar
                    
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //-- Se arma la consulta
                        $consulta = 'SELECT * FROM perfil WHERE usuario_id= '. $f3->get('SESSION.id') .';';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );

                        //-- Carga el arreglo de respuesta en 'SESSION'
                        $f3->set( 'todosLosRegistros', $todosLosRegistros );

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/index.html');
                        $f3->set('menu','menu.html');
                        echo Template::instance()->render('layout.html');

                    }
                    else
                    {
                        //Se destruye la sesion del usuario
                        $f3->clear('SESSION.user');
                        $f3->clear('SESSION.id');
                        $f3->clear('SESSION');
                        
                        /*Si no se reenvia al home*/
                        $f3->reroute('@home'); 
                    }
                    
                    break;

                case '2':   //-- Pre-Agregar
                    
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/preAgregar.html');
                        $f3->set('menu','menu.html');
                        echo Template::instance()->render('layout.html');

                    }
                    else
                    {
                        //Se destruye la sesion del usuario
                        $f3->clear('SESSION.user');
                        $f3->clear('SESSION.id');
                        $f3->clear('SESSION');
                        
                        /*Si no se reenvia al home*/
                        $f3->reroute('@home'); 
                    }
                    
                    break;
                
                case '3':   //-- Agregar
                    
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Fijamos la fecha
                        $fecha = date("Y-m-d H:i:s");
                        
                        //--    Llama al modelo
                        $f3->set('perfil',new DB\SQL\Mapper($db,'perfil'));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('perfil')->copyFrom('POST');
                        
                        //--    Agrega datos que no vienen del
                        //      formulario pero que son necesarios
                        $f3->get('perfil')->set('usuario_id',$f3->get('SESSION.id'));
                        $f3->get('perfil')->set('updated_at',$fecha);
                        $f3->get('perfil')->set('created_at',$fecha);
                        
                        //--    Salva en BD
                        $f3->get('perfil')->save();
                        
                        //-- Prepara los datos para la vista
                        $f3->get('perfil')->copyTo('POST');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El perfil fue creado satisfactoriamente');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        //-- Llama a la vista
                        echo Template::instance()->render('layout.html');

                    }
                    else
                    {
                        //Se destruye la sesion del usuario
                        $f3->clear('SESSION.user');
                        $f3->clear('SESSION.id');
                        $f3->clear('SESSION');
                        
                        /*Si no se reenvia al home*/
                        $f3->reroute('@home'); 
                    }
                    
                    break;
                
                case '4':   //-- Consultar

                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //-- Llama al modelo
                        $f3->set('perfil',new DB\SQL\Mapper($db,'perfil'));

                        //-- Carga al objeto
                        $f3->get('perfil')->load(array('idPerfil=?',$request['id']));

                        //-- Prepara los datos para la vista
                        $f3->get('perfil')->copyTo('POST');

                        // Inicializamos la variable
                        $f3->set('flash',NULL);

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEditar.html');

                        //-- Agrega el menu
                        $f3->set('menu','menu.html');

                        //-- Llama a la vista
                        echo Template::instance()->render('layout.html');

                    }
                    else
                    {
                        //Se destruye la sesion del usuario
                        $f3->clear('SESSION.user');
                        $f3->clear('SESSION.id');
                        $f3->clear('SESSION');
                        
                        /*Si no se reenvia al home*/
                        $f3->reroute('@home'); 
                    }
                    
                    break;
                
                case '5':   //-- Editar
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('perfil',new DB\SQL\Mapper($db,'perfil'));
                        
                        //-- Carga al objeto
                        $f3->get('perfil')->load(array('idPerfil=?',$request['id']));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('perfil')->copyFrom('POST');
                        
                        //--    Agrega datos que no vienen del
                        //      formulario pero que son necesarios
                        $f3->get('perfil')->set('updated_at',date("Y-m-d H:i:s"));
                        
                        //--    Salva en BD
                        $f3->get('perfil')->save();
                        
                        //-- Prepara los datos para la vista
                        $f3->get('perfil')->copyTo('POST');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El perfil fue creado satisfactoriamente');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        //-- Llama a la vista
                        echo Template::instance()->render('layout.html');
                        
                    }
                    else
                    {
                        //Se destruye la sesion del usuario
                        $f3->clear('SESSION.user');
                        $f3->clear('SESSION.id');
                        $f3->clear('SESSION');
                        
                        /*Si no se reenvia al home*/
                        $f3->reroute('@home'); 
                    }
                    
                    break;
                
                case '6':   //-- Pre-Eliminar
                    die('Pre-Eliminar');
                    break;
                
                case '7':   //-- Eliminar
                    die('Eliminar');
                    break;
                    
                default:
                    break;
            }
		
	}
);

$f3->run();