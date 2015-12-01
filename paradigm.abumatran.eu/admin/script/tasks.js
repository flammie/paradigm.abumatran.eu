var buildChart = function( data, node ){
	node.highcharts( {
		chart: {
			height: 275,
			BackgroundColor: null,
			BorderWidth: null,
			Shadow: false,
		},
		title: { text: null, },
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: false
				},
				showInLegend: true,
			}
		},
		series: data
	});
};

var FormMngTaskListener = ( function(){

	var tabLang = [];
	var selectedTaskId = -1;
	var taskNbSurface = {};
	var taskLangId = {};
	var selectedTaskDetails = {};
	var annotators = {};
	var users = {};

	var __init__ = function(){
		initContainers();
		getLangs();
	};

	var initContainers = function(){
		$( 'div#listalltasks' ).html( '<label>Tasks</label>' );
		emptyCentralContainer();
		emptyUsersContainer();
	};

	var emptyUsersContainer = function(){
		$( 'div#listuserstask div#listannotators' ).html( '<label>Annotators</label>' );
		$( 'div#listuserstask div#listothers' ).html( '<label>Other Users</label>' );
	};

	var emptyCentralContainer = function(){
		$( 'div#chart_surface' ).html( '' );
		$( 'div#details_task' ).html( '' );
		$( 'div#edit_task' ).html( '' );
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
					selectedTaskId = parseInt( val.id_task );
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
		$( "div.taskblock" ).off().on( "click", function( eventObject ){
			eventObject.preventDefault();
			setListener( eventObject );
		});    
		//setActivate();
		//setDelete();
		//setXval();
		//setLangPicker();
	};

	var setListener = function( eventObject ){
		var tmpSelected = eventObject.currentTarget;
		emptyCentralContainer();
		emptyUsersContainer();
		if( $( tmpSelected ).hasClass( 'taskblock_active' ) ){
			$( "div.taskblock" ).removeClass( 'taskblock_active' );
		}else{
			$( "div.taskblock" ).removeClass( 'taskblock_active' );
			$( tmpSelected ).toggleClass( 'taskblock_active' );
			getTaskDetails( tmpSelected );
		}
	};

	var getTaskDetails = function( selectedTask ){
		var tmpId = $( selectedTask ).attr( 'name' );
		selectedTaskId = tmpId;
		$.get( $( location ).attr( 'pathname' ) + "/class/task.php", { get_details: selectedTaskId } )
		.done( function( data ){ 
			if( data.length > 0 ){
				selectedTaskDetails = $.parseJSON( data );
				drawSurface();
				insertDetails();
				insertEditForm();
				getAssociatedUsers();
			}
		});
	};

	var getAssociatedUsers = function(){
		annotators = {};
		users = {};
		$.get( $( location ).attr( 'pathname' ) + "/class/task.php", { "get_users_task": selectedTaskId } )
		.done( function( data ){
			if( data.length > 0 ){
				setAssociatedUsers( data );
			}
		});
		$.get( $( location ).attr( 'pathname' ) + "/class/task.php", { "get_users_notask": selectedTaskId } )
		.done( function( data ){
			if( data.length > 0 ){
				setOtherUsers( data );
			}	
		});
	};

	var setAssociatedUsers = function( data ){
		var tmpUsers = $.parseJSON( data );
		var toappend = "<ul>";
		$.each( tmpUsers, function( key, val ){
			annotators[ val.id_user ] = val;
			var checked = '<i class="fa fa-check-square-o"></i>';
			toappend += '<li><span>' + val.name_user + '</span><span class="untick_user" name="' + val.id_user + '">' + checked + '</span></li>';
		});
		toappend += "</ul>";
		$( 'div#listuserstask div#listannotators' ).append( toappend );
		$( 'div#listuserstask div#listannotators' ).off().on( 'click', 'span.untick_user', manageUser );
	};

	var setOtherUsers = function( data ){
		var tmpUsers = $.parseJSON( data );
		var others = "<ul>";
		$.each( tmpUsers, function( key, val ){
			if( !( val.id_user in annotators ) && !( val.id_user in users ) ){
				users[ val.id_user ] = val;
				var checked = '<i class="fa fa-square-o"></i>';
				others += '<li><span>' + val.name_user + '</span><span class="untick_user" name="' + val.id_user + '">' + checked + '</span></li>';
			}
		});
		others += "</ul>";
		$( 'div#listuserstask div#listothers' ).append( others );
		$( 'div#listuserstask div#listothers' ).off().on( 'click', 'span.untick_user', manageUser );
	};

	var manageUser = function( eventObject ){
		eventObject.preventDefault();
		var tmpNode = $( eventObject.currentTarget );
		var userId = tmpNode.attr( 'name' );
		if( tmpNode.children( 'i' ).hasClass( 'fa-check-square-o' ) ){
			tmpNode.children( 'i' ).removeClass( 'fa-check-square-o' );
			tmpNode.children( 'i' ).addClass( 'fa-square-o' );
		}else{
			tmpNode.children( 'i' ).removeClass( 'fa-square-o' );
			tmpNode.children( 'i' ).addClass( 'fa-check-square-o' );
		}
		$.get( 'class/task.php', { associate_user: tmpNode.attr( 'name' ), task: selectedTaskId }, function( data ){
			if( data.length > 0 ){
				data = $.parseJSON( data );
				emptyUsersContainer();
				getAssociatedUsers();	
			}
		});
	};

	var insertEditForm = function(){
		var edit = '<label>Edit Task</label>';
		edit += '<div><span>Language</span>' + dropDownLang( taskLangId[ selectedTaskId ] ) + '</div>';
		//edit += '<div><span>Add users</span><button name="" value="">+</button></div>';
		edit += '<div><span>Delete task</span><i id="deletetask" class="fa fa-trash-o"></i></div>';
		$( 'div#edit_task' ).append( edit );
		$( 'select#tasklang' ).off().on( 'change', dropDownLangListener );
		$( 'i#deletetask' ).off().on( 'click', deleteTask );
	};

	var deleteTask = function(){
		var check = confirm( 'Are you sure you want to delete this task and everything associated (OOV, lemmas, paradigms, annotations, etc.)?' );
		if( check ){
			$.get( "class/task.php", { "delete_full_task": selectedTaskId }, function( data ){
				FormMngTaskListener.__init__();
			});
	       	}
	};

	var insertDetails = function(){
		var totalSurface = parseInt( taskNbSurface[ selectedTaskId ] );
		var details = '<span>Total Surface Forms</span><span>' + $.number( totalSurface ) + '</span>';
		details += '<span>Annotated</span><span>' + $.number( selectedTaskDetails.done ) + '</span>';
		details += '<span>Flagged</span><span>' + $.number( selectedTaskDetails.flag ) + '</span>';
		details += '<span>Filtered</span><span>' + $.number( selectedTaskDetails.expanded_lock ) + '</span>';
		$( 'div#details_task' ).append( details );
	};

	var drawSurface = function(){
		var totalSurface = parseInt( taskNbSurface[ selectedTaskId ] );
		buildChart( [{
			type: 'pie',
			name: 'Surface',
			data: [
				[ "OOV", Math.floor( ( totalSurface - selectedTaskDetails.done - selectedTaskDetails.flag ) * 100 / totalSurface ) ],
				[ "Flagged", Math.floor( selectedTaskDetails.flag * 100 / totalSurface ) ],
				[ "Annotated", Math.floor( selectedTaskDetails.done * 100 / totalSurface ) ],
		//		[ "Filtered", Math.floor( selectedTaskDetails.expanded_lock * 100 / tmp ) ],
			]
		}], $( 'div#chart_surface' ) );
	};

	var buildLangList = function(){
		toshow = "<ul class='list_lang_topick'>";
		$.each( tabLang, function( key, val ){
			toshow += "<li name='" + val.id_lang + "'>" + val.shortname_lang + "</li>";
		});
		toshow += "</ul>";
		return toshow;
	};

	var dropDownLang = function( selectedLangId ){
		var dd = '<select id="tasklang">';
		$.each( tabLang, function( key, val ){
			var selected = '';
			if( val.id_lang == selectedLangId ){
				selected = 'selected';
			}
			dd += '<option name="' + val.id_lang + '" ' + selected + ' >' + val.shortname_lang + ' -- ' + val.longname_lang + '</option>';
		});
		dd += '</select>';
		return dd;
	};

	var dropDownLangListener = function( eventObject ){
		eventObject.preventDefault();
		var tmpLangId = $( eventObject.currentTarget ).children( 'option:selected' ).attr( 'name' );
		$.get( "class/task.php", { "change_language": tmpLangId, "task": selectedTaskId }, function( response ){
			if( response.length == 0 ){ return 0; };
			resetTasksList( selectedTaskId );
		});
	};

	var resetTasksList = function( taskId ){
		$( 'div#listalltasks' ).html( '<label>Tasks</label>' );
		getTasks( taskId );
	};

	var setLangPicker = function(){
		$( "ul.list_lang_topick" ).hide();	
		$( "td.pick_lang" ).off().on( "mousedown", function( eventObject ){
			$( this ).children( "span.init_lang_task" ).toggle();
			$( this ).children( "ul.list_lang_topick" ).toggle();
			$( this ).children( "ul.list_lang_topick" ).css( { "top": eventObject.pageY, "left": eventObject.pageX - 15 } );
		});
		$( "td.pick_lang" ).off().on( "mouseup", function( eventObject ){
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
		$( "span.xval_task" ).off().on( "mousedown", function( eventObject ){
			$( this ).children( "i" ).addClass( "fa-check-square" );
		});
		$( "span.xval_task" ).off().on( "mouseup", function( eventObject ){
			var taskid = $( this ).attr( "name" );
			$.get( "class/task.php", { "xval": taskid }, function( response ){
				FormMngTaskListener.__init__();
			});
		});	
	};

	var setActivate = function(){
		$( "span.active_task" ).off().on( "click", function( eventObject ){
			var taskid = $( this ).attr( "name" );
			$.get( "class/task.php", { "activate": taskid }, function( response ){
				FormMngTaskListener.__init__();
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
