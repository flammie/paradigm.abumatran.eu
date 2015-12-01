var Menu = ( function(){

	var __init__ = function(){
		$( "section" );
	};

	var getContent = function( choice ){
                switch ( choice ) {
                        case "overview":
				$.holdReady( true );
				$( "section" ).load( "html/overview.html", function(){
					$.getScript( "script/overview.js" , function(){
						$.holdReady( false );
						TasksDetails.__init__();
						UsersDetails.__init__();
					});
				});
                        break;

                        case "tasks":
				$.holdReady( true );
	                        $( "section" ).load( "html/tasks.html", function(){
					$.getScript( "script/tasks.js", function(){
						$.holdReady( false );
						FormMngTaskListener.__init__();
					});
				});
				$.getScript( "script/tasks.js" );
                        break;
		
			case "languages":
				$.holdReady( true );
				$( "section" ).load( "html/languages.html", function(){ 
	                                $.getScript( "script/languages.js", function(){
						$.holdReady( false );
					        FormAddLanguageListener.__init__();
					});
				});
			break;

                        case "users":
				$.holdReady( true );
                                $( "section" ).load( "html/users.html", function(){
					$.getScript( "script/users.js", function(){
						$.holdReady( false );
					        FormAddUserListener.__init__();	
					});
				});
                        break;

                        case "export":
				$.holdReady( true );
                                $( "section" ).load( "html/export.html", function(){
					$.getScript( "script/export.js", function(){
						$.holdReady( false );
						Export.__init__();
					});
				});
                        break;
	
			case "flags":
				$.holdReady( true );
                                $( "section" ).load( "html/flags.html", function(){
					$.getScript( "script/flags.js", function(){
						$.holdReady( false );
					        TasksFilter.getLangs();	
					});
				});
                        break;

                        default:
                                $( "section" ).load( "html/overview.html" );
                        break;
                };	
	};

	return {
		getContent: getContent,
	};

})();

var DisplayManager = ( function(){

	var __init__ = function() {
		$( "body" );
	};

	var setURL = function( hash ){
		url = $( location ).attr( 'href' ).replace( "index.php", "" );
                history.pushState( null, null, url );
		document.location.hash = hash;
	};

	var setLink = function( node ){
		$( "nav ul li a" ).removeClass( "navmenu_selected" );
		node.addClass( "navmenu_selected" );
	};

	var setContent = function( choice ){
		$( "section" ).addClass( "main_content" );
		Menu.getContent( choice );
	};

	return {
		setURL: setURL,
		setLink: setLink,
		setContent: setContent, 
	};

})();

var EventManager = ( function(){

        var __init__ = function() {
		$( "body" );
        };

	var run = function( node ){
		var choice = node.attr( "id" );
		window.localStorage.setItem( "current_page", choice );
		DisplayManager.setLink( node );
		DisplayManager.setURL( choice );
		DisplayManager.setContent( choice );
	};

	return {
		run: run,
	};

})();

var ListenerManager = ( function() {

	var __init__ = function() {
		setLinks();
	};

	var setLinks = function() {
		$( "nav ul li a" ).hover( function() {
			$( this ).addClass( "navmenu_hover" );
		}, function() {
			$( this ).removeClass( "navmenu_hover" );
		});

		$( "nav ul li a" ).click( function( eventObject ) {
			eventObject.preventDefault();
			EventManager.run( $( this ) );
		});
	};

	return{
		__init__: __init__,
	};
	
})();

$( document ).ready( function() {
	$.xhrPool = [];
        $.xhrPool.abortAll = function(){
                $( this ).each( function( idx, jqXHR ){
                        jqXHR.abort();
                });
                $.xhrPool.length = 0
        };
        $.ajaxSetup({
                cache: false,
                beforeSend: function( jqXHR ){
                        $.xhrPool.push( jqXHR );
                        $( "div#loading_stuff" ).show();
                },
                complete: function( jqXHR ){
                        var index = $.xhrPool.indexOf( jqXHR );
                        if( index > -1 ){
                                $.xhrPool.splice( index, 1 );
                        }
                        $( "div#loading_stuff" ).hide();
                }
        });

	ListenerManager.__init__();
	if( window.localStorage.getItem( "current_page" ) != null ){
		$( "nav li a#" + window.localStorage.getItem( "current_page" ) ).trigger( "click" );
	}else{
		$( "nav li a#overview" ).trigger( "click" );
	}
});
