//Funcion para capturar el avance del formulario de reporte de incidencia
$(".validarMiCuenta").click(function(event){

    var valido = true;
    var campos = '';
    
    if( $("#email").val() == "" ){
        valido = false;
        campos += '\nEmail [Obligatorio]';
    }
    
    if( $("#password").val() == "" ){
        valido = false;
        campos += '\nClave [Obligatorio]';
    }
    
    if( $("#repassword").val() == "" ){
        valido = false;
        campos += '\nRepetir Clave [Obligatorio]';
    }
    
    if( valido && ( $("#password").val() != $("#repassword").val() ) ){
        valido = false;
        var op = 1;
        campos += '\nEl campo "Clave" debe ser igual al campo "Repetir Clave"';
    }
    
    if( !valido ){
        if( op == 1 )
        {
            alert("ADVERTENCIA:\n" + campos);
        }
        else
        {
            alert("CAMPO:\n" + campos);
        }
            
        return false;
    }
    
});
