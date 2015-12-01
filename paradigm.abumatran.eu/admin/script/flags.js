var TasksFilter = ( function(){

        var tabLang = [];
        var selectedTaskId = -1;
        var taskNbSurface = {};
        var taskLangId = {};

	var initContainers = function(){
                $( 'div#listalltasks' ).html( '' );
		$( 'div#export_flag_link' ).html( '' );
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

        var getLongLangById = function( idLang ){
                var longname = -1;
                $.each( tabLang, function( key, val ){
                        if( val.id_lang == idLang ){
                                longname = val.longname_lang;
                        }
                });
                return longname;
        };

        var getTasks = function( taskId ){
		initContainers();
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
                                        selectedTaskId = parseInt( val.id_task );
                                }
                                var toappend = "<div class='" + c + "' name='" + val.id_task + "'>";
                                toappend += "<span>Created</span><span>" + date_add_task + "</span>";
                                toappend += "<span class='lang_task'>Language</span><span> " + getLangById( val.id_lang ) + " (" + getLongLangById( val.id_lang ) + ")</span>";//buildLangList()
                                toappend += "<span>OOV</span><span> " + $.number( taskNbSurface[ val.id_task ] ) + "</span>";
                                toappend += "</div>";
                                $( 'div#listalltasks' ).append( toappend );
                        });
                        setListener();
                });
        };

        var setListener = function(){
		$( 'div#listalltasks div' ).off().on( 'click', selectTask );
	};

	var selectTask = function( eventObject ){
		var tmpSelected = eventObject.currentTarget;
		if( $( tmpSelected ).hasClass( 'taskblock_active' ) ){
                        $( "div.taskblock" ).removeClass( 'taskblock_active' );
			$( 'table#table_listflags' ).html( '' );
			$( 'ul#listfilters' ).html( '' );
                }else{
                        $( "div.taskblock" ).removeClass( 'taskblock_active' );
                        $( tmpSelected ).toggleClass( 'taskblock_active' );
			selectedTaskId = $( eventObject.currentTarget ).attr( 'name' );
			Flags.__init__( selectedTaskId, taskLangId[ selectedTaskId ] );	
		}
        };

	return {
		getTasks: getTasks,
		getLangs: getLangs,
		getLangById: getLangById,
		getLongLangById: getLongLangById,
	};
})();

var Flags = ( function(){

	var __init__ = function( taskId, langId ){
		$( 'div#export_flag_link' ).html( '' );
		setButton();
		if( taskId === undefined ){ taskId = -1; };
		if( langId === undefined ){ langId = -1; };
		setButton( taskId );
		query4flags( taskId );
		query4filters( langId );
	};

	var setButton = function( taskId ){
		$( 'body' ).off().on( 'click', 'button#button_export_flags', function( eventObject ){
			eventObject.preventDefault();
			var filtered = getCheckedFilters();
			if( filtered.length == 0 ){
				filtered = getAllFilters();
			}
			$.get( "class/flag.php", { 'export': filtered, 'task': taskId }, function( response ){
				if( response != -1 ){
					response = $.parseJSON( response );
					$( 'div#export_flag_link' ).hide();
					$( 'div#export_flag_link' ).html( "<a href='http://paradigm.abumatran.eu/admin/" + response + "'>Download</a>" );
					$( 'div#export_flag_link' ).show();
				}
			});
		});		
	};

	var getAllFilters = function(){
		var toreturn = [];
		$.each( $( 'ul#listfilters li input' ), function( key, val ){
                        val = $( val );
                        toreturn.push( val.attr( 'name' ) );
                });
		return toreturn;
	};

	var query4flags = function( taskId ){
		$( 'table#table_listflags' ).html( '' );
                $.get( "class/flag.php", { 'list': 'flags', 'task': taskId }, function( response ){
			var data = $.parseJSON( response );
			var toappend = '<tr><th>Surface Form</th><th>Flagged As</th><th>User</th></tr>';
			$.each( data, function( key, val ){
				toappend += '<tr class="table_flags_content" name="' + val.value_flag + '"><td>' + val.value_surface + '</td><td>'
					+ val.value_flag + '</td><td>' 
					+ val.name_user + '</td></tr>';
			});
			$( 'table#table_listflags' ).html( toappend );
                });  
	};

	var query4filters = function( langId ){
		$( 'ul#listfilters' ).html( '' );
		$.get( "class/flag.php", { 'filter': 'flags', 'lang': langId }, function( response ){
			var data = $.parseJSON( response );
			var toappend = '';
			$.each( data, function( key, val ){
				toappend += '<li name="' + val.value_flag + '">';
				toappend += '<input id="' + val.value_flag + '" name="' + val.value_flag + '" placeholder="' + val.value_flag + '" type="checkbox">'
				toappend += '<label for="' + val.value_flag + '">' + val.value_flag + ' (' + val.count_flagged + ')</label>';
				
			});
			$( 'ul#listfilters' ).html( toappend );
			$( 'ul#listfilters' ).append( '<button class="pure-button" type="submit" name="button_export_flags" id="button_export_flags">Export</button>' );
			$( 'ul#listfilters' ).off().on( 'click', 'input', setFilters );
		});
	};

	var setFilters = function( eventObject ){
		var checkedFilters = getCheckedFilters();
		if( checkedFilters.length > 0 ){
			$( 'table#table_listflags tr.table_flags_content' ).hide();
			$.each( checkedFilters, function( key, val ){
				$( 'table#table_listflags tr.table_flags_content[name="' + val + '"]' ).show();
			});
		}else{
			$( 'table#table_listflags tr.table_flags_content' ).show();
		}
	};

	var getCheckedFilters = function(){
		var toreturn = [];
		$.each( $( 'ul#listfilters li input' ), function( key, val ){
			val = $( val );
			if( val.prop( 'checked' ) ){
				toreturn.push( val.attr( 'name' ) );
			}
		});
		return jQuery.unique( toreturn );
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

