
var FormMngTaskListener = ( function(){

	var tabLang = new Array();

        var __init__ = function(){
		getLangs();
        };

	var getLangs = function(){
		$.post( "class/task.php", { "getlang": "all" }, function( response ){
			response = $.parseJSON( response );
			tabLang = response;
			getTasks();
		});
	};

	var getTasks = function(){
		/*
                $.get( $( location ).attr( 'pathname' ) + "/class/task.php", { list: "all" } )
                .done( function( data ){
			if( data.length == 0 ){ return 0; };
			var tmpDone = 0;
                        var listTasks = $.parseJSON( data );
			var toappend = "<tr class='table_tasks_head'><th>Date</th><th>Lang</th><th>Surface Forms</th><th>Validated Candidates</th><th>Cross-Val</th><th>Activate</th><th>Delete</th></tr>";
                        $.each( listTasks, function( key, val ){
                                toappend += "<tr name='" + val.id_task + "' class='table_tasks_content'>";
				date_add_task = val.date_add_task[6] + val.date_add_task[7] + "/" + val.date_add_task[4] + val.date_add_task[5] + "/" + val.date_add_task[0] + val.date_add_task[1] + val.date_add_task[2] + val.date_add_task[3];
				toappend += "<td>" + date_add_task + "</td>";
				toappend += "<td name='" + val.lang_task + "' class='pick_lang'><span class='init_lang_task'>" + val.lang_task + "</span>" + buildLangList() + "</td>";
				toappend += "<td>" + val.surface_count +"</td>";
				toappend += "<td>" + val.validated_candidates + "</td>";
				var xval = "<i class='fa fa-square-o'></i>";
				if( val.cross_validate == 1 ){ xval = "<i class='fa fa-check-square-o'></i>"; }
				toappend += "<td title='De/Activate Cross-validation'><span class='xval_task' name='" + val.id_task + "' id='xval_" + val.id_task + "'>" + xval + "</span></td>";
				var activ = "<i class='fa fa-square-o'></i>";
				if( val.activate_task == 1 ){ activ = "<i class='fa fa-check-square-o'></i>"; }
				toappend += "<td title='De/Activate Task'><span class='activ_task' name='" + val.id_task + "' id='activ_" + val.id_task + "'>" + activ + "</span></td>";
				toappend += "<td title='Delete Task' class='table_tasks_delete'><i class='fa fa-trash-o'></i></td></tr>";
	
                        });
			$( "table#table_listtasks" ).html( toappend );
			setOptions();
                });*/
		$.get( $( location ).attr( 'pathname' ) + "/class/task.php", { list: "all" } ).done( function( data ){
			data = $.parseJSON( data );
			$( "table#table_listtasks" ).html( '<tr class="table_tasks_head"><th>' + data.tasks + '</th></tr>' );
		});
        };

        var setOptions = function(){
		$( "select.list_lang_pick" ).on( "change", function( eventObject ){
                        eventObject.preventDefault();
			//alert( $( this ).children( "option:selected" ).attr( "name" ) );
                });    
                setActivate();
		setXval();
		setLangPicker();
        };

	var buildLangList = function(){
		toshow = "<ul class='list_lang_topick'>";
                $.each( tabLang, function( key, val ){
	                toshow += "<li name='" + val.id_lang + "'>" + val.shortname_lang + "</li>";
                });
		toshow += "</ul>";
		return toshow;
	}

	var setLangPicker = function(){
		$( "ul.list_lang_topick" ).hide();	
		$( "td.pick_lang" ).on( "mousedown", function( eventObject ){
			$( this ).children( "span.init_lang_task" ).toggle();
			$( this ).children( "ul.list_lang_topick" ).toggle();
			$( this ).children( "ul.list_lang_topick" ).css( { "top": eventObject.pageY, "left": eventObject.pageX - 15 } );
		});
                $( "td.pick_lang" ).on( "mouseup", function( eventObject ){
			tmpTaskId = $( this ).parent( "tr.table_tasks_content" ).attr( "name" );
			$( this ).children( "span.init_lang_task" ).toggle();
			$.each( $( this ).children( "ul" ).children( "li" ), function( key, val ){
				if( $( val ).is( ":hover" ) ){
					$( val ).parent( "ul" ).prev( "span" ).text( $( val ).text() );
					$.get( "class/task.php", { "setlang": tmpTaskId, "value": $( val ).attr( "name" ) }, function( response ){
					});
				}
			});
                        $( this ).children( "ul.list_lang_topick" ).toggle();
                });
	};

	var setXval = function(){
		$( "span.xval_task" ).on( "mousedown", function( eventObject ){
                        $( this ).children( "i" ).addClass( "fa-check-square" );
                });
                $( "span.xval_task" ).on( "mouseup", function( eventObject ){
                        var taskid = $( this ).attr( "name" );
                        $.get( "class/task.php", { "xval": taskid }, function( response ){
                                FormMngTaskListener.__init__();
                        });
                });	
	};

	var setActivate = function(){
                $( "span.activ_task" ).on( "mousedown", function( eventObject ){
                        $( this ).children( "i" ).addClass( "fa-check-square" );
                });
                $( "span.activ_task" ).on( "mouseup", function( eventObject ){
                        var taskid = $( this ).attr( "name" );
                        $.get( "class/task.php", { "activate": taskid }, function( response ){
                                FormMngTaskListener.__init__();
                        });
                });
        };

	var refreshList = function(){
		$( "div#listtasks table" ).html( " " );
		getTasks();
	};

	return {
		__init__: __init__,
		refreshList: refreshList,
	};	
})();

$( document ).ready( function(){
        $.ajaxSetup ({
                cache: false,
        });
	FormMngTaskListener.__init__();
});
