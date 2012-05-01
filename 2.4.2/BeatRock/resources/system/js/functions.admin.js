/**
 * Funciones de la administración.
 *
 * InfoSmart. Todos los derechos reservados.
 * Copyright 2011 - Iván Bravo Bravo.
 * http://www.infosmart.mx/ - http://www.jquery.com/
**/

NotesSave = null;

$(document).on('ready', function()
{
	FixIt();

	$('.notes').on('keyup', SaveNotes);

	setInterval(FixIt, 3000);
});

function FixIt()
{
	Height = $('.c2').height();
	$('.c1').height(Height);
}

function SaveNotes()
{
	if(NotesSave !== null)
		clearTimeout(NotesSave);

	Value = $('.notes').val();

	$.post(Path_Now + '/actions/save.php', {'type': 'notes', 'value': Value}, function()
	{
		$('#notes-save').fadeIn('slow');		
		NotesSave = setTimeout(function() { $('#notes-save').fadeOut('slow'); }, 5000);
	});
}