//Funcion para capturar el avance del formulario de reporte de incidencia
$(".validarPerfil").click(function(event){
    
    var valido = true;
    var campos = '';
    
    if( $("#fechaNacimiento").val() == "" ){
        valido = false;
        campos += '\nFecha de Nacimiento [Obligatorio]';
    }
    
    if( $("#pais").val() == "" ){
        valido = false;
        campos += '\nPais [Obligatorio]';
    }
    
    if( $("#municipio").val() == "" ){
        valido = false;
        campos += '\nMunicipio [Obligatorio]';
    }
    
    if( $("#ciudad").val() == "" ){
        valido = false;
        campos += '\nCiudad [Obligatorio]';
    }
    
    if( !valido ){
            alert("CAMPOS:\n" + campos);
            return false;
    }
    
});