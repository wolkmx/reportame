//Funcion para capturar el avance del formulario de reporte de incidencia
$(".validarEnfermedad").click(function(event){
    
    var valido = true;
    var campos = '';
    
    if( $("#nombre").val() == "" ){
        valido = false;
        campos += '\nNombre [Obligatorio]';
    }
    
    if( !valido ){
            alert("CAMPO:\n" + campos);
            return false;
    }
    
});