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
		//echo "entro";
	}/*else{
		echo "no entro--";
		echo $f3->get('SESSION.user');
	}*/
	
	/*echo "---<pre>";
	print_r($sesion);
	echo "</pre>---";*/
	
	$f3->set('nombre',$user);
	
	//Consulta a la base de datos para obtener una lista con los eventos
	
	$fechainicial = date ("Y-m-d H:i:s",time());
	$fechaanterior = date('Y-m-d H:i:s', strtotime('-1 day', strtotime( date("Y-m-d H:i:s",time()) )));
	
	//Se hace una consulta para recuperar el evento, el perfil, la categoria y la enfermedad, asi como el usuario
	//Esta consulta se debe simplificar solo obtener la informacion del evento y luego con ajax hacer una consulta especifica cuando se de clic en el evento.
	/*$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial.'")');*/
	
	$eventos = $db->exec('SELECT e.*, p.*, en.name, en.imagePing, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario');
	
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
		$formulario = $f3->get("REQUEST");
		//Se declara la variable flash como nula para que no aparesca la primera vez que se muestra el formulario pero si la segunda vez si es que hay un error.
		$f3->set('flash',null);
		$f3->set('flashr',null);
		$f3->set("tipo", $formulario["tipo"]);
		echo Template::instance()->render('loginform.html');
	}
);

/*Ruta para cerrar la seccion descarga */
$f3->route('GET  @descarga: /descarga',
	function($f3) {
	
		//Se indica que el contenido del template lo tomara de home.html
		$f3->set('content','descargar.html');
		$f3->set('menu','menu.html');
                
                $f3->set('flash',NULL);
		
		/*Se obtiene el arreglo de la sesion para saber si existe el key user*/
	$sesion = $f3->get('SESSION');

	/*Si no existe se declara nula*/
	if(!array_key_exists("user",$sesion)){
		$f3->set('usuario','');
	}else{
		$f3->set('usuario',$f3->get('SESSION.user'));
	}
		
		echo Template::instance()->render('layout.html');
	}
);

/*Ruta para cerrar la seccion email */
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
		$f3->set('flashr',Null);
		
		if(count($usuario)){
			//Si encuentra el usuario se inicia la sesion con los datos del usuario.
			//new Session();
			
			$f3->set('SESSION.user',$usuario[0]['alias']);
			$f3->set('SESSION.id',$usuario[0]['idUsuario']);
			$f3->set('SESSION.tipo',$usuario[0]['tipoUsuario']);
			
			//echo "--".$f3->get('SESSION.user')."--";
			
			//Se redirige al usuario a la ruta de panel de control
			//$f3->reroute('@miPanel'); 
			echo '<script language="javascript" type="text/javascript">window.location.href="mipanel/";</script>';
			
		}else{
		//Si no encuentra el usuario volvera a mostrar el formulario con un mensaje de advertencia llamado flash
			$f3->set('flash','Usuario o clave incorrecta por favor ingrese los datos nuevamente');
			$f3->set("tipo", "is");
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
		$f3->set('flash',Null);
		$f3->set('flashr',Null);		
		
		$user=new DB\SQL\Mapper($db,'Usuario');
		$user->set('alias',$formulario['user']);
		$user->set('email',$formulario['email']);
		$user->set('tipoUsuario',2);
		$user->set('password',$formulario['clave']);
                
		//Se guarda el usuario creado
		$user->save();
		
        //-- Envio del email
    
                $smtp = new SMTP ( 'smtp.gmail.com', 465, 'SSL', 'reportame.vzla@gmail.com', 'gpuOyuN8Ma92la0TsMrj' );

                $smtp->set('From', '"Reporta-m" <reportame@info.com>');
                $smtp->set('To', '<'.$user->get('email').'>');
                $smtp->set('Subject', 'Confirmacion de Activacion');  
                $smtp->set('Errors-to', '<usuario@dominio.com>');  

                $message = 'Usuario: '. $user->get('alias'); 
                $message .= '<br>Clave: '. $user->get('password');
				
				/*$message = "<!DOCTYPE html>
<html lang='en'>
<head>
	<meta charset='UTF-8'>
	<link rel='stylesheet' href='http://reportame.ohsioh.com/ui/css/bootstrap.min.css'>
	<link rel='stylesheet' href='http://reportame.ohsioh.com/ui/css/style_email.css'>
	<script src='http://reportame.ohsioh.com/ui/js/jquery-2.1.1.min.js'></script>
</head>
<body>
	<div class='container borde'>
		<div class='row'>
			<div class='col-md-12'>
				<div class='logo'><img src='http://reportame.ohsioh.com/ui/images/logo.png' width='300' alt='' class='img-responsive'></div>
				<div class='texto'>
					<h3>¡Bienvenido a Reportame!</h3>
					<p>Para comenzar a formar parte de una comunidad transparente e informada debes verificar tu dirección de email. </p>
				</div>
				<button class='btn btn-primary send' type='buttom' >Verificar Email</button><br>
				<p style='margin-top:10px; color:#999;'>También puedes verificarla a través de este enlace:
					<a href='#'>http://reportame.org/account/email/verify/w2ZPJUpQQmE_LQmCw-Jv</a>
				</p>
			</div>
		</div>
	</div>
	
</body>
</html>";*/

                $sent = $smtp->send($message, TRUE);
				//$sent = $smtp->send(Template::instance()->render('email.txt','text/html'));

                $mylog = $smtp->log();

                $sentText = 'not sent';

                $headerText = 'does not exist';

                if ($sent)
                {
                    $sentText = 'was sent';
                }

                if ($smtp->exists('Date'))
                {
                    $headerText = 'exists';
                }

        //-- Envio del email
       
		/*@todo falta agregar la logica para verificar si ya existe le usuario o si se pudo guardar correctamente por ejemplo que no exista el usuario y el email antes*/
		
		//Si encuentra el usuario se inicia la sesion con los datos del usuario.
                //new Session();

                $f3->set('SESSION.user',$user->get('alias'));
                $f3->set('SESSION.id',$user->get('idUsuario'));
				$f3->set('SESSION.tipo',$user->get('tipoUsuario'));
				
				/*echo $f3->get('SESSION.user');
				echo $f3->get('SESSION.id');
				echo $f3->get('SESSION.tipo');
				echo "*****";*/
			/*$f3->set('SESSION.user',$formulario['user']);
			$f3->set('SESSION.id',1);
			$f3->set('SESSION.tipo',2);
			
			echo $f3->get('SESSION.user');
				echo $f3->get('SESSION.id');
				echo $f3->get('SESSION.tipo');*/
				
				


		//Se redirecciona al panel de control
		echo '<script language="javascript" type="text/javascript">window.location.href="mipanel/";</script>'; 
	}
);

/*Ruta para recibir la peticion ajax para buscar si existe el usuario*/
$f3->route('POST @checkUser: /checkUser [ajax]',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/

		$formulario = $f3->get("REQUEST");	
		/*Se consulta la base de datos para ver si existe un perfil propio para este usuario*/
		$usuario = $db->exec('SELECT count(u.idUsuario) AS total FROM Usuario AS u WHERE alias LIKE "'.$formulario['alias'].'" AND email LIKE "'.$formulario['email'].'"');
		//Si trae un registro debe regresar el valor de este perfil
		if($usuario[0]['total'] > 0){
			
			echo '{"existe": "1"}';
			
		}else{
			echo '{"existe": "0"}';
		}
		
	}

);

/*Ruta para recibir la peticion ajax para buscar si existe el perfil del usuario*/
$f3->route('POST @existeMiPerfil: /existeMiPerfil [ajax]',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	//new Session();
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
	//new Session();
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

/*Ruta para recibir la peticion ajax para buscar un perfil especifico del usuario*/
$f3->route('POST @getPerfil: /getPerfil [ajax]',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	//new Session();
	//echo "--".$f3->get('SESSION.user')."--";
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			$f3->set('usuario',$f3->get('SESSION.user'));
			$formulario = $f3->get("REQUEST");
			/*Se consulta la base de datos para ver si existe un perfil propio para este usuario*/
			$perfil = $db->exec('SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND idPerfil = "'.$formulario['perfilId'].'"');
			//Si trae un registro debe regresar el valor de este perfil
			if(count($perfil)){
			//if(false){
			//echo 'SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND owner = "0"';
			/*echo "<pre>";
			print_r($perfil);
			echo "</pre>";
			die();*/
				//Se inicia la cadena con el formato de json
				$objetojson = '{"objeto": [{';
				$lastKey = count($perfil[0])-1;
				$aux = 0;
				foreach($perfil[0] as $k => $value):
					$objetojson.= '"'.$k.'":"'.$value.'"';
					if($aux != $lastKey){
								$objetojson.= ',';
							}
					$aux++;
				endforeach;
				$objetojson .= '}],"existe":"1", "length": "'.count($perfil[0]).'"}';
				
				echo $objetojson;
				
			}else{
				$objetojson = '{"existe": "0", "lenght" : "0"}';
				echo $objetojson;
			}
		
		}
	}

);

/*Ruta para recibir la peticion ajax para guardar el evento*/
$f3->route('POST @guardarEvento: /guardarEvento [ajax]',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	//new Session();
	//echo "--".$f3->get('SESSION.user')."--";
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			//Se define vacia la variable
			$perfil = null;			

			$formulario = $f3->get("REQUEST");
			//Se revisa si el perfil esta definido como propio o no
			if($formulario['owner']){
				//Se revisa si el perfil no existia previamente.
				if(!$formulario['owner_exist']){
					//Si no existia previamente se crea en la base de datos
					$perfil=new DB\SQL\Mapper($db,'Perfil');
					$perfil->firtsName=$formulario['nombre_reporte'];
					$perfil->lastName=$formulario['apellido_reporte'];
					$perfil->phone=$formulario['telefono_reporte'];
					$perfil->cellphone=$formulario['celular_reporte'];
					$perfil->birthday=$formulario['cumple_reporte'];
					$perfil->country=$formulario['pais_reporte'];
					$perfil->city=$formulario['ciudad_reporte'];
					$perfil->municipio=$formulario['municipio_reporte'];
					$perfil->sex=$formulario['sexo_reporte'];

					$perfil->profesion=$formulario['profesion_reporte'];
					$perfil->usuario_id=$f3->get('SESSION.id');
					$perfil->documentType=$formulario['documentType_reporte'];
					$perfil->estadoCivil=$formulario['estadoCivil_reporte'];
					$perfil->numeroHijos=$formulario['numeroHijos_reporte'];
					$perfil->peso=$formulario['peso_reporte'];
					$perfil->tipoSangre=$formulario['tipoSangre_reporte'];
					$perfil->owner=1;
					$perfil->created_at=date('Y-m-d H:i:s');
					$perfil->updated_at=date('Y-m-d H:i:s');
					$perfil->save();
					
				}else{
					//Si existe se busca en la base de datos y se relaciona con el evento
					$perfil=new DB\SQL\Mapper($db,'Perfil');
					$perfil->load(array('usuario_id=? AND owner=?',$f3->get('SESSION.id'), 1));
					
				}
				
			}else{
				//Si no es propio se revisa si existe o no un perfil previamente
				if($formulario['perfil'] != "" ){
					//Si es diferente de '' se busca el perfil seleccionado
					$perfil=new DB\SQL\Mapper($db,'Perfil');
					$perfil->load(array('usuario_id=? AND idPerfil=? ',$f3->get('SESSION.id'), $formulario['perfil']));
				}else{
					//Si no existia el perfil debe crearse uno nuevo con los datos que se ingresaron
					//@todo falta optimizar esta seccion de codigo para que sea reutilizable con la seccion superior
					$perfil=new DB\SQL\Mapper($db,'Perfil');
					$perfil->firtsName=$formulario['nombre_reporte'];
					$perfil->lastName=$formulario['apellido_reporte'];
					$perfil->phone=$formulario['telefono_reporte'];
					$perfil->cellphone=$formulario['celular_reporte'];
					$perfil->birthday=$formulario['cumple_reporte'];
					$perfil->country=$formulario['pais_reporte'];
					$perfil->city=$formulario['ciudad_reporte'];
					$perfil->municipio=$formulario['municipio_reporte'];
					$perfil->sex=$formulario['sexo_reporte'];
					$perfil->profesion=$formulario['profesion_reporte'];
					$perfil->usuario_id=$f3->get('SESSION.id');
					$perfil->documentType=$formulario['documentType_reporte'];
					$perfil->estadoCivil=$formulario['estadoCivil_reporte'];
					$perfil->numeroHijos=$formulario['numeroHijos_reporte'];
					$perfil->peso=$formulario['peso_reporte'];
					$perfil->tipoSangre=$formulario['tipoSangre_reporte'];
					$perfil->owner=0;
					$perfil->created_at=date('Y-m-d H:i:s');
					$perfil->updated_at=date('Y-m-d H:i:s');
					$perfil->save();
					
				}
			}
			//Se obtiene la latitud y la longitud			
			$latlon = explode(',',$formulario['latlon']);
			//Se crea el nuevo evento
			$evento=new DB\SQL\Mapper($db,'Evento');
			$evento->lat=$latlon[1];
			$evento->lon=$latlon[0];
			$evento->usuario_id=$f3->get('SESSION.id');
			$evento->categoria_id=$formulario['categoria_reporte'];
			if($formulario['categoria_reporte']== 1){
				$evento->enfermedad_id=$formulario['enfermedad_reporte'];			
			}
			$evento->descripcion=$formulario['descripcion_reporte'];
			$evento->perfil_id=$perfil->get('_id');
			$evento->created_at=date('Y-m-d H:i:s');
			$evento->updated_at=date('Y-m-d H:i:s');

			//Se guarda el evento
			$evento->save();

			/*echo "<pre>";
			print_r($formulario);
			echo "</pre>";*/

			//Se obtiene el tipo de reporte y el nombre de la enfermedad
			$categoria=new DB\SQL\Mapper($db,'Categoria');
			$categoria->load(array('idCategoria=?',$evento->get('categoria_id')));
			//Se obtiene el tipo de enfermedad
			if($formulario['categoria_reporte']== 1){
				$enf=new DB\SQL\Mapper($db,'Enfermedad');
				$enf->load(array('idEnfermedad=?',$evento->get('enfermedad_id')));
				$enfermedad['name'] = $enf->get('name');
				$enfermedad['image'] = $enf->get('imagePing');
			}else{
				$enfermedad = null;
			}

			echo '{"objeto": [{"lat":"'.$evento->get('lat').'", "lon":"'.$evento->get('lon').'", "usuario":"'.$f3->get('SESSION.user').'", "tipo_reporte":"'.$categoria->get('name').'", "enfermedad":"'.$enfermedad['name'].'", "image":"'.$enfermedad['image'].'", "created":"'.$evento->get('created_at').'" }],"resultado": "1","evento": "'.$evento->get('_id').'"}';
		
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
		
		/*$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial.'") AND UPPER(en.name) LIKE "'.$busqueda['busquedaajax'].'" '.$tipousuario);*/
		
		$eventos = $db->exec('SELECT e.*, p.*, en.name, en.imagePing, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE UPPER(en.name) LIKE "'.$busqueda['busquedaajax'].'" '.$tipousuario);
	
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


/*Ruta para recibir la peticion ajax para buscar todas las etiquetas y consejos del evento*/
$f3->route('POST @etiquetasConsejo: /etiquetasConsejo [ajax]',
	function($f3) use ($db) {
		$idEvento = $f3->get("REQUEST");
		$eventoLabels = $db->exec('SELECT el.label_id, l.name FROM EventoLabel AS el LEFT JOIN Label l ON el.label_id = l.idLabel WHERE el.evento_id = '.$idEvento['idEvento']);

		//echo 'SELECT el.label_id, l.name FROM EventoLabel AS el LEFT JOIN Label l ON el.label_id = l.idLabel WHERE el.evento_id = '.$idEvento['idEvento'];
		
	//Se inicia la cadena con el formato de json
	$objetojson = '{"labels": [{';
	//Se obtiene la longitud de la columna
	$lastKey = count($eventoLabels)-1;

	foreach($eventoLabels as $k => $eventoLabel):
		$objetojson.= '"'.$k.'":[{';
		//Se obtiene el total de keys
		$lastKeyinner = count($eventoLabel)-1;
		$aux = 0;
		foreach($eventoLabel as $key => $event ):
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
	
	$objetojson .= '}],"consejos": [{';
	
	$cadena = "";
	
	//Se construye una cadena con los id de las etiquetas del evento
	foreach($eventoLabels as $ev):
		$cadena .= $ev["label_id"].',';
	endforeach;
	
	if(count($eventoLabels) < 1){
		$cadena = "0,";
	}
	
	//se buscan los consejos relacionados con las etiquetas de eventoLabels
	$consejos = $db->exec('SELECT c.*, u.alias FROM Consejo AS c LEFT JOIN ConsejoLabel cl ON c.idConsejo = cl.consejo_id LEFT JOIN Usuario u ON u.idUsuario = c.usuario_id WHERE cl.label_id IN ('.substr($cadena, 0, -1).')');
	
	//Se itera entre las etiquetas para construir el objeto json
	$lastKey = count($consejos)-1;

	foreach($consejos as $k => $consejo):
		$objetojson.= '"'.$k.'":[{';
		//Se obtiene el total de keys
		$lastKeyinner = count($consejo)-1;
		$aux = 0;
		foreach($consejo as $key => $c ):
			//Se inicializa
			$objetojson.='"'.$key.'":"'.$c.'"';
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

	
	//$objetojson .= '}]}';
	
	//$objetojson .= '}]';
	$objetojson .= '}],"lengthetiquetas":"'.count($eventoLabels).'", "lengtconsejos":"'.count($consejos).'"}';
	echo $objetojson;
/*
echo '<pre>';
print_r($eventoLabels);
echo '</pre>';*/

	}
);


/*Ruta dummy para aceptar las peticiones get desde jquery para mostrar el formulario de etiquetas*/
$f3->route('POST @formularioEtiquetas: /formularioEtiquetas [ajax]',
	function($f3) use ($db) {
		$formulario = $f3->get("REQUEST");		
		$f3->set('id_evento',$formulario['evento']);
		echo Template::instance()->render('panel/formularioetiquetas.html');
	}

);

/*Ruta guardar las etiquetas del evento*/
$f3->route('POST @guardarEtiquetasEvento: /guardarEtiquetasEvento [ajax]',
	function($f3) use ($db) {
		//new Session();
		$formulario = $f3->get("REQUEST");
		//Se crea un array con todas las etiquetas que llegan del formulario
		$etiquetas = explode ( ' ' , $formulario['etiquetas'] );
		//Por cada etiqueta se busca si existe en la base de datos y si no se crea
		//$label=new DB\SQL\Mapper($db,'Label');
		//Etiquetas finales
		$labelfinal = '';
		$aux = 0;
		foreach($etiquetas as $etiqueta):
			//Se crea un eventolabel para guardarlo por cada etiqueta nueva o existente
			$eventoLabel=new DB\SQL\Mapper($db,'EventoLabel');
			$eventoLabel->evento_id = $formulario['id_evento'];
			$label=new DB\SQL\Mapper($db,'Label');
			//Si existe se recupera el ID;
			if($label->load(array('name=?',$etiqueta)) !== FALSE){
				$eventoLabel->label_id = $label->get('idLabel');
				//echo 'entro1:/'.$label->get('_id').'/';
			}else{
				//Si no existe se crea la etiqueta
				$label->name = $etiqueta;
				$label->usuario_id = $f3->get('SESSION.id');
				$label->save();
				//Se relaciona la nueva etiqueta con el eventoLabel
				$eventoLabel->label_id = $label->get('idLabel');
				
			}
			
			//Se guardan el eventolabel
			$eventoLabel->save();
			//$labelfinal[$aux]=$eventoLabel->get('name');
			$aux++;
			//Se libera el objeto
			$eventoLabel->reset();
			//Se libera el objeto
			$label->reset();
		endforeach;

		//Se inicia la cadena con el formato de json
		$objetojson = '{"objeto": [{';
		//Se obtiene la longitud de la columna
		$lastKey = count($etiquetas)-1;

		foreach($etiquetas as $k => $etiqueta):
			$objetojson.= '"'.$k.'":"'.$etiqueta.'"';
			
			if($k != $lastKey){
				$objetojson.= ',';
			}
		endforeach;
		$objetojson .= '}],"length":"'.count($etiquetas).'"}';
		echo $objetojson;
		
	}

);



/*Ruta para recibir la peticion ajax para buscar todos los eventos y refrescar el mapa*/
$f3->route('POST @todosLosEventos: /todosLosEventos [ajax]',
	function($f3) use ($db) {

		//Consulta a la base de datos para obtener una lista con los eventos
	
		$fechainicial = date ("Y-m-d H:i:s",time());
		$fechaanterior = date('Y-m-d H:i:s', strtotime('-1 day', strtotime( date("Y-m-d H:i:s",time()) )));
		
		/*$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial.'") AND UPPER(en.name) LIKE "'.$busqueda['busquedaajax'].'" '.$tipousuario);*/
		
		$eventos = $db->exec('SELECT e.*, p.*, en.name, en.imagePing, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario');
	
	//Se inicia la cadena con el formato de json
	$objetojson = '{"objeto": [{';
	//Se obtiene la longitud de la columna
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
	echo $objetojson;

	}
);

/*----*/


/*Ruta para mostrar al usuario su panel de control*/
$f3->route('GET  @miPanel: /mipanel',
	function($f3) use ($db) {
	/*Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!*/
	//new Session();
	/*echo "----".$f3->get('SESSION.user')."---";
	die();*/
		/*Se verifica si el usuario tiene la sesion iniciada*/
		if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user'))){
			$f3->set('content','panel/paneldecontrol.html');
			$f3->set('formularioreporte','panel/formularioreporte.html');
			$f3->set('menu','menu.html');
			$f3->set('usuario',$f3->get('SESSION.user'));
			$f3->set('usuarioId',$f3->get('SESSION.id'));
			
			//Consulta a la base de datos para obtener una lista con los eventos
			$fechainicial = date ("Y-m-d H:i:s",time());
			$fechaanterior = date('Y-m-d H:i:s', strtotime('-1 day', strtotime( date("Y-m-d H:i:s",time()) )));
			
			//Se hace una consulta para recuperar el evento, el perfil, la categoria y la enfermedad, asi como el usuario
			//Esta consulta se debe simplificar solo obtener la informacion del evento y luego con ajax hacer una consulta especifica cuando se de clic en el evento.
			/*$eventos = $db->exec('SELECT e.*, p.*, en.name, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario WHERE (e.created_at BETWEEN "'.$fechaanterior.'" AND "'.$fechainicial*/
			
			$eventos = $db->exec('SELECT e.*, p.*, en.name, en.imagePing, c.name as categoriaName, u.alias FROM Evento AS e LEFT JOIN Perfil p ON e.perfil_id = p.idPerfil LEFT JOIN Enfermedad en ON e.enfermedad_id = en.idEnfermedad LEFT JOIN Categoria c ON e.categoria_id = c.idCategoria LEFT JOIN Usuario u ON e.usuario_id = u.idUsuario');
			
			$f3->set('eventosrecientes',$eventos);
			
			//Se buscan los perfiles asociados al usuario
			$perfiles = $db->exec('SELECT * FROM Perfil WHERE usuario_id = "'.$f3->get('SESSION.id').'" AND owner = "0"');
			$per = null;			

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

			/*Se cargan las categorias*/
			$ca = $db->exec('SELECT c.idCategoria, c.name FROM Categoria AS c ORDER BY c.name ASC');
			
			foreach($ca as $c):
				$categorias[$c["idCategoria"]] = $c["name"];
			endforeach;
			$f3->set('categorias',$categorias);
			
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
		//new Session();
		//Se destruye la sesion del usuario
		$f3->clear('SESSION.user');
		$f3->clear('SESSION.id');
		$f3->clear('SESSION.tipo');
		$f3->clear('SESSION');
		//session_destroy();
		$f3->reroute('@home'); 
		//echo Template::instance()->render('layout.html');
	}
);

/*Ruta para cerrar la seccion por que lo hacemos */
$f3->route('GET  @porquelohacemos: /porquelohacemos',
	function($f3) {
	
		//Se indica que el contenido del template lo tomara de home.html
		$f3->set('content','porquelohacemos.html');
		$f3->set('menu','menu.html');
		
		/*Se obtiene el arreglo de la sesion para saber si existe el key user*/
	$sesion = $f3->get('SESSION');
	/*Si no existe se declara nula*/
	if(!array_key_exists("user",$sesion)){
		$f3->set('usuario','');
	}else{
		$f3->set('usuario',$f3->get('SESSION.user'));
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

/**
 * @author Oscar Galindez <oscarabreu19@gmail.com>
 * @todo Controlador para el manejo del CRUD de la tabla 'perfiles'
 */
$f3->route('GET|POST @perfil: /perfil',
	function($f3) use ($db) {
    
            //-- Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!
            //new Session();
            
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
                        $consulta = 'SELECT * FROM Perfil WHERE usuario_id= '. $f3->get('SESSION.id') .';';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );

                        //-- Si tiene registros
                        if(count($todosLosRegistros)!=0 ):
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', $todosLosRegistros );
                        else:
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', 0 );
                        endif;

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/index.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/preAgregar.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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
                        $f3->set('perfil',new DB\SQL\Mapper($db,'Perfil'));
                        
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
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El perfil fue creado satisfactoriamente');
                        
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
                        $f3->set('perfil',new DB\SQL\Mapper($db,'Perfil'));

                        //-- Carga al objeto
                        $f3->get('perfil')->load(array('idPerfil=?',$request['id']));

                        //-- Prepara los datos para la vista
                        $f3->get('perfil')->copyTo('POST');

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEditar.html');

                        //-- Agrega el menu
                        $f3->set('menu','menu.html');

                        // Inicializamos la variable
                        $f3->set('flash',NULL);
                        
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
                        $f3->set('perfil',new DB\SQL\Mapper($db,'Perfil'));
                        
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
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El perfil fue modificado satisfactoriamente');
                        
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
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('perfil',new DB\SQL\Mapper($db,'Perfil'));
                        
                        //-- Carga al objeto
                        $f3->get('perfil')->load(array('idPerfil=?',$request['id']));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('perfil')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEliminar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',"¿Seguro que quiere eliminar?");
                        
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
                
                case '7':   //-- Eliminar
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('perfil',new DB\SQL\Mapper($db,'Perfil'));
                        
                        //-- Arma la consulta
                        $consulta = 'DELETE FROM Perfil WHERE idPerfil='.$request['id'].';';
                        
                        //-- Eliminar el registro
                        $db->exec($consulta);
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','perfil/preEliminar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        //-- Se arma la consulta
                        $consulta = 'SELECT * FROM Perfil WHERE usuario_id= '. $f3->get('SESSION.id') .';';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );

                        //-- Si tiene registros
                        if(count($todosLosRegistros)!=0 ):
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', $todosLosRegistros );
                        else:
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', 0 );
                        endif;

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','perfil/index.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',"El registro ".$request['id']." fue eliminado satisfactorio.");
                        
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
                    
                default:
                    break;
            }
		
	}
);

/**
 * @author Oscar Galindez <oscarabreu19@gmail.com>
 * @todo Controlador para el manejo del CRUD de la tabla 'categoria'
 */
$f3->route('GET|POST @categoria: /categoria',
	function($f3) use ($db) {
    
            //-- Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!
            //new Session();
            
            //-- Obtiene la peticion
            $request = $f3->get("REQUEST");
            
            /**
             * Maneja las cuatro opciones 
             * del CRUD
             * ---
             * 1:   Listar: muestra el contenido de la tabla 
             *      con todos los registros
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
                        $consulta = 'SELECT * FROM Categoria;';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );
                        
                        //-- Si tiene registros
                        if(count($todosLosRegistros)!=0 ):
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', $todosLosRegistros );
                        else:
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', 0 );
                        endif;
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','categoria/index.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','categoria/preAgregar.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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
                        $f3->set('categoria',new DB\SQL\Mapper($db,'categoria'));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('categoria')->copyFrom('POST');
                        
                        //--    Agrega datos que no vienen del
                        //      formulario pero que son necesarios
                        $f3->get('categoria')->set('updated_at',$fecha);
                        $f3->get('categoria')->set('created_at',$fecha);
                        
                        //--    Salva en BD
                        $f3->get('categoria')->save();
                        
                        //-- Prepara los datos para la vista
                        $f3->get('categoria')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','categoria/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El categoria fue creado satisfactoriamente');
                        
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
                        $f3->set('categoria',new DB\SQL\Mapper($db,'categoria'));

                        //-- Carga al objeto
                        $f3->get('categoria')->load(array('idCategoria=?',$request['id']));

                        //-- Prepara los datos para la vista
                        $f3->get('categoria')->copyTo('POST');

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','categoria/preEditar.html');

                        //-- Agrega el menu
                        $f3->set('menu','menu.html');

                        // Inicializamos la variable
                        $f3->set('flash',NULL);
                        
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
                        $f3->set('categoria',new DB\SQL\Mapper($db,'categoria'));
                        
                        //-- Carga al objeto
                        $f3->get('categoria')->load(array('idCategoria=?',$request['id']));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('categoria')->copyFrom('POST');
                        
                        //--    Agrega datos que no vienen del
                        //      formulario pero que son necesarios
                        $f3->get('categoria')->set('updated_at',date("Y-m-d H:i:s"));
                        
                        //--    Salva en BD
                        $f3->get('categoria')->save();
                        
                        //-- Prepara los datos para la vista
                        $f3->get('categoria')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','categoria/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El categoria fue modificada satisfactoriamente');
                        
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
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('categoria',new DB\SQL\Mapper($db,'categoria'));
                        
                        //-- Carga al objeto
                        $f3->get('categoria')->load(array('idCategoria=?',$request['id']));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('categoria')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','categoria/preEliminar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',"¿Seguro que quiere eliminar?");
                        
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
                
                case '7':   //-- Eliminar
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('categoria',new DB\SQL\Mapper($db,'categoria'));
                        
                        //-- Arma la consulta
                        $consulta = 'DELETE FROM Categoria WHERE idCategoria='.$request['id'].';';
                        
                        //-- Eliminar el registro
                        $db->exec($consulta);
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','categoria/preEliminar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        //-- Se arma la consulta
                        $consulta = 'SELECT * FROM Categoria;';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );

                        //-- Si tiene registros
                        if(count($todosLosRegistros)!=0 ):
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', $todosLosRegistros );
                        else:
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', 0 );
                        endif;

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','categoria/index.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',"El registro ".$request['id']." fue eliminado satisfactorio.");
                        
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
                    
                default:
                    break;
            }
		
	}
);

/**
 * @author Oscar Galindez <oscarabreu19@gmail.com>
 * @todo Controlador para el manejo del CRUD de la tabla 'enfermedad'
 */
$f3->route('GET|POST @enfermedad: /enfermedad',
	function($f3) use ($db) {
    
            //-- Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!
            //new Session();
            
            //-- Obtiene la peticion
            $request = $f3->get("REQUEST");
            
            /**
             * Maneja las cuatro opciones 
             * del CRUD
             * ---
             * 1:   Listar: muestra el contenido de la tabla 
             *      con todos los registros
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
                        $consulta = 'SELECT * FROM Enfermedad;';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );
                        
                        //-- Si tiene registros
                        if(count($todosLosRegistros)!=0 ):
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', $todosLosRegistros );
                        else:
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', 0 );
                        endif;
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/index.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/preAgregar.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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
                        $f3->set('enfermedad',new DB\SQL\Mapper($db,'Enfermedad'));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('enfermedad')->copyFrom('POST');
                        
                        //--    Agrega datos que no vienen del
                        //      formulario pero que son necesarios
                        
                        $f3->get('enfermedad')->set('updated_at',$fecha);
                        $f3->get('enfermedad')->set('created_at',$fecha);
                        
                        //--    Manejo de archivo 
                        $rutaDirectorio = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
                        
                        //-- Manejo de 'imagePing'
                        $uploadFile = $rutaDirectorio . basename($_FILES['imagePing']['name']);
                
                        $nombre_archivo = $_FILES['imagePing']['name'];
                        $tipo_archivo = $_FILES['imagePing']['type'];
                        $tamano_archivo = $_FILES['imagePing']['size'];
                        //compruebo si las características del archivo son las que deseo
                        if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "png"))  && ($tamano_archivo < 2000000))) {
                            $mensaje = "La extensión o el tamaño de los archivos no es correcta. <br><br><table><tr><td><li>Se permiten archivos .gif o .jpg<br><li>se permiten archivos de 100 Kb máximo.</td></tr></table>";
                        }else{
                            if (move_uploaded_file($_FILES['imagePing']['tmp_name'], $uploadFile)){
                               $mensaje = "El archivo ha sido cargado correctamente.";
                            }else{
                               $mensaje = "Ocurrió algún error al subir el fichero. No pudo guardarse.";
                            }
                        }
                        
                        //-- Manejo de 'imagePingShadow'
                        $uploadFileShadow = $rutaDirectorio . basename($_FILES['imagePingShadow']['name']);
                
                        $nombre_archivoShadow = $_FILES['imagePingShadow']['name'];
                        $tipo_archivoShadow = $_FILES['imagePingShadow']['type'];
                        $tamano_archivoShadow = $_FILES['imagePingShadow']['size'];
                        //compruebo si las características del archivo son las que deseo
                        if (!((strpos($tipo_archivoShadow, "gif") || strpos($tipo_archivoShadow, "jpeg") || strpos($tipo_archivoShadow, "png")) && ($tamano_archivoShadow < 2000000))) {
                            $mensaje = "La extensión o el tamaño de los archivos no es correcta. Se permiten archivos .gif o .jpg se permiten archivos de 100 Kb máximo.";
                        }else{
                            if (move_uploaded_file($_FILES['imagePingShadow']['tmp_name'], $uploadFileShadow)){
                               $mensaje = "El archivo ha sido cargado correctamente.";
                            }else{
                               $mensaje = "Ocurrió algún error al subir el fichero. No pudo guardarse.";
                            }
                        } 

                        //-- Guarda el nombre del archivo
                        if( $nombre_archivo != '' ):
                            $f3->get('enfermedad')->set('imagePing',$nombre_archivo);
                        endif;
                        
                        if( $nombre_archivoShadow != '' ):
                            $f3->get('enfermedad')->set('imagePingShadow',$nombre_archivoShadow);
                        endif;
                        
                        //--    Salva en BD
                        $f3->get('enfermedad')->save();
                        
                        //-- Prepara los datos para la vista
                        $f3->get('enfermedad')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El enfermedad fue creado satisfactoriamente. '.$mensaje);
                        
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
                        $f3->set('enfermedad',new DB\SQL\Mapper($db,'Enfermedad'));

                        //-- Carga al objeto
                        $f3->get('enfermedad')->load(array('idEnfermedad=?',$request['id']));

                        //-- Prepara los datos para la vista
                        $f3->get('enfermedad')->copyTo('POST');
                        
                        //--    Ruta de la carpeta de imagenes
                        $f3->set('dir',$_SERVER['DOCUMENT_ROOT'].'/uploads/');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/preEditar.html');

                        //-- Agrega el menu
                        $f3->set('menu','menu.html');

                        // Inicializamos la variable
                        $f3->set('flash',NULL);
                        
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
                        $f3->set('enfermedad',new DB\SQL\Mapper($db,'Enfermedad'));
                        
                        //-- Carga al objeto
                        $f3->get('enfermedad')->load(array('idEnfermedad=?',$request['id']));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('enfermedad')->copyFrom('POST');
                        
                        //--    Agrega datos que no vienen del
                        //      formulario pero que son necesarios
                        $f3->get('enfermedad')->set('updated_at',date("Y-m-d H:i:s"));
                        
                        //--    Manejo de archivo 
                        $rutaDirectorio = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
                        
                        //-- Manejo de 'imagePing'
                        $uploadFile = $rutaDirectorio . basename($_FILES['imagePing']['name']);
                
                        $nombre_archivo = $_FILES['imagePing']['name'];
                        $tipo_archivo = $_FILES['imagePing']['type'];
                        $tamano_archivo = $_FILES['imagePing']['size'];
                        
                        //compruebo si las características del archivo son las que deseo
                        if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "png")) && ($tamano_archivo < 2000000))) {
                            $mensaje = "La extensión o el tamaño de los archivos no es correcta. Se permiten archivos .gif o .jpg se permiten archivos de 100 Kb máximo.";
                        }else{
                            if (move_uploaded_file($_FILES['imagePing']['tmp_name'], $uploadFile)){
                               $mensaje = "El archivo ha sido cargado correctamente.";
                            }else{
                               $mensaje = "Ocurrió algún error al subir el fichero. No pudo guardarse.";
                            }
                        }
                        
                        //-- Manejo de 'imagePingShadow'
                        $uploadFileShadow = $rutaDirectorio . basename($_FILES['imagePingShadow']['name']);
                
                        $nombre_archivoShadow = $_FILES['imagePingShadow']['name'];
                        $tipo_archivoShadow = $_FILES['imagePingShadow']['type'];
                        $tamano_archivoShadow = $_FILES['imagePingShadow']['size'];
                        //compruebo si las características del archivo son las que deseo
                        if (!((strpos($tipo_archivoShadow, "gif") || strpos($tipo_archivoShadow, "jpeg") || strpos($tipo_archivoShadow, "png")) && ($tamano_archivoShadow < 2000000))) {
                            $mensaje = "La extensión o el tamaño de los archivos no es correcta. <br><br><table><tr><td><li>Se permiten archivos .gif o .jpg<br><li>se permiten archivos de 100 Kb máximo.</td></tr></table>";
                        }else{
                            if (move_uploaded_file($_FILES['imagePingShadow']['tmp_name'], $uploadFileShadow)){
                               $mensaje = "El archivo ha sido cargado correctamente.";
                            }else{
                               $mensaje = "Ocurrió algún error al subir el fichero. No pudo guardarse.";
                            }
                        } 

                        //-- Guarda el nombre del archivo
                        if( $nombre_archivo != '' ):
                            $f3->get('enfermedad')->set('imagePing',$nombre_archivo);
                        endif;
                        
                        if( $nombre_archivoShadow != '' ):
                            $f3->get('enfermedad')->set('imagePingShadow',$nombre_archivoShadow);
                        endif;
                        
                        //--    Salva en BD
                        $f3->get('enfermedad')->save();
                        
                        //-- Prepara los datos para la vista
                        $f3->get('enfermedad')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/preEditar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','El enfermedad fue modificada satisfactoriamente');
                        
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
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('enfermedad',new DB\SQL\Mapper($db,'Enfermedad'));
                        
                        //-- Carga al objeto
                        $f3->get('enfermedad')->load(array('idEnfermedad=?',$request['id']));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('enfermedad')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/preEliminar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',"¿Seguro que quiere eliminar?");
                        
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
                
                case '7':   //-- Eliminar
                
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('enfermedad',new DB\SQL\Mapper($db,'Enfermedad'));
                        
                        //-- Arma la consulta
                        $consulta = 'DELETE FROM Enfermedad WHERE idEnfermedad='.$request['id'].';';
                        
                        //-- Eliminar el registro
                        $db->exec($consulta);
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/preEliminar.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        //-- Se arma la consulta
                        $consulta = 'SELECT * FROM Enfermedad;';
                        
                        //-- Se hace la consulta
                        $todosLosRegistros = $db->exec( $consulta );

                        //-- Si tiene registros
                        if(count($todosLosRegistros)!=0 ):
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', $todosLosRegistros );
                        else:
                            //-- Carga el arreglo de respuesta en 'SESSION'
                            $f3->set( 'todosLosRegistros', 0 );
                        endif;

                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','enfermedad/index.html');
                        
                        //-- Agrega el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',"El registro ".$request['id']." fue eliminado satisfactorio.");
                        
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
                    
                default:
                    break;
            }
		
	}
);

/**
 * @author Oscar Galindez <oscarabreu19@gmail.com>
 * @todo Controlador para el manejo de la descarga de los datos
 */
$f3->route('POST @descargar: /descargar',
    function($f3) use ($db) 
    {

        //-- Obtiene la peticion
        $request = $f3->get("REQUEST");
        
        //-- Base de la consulta
        $consulta = "
                    SELECT
                        e.lat, 
                        e.lon, 
                        e.created_at, 
                        en.name, 
                        p.sex, 
                        p.country, 
                        p.city, 
                        p.municipio
                    FROM 
                            Evento e
                    INNER JOIN Perfil p
                        ON e.perfil_id = p.idPerfil
                    INNER JOIN Enfermedad en
                        ON e.enfermedad_id = en.idEnfermedad";
        
        //-- Armado de la fecha
        $fecha = '';
        
        if( $request['anio'] != '' ):
            $fecha .= $request['anio'];
        endif;
        
        if( $request['mes'] != '' ):
            $fecha .= "-".$request['mes']."-00";
        endif;
        
        //-- Armado del where segun los traido del formulario
        $where = "
                WHERE
                e.created_at >= '".$fecha."'";
        
        if( $request['enfermedad'] != '' ):
            $consulta .= " AND en.name LIKE '".$request['enfermedad']."'";
        endif;
        
        if( $request['pais'] != '' ):
            $consulta .= " AND p.country LIKE '".$request['pais']."'";
        endif;
        
        if( $request['municipio'] != '' ):
            $consulta .= " AND p.municipio LIKE '".$request['municipio']."'";
        endif;
        
        if( $request['ciudad'] != '' ):
            $consulta .= " AND p.city LIKE '".$request['ciudad']."'";
        endif;
        
        //-- Se hace la consulta
        $todosLosRegistros = $db->exec( $consulta );

        //-- Si tiene registros
        if(count($todosLosRegistros)!=0 ):
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=reporta-m-'.date("Y-m-d").'-data.csv');
        
            $fp = fopen('php://output', 'w');
            
            //-- Monta el encabezado 'latitud,longitud,fecha,enfermedad,sexo,pais,ciudad,numicipio'
            $encabezado = 
                    array('Latitud',
                        'Longitud',
                        'Reportado el',
                        'Enfermedad',
                        'Sexo',
                        'Pais',
                        'Ciudad',
                        'Municipio');
            
            fputcsv($fp, $encabezado);
            
            foreach ($todosLosRegistros as $campos) {
                fputcsv($fp, $campos);
            }

            fclose($fp);
            
        else:
            
            //Se indica que el contenido del template lo tomara de home.html
            $f3->set('content','descargar.html');
            $f3->set('menu','menu.html');

            /*Se obtiene el arreglo de la sesion para saber si existe el key user*/
            $sesion = $f3->get('SESSION');

            /*Si no existe se declara nula*/
            if(!array_key_exists("user",$sesion)){
                    $f3->set('usuario','');
            }else{
                $f3->set('usuario',$f3->get('SESSION.user'));
                // Inicializamos la variable
                $f3->set('flash',"Algo no esta bien, verifique el valos de los campos ya algunos no coinciden");
            }

            echo Template::instance()->render('layout.html');
            
        endif;
        
    }
);


/**
 * @author Oscar Galindez <oscarabreu19@gmail.com>
 * @todo Controlador para el manejo de 'Mi Cuenta' para
 * el usuario en sesion
 */
$f3->route('GET|POST @miCuenta: /miCuenta',
	function($f3) use ($db) {
    
            //-- Se debe volver a instanciar el objeto de tipo sesion para poder acceder a los datos globales si no no funcionara!!!
            //new Session();
            
            //-- Obtiene la peticion
            $request = $f3->get("REQUEST");
            
            /**
             * ---
             * 1:   Mostrar: formulario con los dato del usuario en sesion.
             * -
             * 
             * 2:   Editar: edita los datos en la bd.
             */
            switch ( $request['menuOpc'] )
            {
                case '1':   //-- Mostrar los datos
                    
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        //--    Llama al modelo
                        $f3->set('misDatos',new DB\SQL\Mapper($db,'Usuario'));

                        //-- Carga al objeto
                        $f3->get('misDatos')->load(array('idUsuario=?',$f3->get('SESSION.id')));
                        
                        //-- Prepara los datos para la vista
                        $f3->get('misDatos')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));

                        //-- Preparamos la vista
                        $f3->set('content','miCuenta/index.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash',null);
                        
                        //-- Llama la vista
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

                case '2':   //-- Editar 
                    
                    //-- Se verifica si el usuario tiene la sesion iniciada
                    if( ('' !== $f3->get('SESSION.user')) && (NULL !== $f3->get('SESSION.user')))
                    {
                        
                        //--    Llama al modelo
                        $f3->set('misDatos',new DB\SQL\Mapper($db,'Usuario'));

                        //-- Carga al objeto
                        $f3->get('misDatos')->load(array('idUsuario=?',$f3->get('SESSION.id')));
                        
                        //--    Cargamos los valores que tenemos del formulario
                        //-- Modifica los valores que convienen
                        $f3->get('misDatos')->set('email',$request['email']);
                        $f3->get('misDatos')->set('password',$request['password']);
                        
                        //--    Salva en BD
                        $f3->get('misDatos')->save();
                        
                        //--    Cargamos los valores que tenemos del formulario
                        $f3->get('misDatos')->copyTo('POST');
                        
                        //-- Datos del usuario en sesion
                        $f3->set('usuario',$f3->get('SESSION.user'));
                        
                        //-- Preparamos la vista
                        $f3->set('content','miCuenta/index.html');
                        
                        //-- Carga el menu
                        $f3->set('menu','menu.html');
                        
                        // Inicializamos la variable
                        $f3->set('flash','Sus datos fueron modificados satisfactoriamente');
                        
                        //-- Llama la vista
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
               
                    
                default:
                    break;
            }
		
	}
);

$f3->run();
