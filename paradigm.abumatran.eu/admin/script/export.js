var Export = ( function(){

	var tabLang = [];
	var format = -1;
        var tabLang = [];
        var selectedTaskId = -1;
        var taskNbSurface = {};
        var taskLangId = {};

	var __init__ = function(){
		getLangs();
		 $( "div#export_link" ).html( '' );
		$( 'input[name="format_export"]' ).on( 'click', setFormat );
		$( 'button#button_export' ).on( 'click', submitExport );
	};

	var setFormat = function( eventObject ){
		var el = $( eventObject.currentTarget );
		format = el.attr( 'value' );
	};

	var checkSelectedTask = function(){
		if( selectedTaskId == -1 ){
                        $( "div#export_msg" ).stop();
                        $( "div#export_msg" ).html( 'Please select a task to export.' );
                        $( "div#export_msg" ).fadeIn( 150, "linear" );
                        $( "div#export_msg" ).fadeOut( 4000, "linear" );
                        return false;
                }
		return true;
	}	

	var checkSelectedFormat = function(){
		if( format == -1 ){
			$( "div#export_msg" ).stop();
                        $( "div#export_msg" ).html( 'Please select a format.' );
                        $( "div#export_msg" ).fadeIn( 150, "linear" );
                        $( "div#export_msg" ).fadeOut( 4000, "linear" );
			return false;
		}
		return true;
	}

	var submitExport = function( eventObject ){
		eventObject.preventDefault();
		if( checkSelectedTask() && checkSelectedFormat() ){
			$.post( $( location ).attr( 'pathname' ) + "/class/export.php", { 'task': selectedTaskId, 'format': format } )
			.done( function( response ){
			//	$( "div#export_link" ).html( response );
				$( "div#export_link" ).hide();
				$( "div#export_link" ).html( "<a href='http://paradigm.abumatran.eu/admin/" + response + "'>Download</a>" );
				$( "div#export_link" ).show();
			});
		}
	};

        var getLangs = function(){
                $.get( "class/task.php", { "getlang": "all" }, function( response ){
                        if( response.length == 0 ){ return 0; };
                        response = $.parseJSON( response );
                        tabLang = response;
                        getTasks();
                });
        };

	var getLangById = function( idLang ){
                var shortname = -1;
                $.each( tabLang, function( key, val ){
                        if( val.id_lang == idLang ){
                                shortname = val.shortname_lang;
                        }
                });
                return shortname;
        };

        var getLonglangById = function( idLang ){
                var longname = -1;
                $.each( tabLang, function( key, val ){
                        if( val.id_lang == idLang ){
                                longname = val.longname_lang;
                        }
                });
                return longname;
        };

        var getTasks = function( taskId ){
                $.get( $( location ).attr( 'pathname' ) + "/class/task.php", { "list": "all" }, function( data ){
                        if( data.length == 0 ){ return 0; };
                        var tmpDone = 0;
                        var listTasks = $.parseJSON( data );
                        $.each( listTasks, function( key, val ){
                                taskNbSurface[ val.id_task ] = val.nb_surface;
                                taskLangId[ val.id_task ] = val.id_lang;
                                var date_add_task = val.date_create[6] + val.date_create[7] + "/" + val.date_create[4] + val.date_create[5] + "/" + val.date_create[0] + val.date_create[1] + val.date_create[2] + val.date_create[3];
                                var activ = "<i class='fa fa-square-o'></i>";
                                if( val.activate_task == 1 ){ activ = "<i class='fa fa-check-square-o'></i>"; }
                                var c = 'taskblock';
                                if( taskId && ( taskId == val.id_task ) ){
                                        c += ' taskblock_active';
                                }
                                var toappend = "<div class='" + c + "' name='" + val.id_task + "'>";
                                toappend += "<span>Created</span><span>" + date_add_task + "</span>";
                                toappend += "<span class='lang_task'>Language</span><span> " + getLangById( val.id_lang ) + " (" + getLonglangById( val.id_lang ) + ")</span>";//buildLangList()
                                toappend += "<span>OOV</span><span> " + $.number( taskNbSurface[ val.id_task ] ) + "</span>";
                                toappend += "</div>";
                                $( 'div#listalltasks' ).append( toappend );
                        });
                        setOptions();
                });
        };

	var setOptions = function(){
		$( 'div#listalltasks div' ).off().on( 'click', selectTask );
	};

	var selectTask = function( eventObject ){
		 $( "div#export_link" ).html( '' );
		eventObject.preventDefault();
		var tmpSelected = eventObject.currentTarget;
                if( $( tmpSelected ).hasClass( 'taskblock_active' ) ){
                        $( "div.taskblock" ).removeClass( 'taskblock_active' );
			selectedTaskId = -1;
                }else{
                        $( "div.taskblock" ).removeClass( 'taskblock_active' );
                        $( tmpSelected ).toggleClass( 'taskblock_active' );
			selectedTaskId = $( tmpSelected ).attr( 'name' );
                }
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
