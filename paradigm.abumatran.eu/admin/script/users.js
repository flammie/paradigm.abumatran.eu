var FormAddUserListener = ( function(){

	var __init__ = function(){
		getLang();
		setListeners();
		FormMngUsersListener.__init__();
	};

	var getLang = function(){
		$.get( $( location ).attr( 'pathname' ) + "/class/task.php", { 'getlang': 'all' } )
		.done( function( data ){
			data = $.parseJSON( data );
			$.each( data, function( key, val ){
				$( 'select#lang' ).append( '<option value="' + val.id_lang + '">' + val.shortname_lang + '</option>' );
			});
		});
	}

	var sortLang = function(){
		$( "#lang" ).html( $( "#lang option" ).sort( function( a, b ){
			return a.text == b.text ? 0 : a.text < b.text ? -1 : 1
		} ) );
	};

	var setListeners = function(){
		$( "form#form_add_user" ).submit( function( eventObject ){
			eventObject.preventDefault();
			var form_values = $( "form#form_add_user" ).serialize();
			$.post( $( location ).attr( 'pathname' ) + "/class/user.php", form_values, function( response ){
				$( "div#add_user_msg" ).html( response );
				$( "div#add_user_msg" ).fadeIn( 160, "linear" );
				$( "div#add_user_msg" ).fadeOut( 2600, "linear" );
			        FormMngUsersListener.__init__();
			});
		});
	};

	return {
		__init__: __init__,
	};

})();

var FormMngUsersListener = ( function(){

	var users = {};
	var update = -1;

        var __init__ = function(){
		getUsers();
        };

	var getUsers = function(){
		$.get( $( location ).attr( 'pathname' ) + "/class/user.php", { list: "all" } )
		.done( function( data ){
			var listUsers = $.parseJSON( data );
			var toappend = "<tr><th>#</th><th>name</th><th>activ.</th><th>edit</th><th>del.</th></tr>";
			$.each( listUsers, function( key, val ){
				users[ val.id ] = [ val.name, val.email ];
				toappend += "<tr name= '" + val.id + "' class='table_users_content'><td>" + val.id + "</td>",
				toappend += "<td>" + val.name + "</td>";
				var activ = "<i class='fa fa-square-o'></i>";
                                if( val.activate_user == 1 ){ activ = "<i class='fa fa-check-square-o'></i>"; }
                                toappend += "<td title='De/Activate User'><span class='activ_user' name='" + val.id + "' id='activ_" + val.id + "'>" + activ + "</span></td>";
				toappend += "<td title='Edit User' class='table_users_edit'><i class='fa fa-pencil-square-o'></i></td>";
				toappend += "<td title='Delete User' class='table_users_delete'><i class='fa fa-trash-o'></i></td></tr>";
			});
			$( "div#listusers table" ).html( toappend );
			setOptions();		
		});
	};

	var setOptions = function(){
		setActivate();
		setEdit();
		setDelete();
	};

	var setActivate = function(){
		$( "span.activ_user" ).off().on( "mousedown", function( eventObject ){
			$( this ).children( "i" ).addClass( "fa-check-square" );
		});
		$( "span.activ_user" ).off().on( "mouseup", function( eventObject ){
			var userid = $( this ).attr( "name" );
			$.get( "class/user.php", { "activate": userid }, function( response ){
				FormMngUsersListener.__init__();			
			});
		});
	};

	var setEdit = function(){
		$( 'td.table_users_edit' ).off().on( 'click', editUser );	
	};

	var setDelete = function(){
		$( 'td.table_users_delete' ).off().on( 'click', deleteUser );
	};

	var editUser = function( eventObject ){
		eventObject.preventDefault();
		var tmpEl = $( eventObject.currentTarget );
		var tmpId = tmpEl.parent( 'tr' ).attr( 'name' );
		update = tmpId;
		if( update != -1 ){
			$( 'input#name' ).val( users[ tmpId ][ 0 ] );
			$( 'input#email' ).val( users[ tmpId ][ 1 ] );
			$( 'button' ).text( 'Update User' );
			$( 'input#pwd' ).prop( 'required', false );
			setLang();
			setListeners();
		}
	};

	var deleteUser = function( eventObject ){
		eventObject.preventDefault();
		var tmpEl = $( eventObject.currentTarget );
                var tmpId = tmpEl.parent( 'tr' ).attr( 'name' );
		if( tmpId != -1 ){
			var check = confirm( 'Are you sure you want to delete this user?' );
			if( check ){
				$.get( "class/user.php", { "delete": tmpId }, function( response ){
                	                FormMngUsersListener.__init__();
        	                });
			}
		}
	};

	var setLang = function(){
		 $( 'select#lang option' ).prop( 'selected', false );
		$.post( $( location ).attr( 'pathname' ) + "/class/user.php", { 'getlang': update } )
                .done( function( data ){
                        data = $.parseJSON( data );
                        $.each( data, function( key, val ){
				$( 'select#lang option[value="' + val[ 0 ] + '"]' ).prop( 'selected', true );
                        });
                });
	};

	var setListeners = function(){
                $( "form#form_add_user" ).off().submit( function( eventObject ){
                        eventObject.preventDefault();
                        var form_values = $( "form#form_add_user" ).serialize();
			form_values += '&update=' + update;
                        $.post( $( location ).attr( 'pathname' ) + "/class/user.php", form_values, function( response ){
                                $( "div#add_user_msg" ).html( response );
                                $( "div#add_user_msg" ).fadeIn( 160, "linear" );
                                $( "div#add_user_msg" ).fadeOut( 2600, "linear" );
				$( 'button' ).text( 'Validate User' );
				$( 'select#lang option' ).prop( 'selected', false );
				update = -1;
				$( 'input#pwd' ).prop( 'required', true );
				$( 'input#name' ).val( "" );
	                        $( 'input#email' ).val( "" );
				$( 'input#pwd' ).val( "" );
                                FormMngUsersListener.__init__();
                        });
                });
	};

	return {
		__init__: __init__,
	};
})();

$( document ).ready( function(){
        $.ajaxSetup ({
	        cache: false,
        });
});

