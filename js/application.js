if ($)
{
	var panel_nuevo_desplegado = false;

	// Controlar la expansión del panel de creación de contenido
	$(".panel-nuevo form .url-contenido #url").focus(function(th){
		if(panel_nuevo_desplegado==false)
		{
			$(".panel-nuevo").animate( {"max-height": "250px"} , 300);
			panel_nuevo_desplegado=true;
		}
	});

	// Controlar la disminución del panel de creación de contenido
	$(":not(.panel-nuevo form .url-contenido #url)").click(function(th){
		if($(".panel-nuevo form").find(th.srcElement).length == 0 && panel_nuevo_desplegado==true && $(".panel-nuevo form .url-contenido #url").val().length == 0 && $(".panel-nuevo form .titulo-contenido #titulo").val().length == 0)
		{
			$(".panel-nuevo").animate( {"max-height": "44px"} , 300);
			panel_nuevo_desplegado=false;
		}
	});

	// Controlar la visibilidad del campo "contraseña" cuando se crea un enlace
	$(".panel-nuevo form .seguridad-contenido #seguridad").change(function(){
		if ($(".panel-nuevo form .seguridad-contenido #seguridad").val()=="1")
		{
			$(".panel-nuevo form .password-contenido").animate( {"max-height": "70px"} , 300);
			$(".panel-nuevo form .password-contenido #password").attr("required", "required");
		}
		else
		{
			$(".panel-nuevo form .password-contenido").animate( {"max-height": "0px"} , 300);
			$(".panel-nuevo form .password-contenido #password").removeAttr("required");
		}
	});

	$(".panel-nuevo form").submit(function(e){

		$(".panel-nuevo form").hide();
		$(".panel-nuevo .ajax-loader").addClass("ajax-loader-visible");
	})

	// Controlar los atributos data-confirm para pedir confirmación antes de ir a enlaces (por ejemplo de eliminación)
	$(document).ready(function () {
    $("[data-confirm]").click(function (event) {
        var texto = event.currentTarget.attributes['data-confirm'].value;
        event.preventDefault(); // Evitar el acceso al enlace
        if (confirm(texto)) // Preguntar
        {
        	// Si la respuesta es afirmativa, continuar
            $(event.currentTarget).unbind('click');
            event.currentTarget.click();
        }
    });

    // Funcionalidad de las listas del panel de compartir
    $(".lista-cuentas").sortable({
    		items: "li:not(.no-cuenta)",
            connectWith: ".lista-seleccionadas",
            placeholder: "cuenta-externa"
        }).disableSelection();
    $(".lista-seleccionadas").sortable({
            items: "li:not(.no-cuenta)",
            connectWith: ".lista-cuentas",
            placeholder: "cuenta-externa"
        }).disableSelection();
});
}