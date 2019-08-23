
var pcache = '';

function confirmDeleteBackup( event ) 
{
    var msg = document.getElementById( 'releases_confirm_delete_version' ).title;
    if ( confirm( msg ) ) {
        return true;
    }

    event.preventDefault();
    return false;
}

document.addEventListener( 'DOMContentLoaded', function () 
{
    var forms = document.getElementsByTagName( 'form' );
    for ( var i = 0; i < forms.length; i++ ) {
        var input = document.createElement( 'input' );
        input.type = 'hidden';
        input.name = 'pcache';
        input.value = pcache;
        input.id = "pcache" + i.toString();
        forms[i].appendChild(input);
    }

    var deleteButtons = document.getElementsByClassName( 'backup_delete' );
    for ( var i = 0; i < deleteButtons.length; i++ ) {
        deleteButtons[i].addEventListener( 'click', confirmDeleteBackup );
    }

} );
