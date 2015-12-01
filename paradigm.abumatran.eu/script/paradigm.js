var Task = ( function(){

	var content = -1;
	var specific_types = -1;
	var flags = -1;
	var value_surface = -1;
	var id_surface = -1;
	var candidates = -1;
	var previous = -1;
	var next = -1;
	var lang = -1;	

	var setupTask = function( position ){
		retrieveContent( position );
	};

	var retrieveContent = function( position ){
		$.get( '//abumatran.eu:59999/?content=' + position, function( response ){
			if( response && response.length > 0 ){
				content = response[ 0 ];
				specific_types = content[ 'specific_types' ];
				flags = content[ 'flags' ];
				Builder.pushContent( content );
			}
		});
	};

	var displayFlags = function( position ){
		$.each( flags, function( key, val ){
			//if( val.value_flag.toLowerCase() != position.toLowerCase() ){
				$( 'select#categories' ).append( '<option value="' + val.id_flag + '">' + val.value_flag + '</option>' );
			//}
		});
	};

	var setFlags = function( _flags ){
		flags = _flags;
	};

	var displaySpecificTypes = function(){
		$.each( specific_types, function( key, val ){
			var tmpNode = '<li><label for="';
			tmpNode += 'checkboxspecifictype_' + val.id_specific_type;
			tmpNode += '" class="pure-checkbox"><input type="checkbox" id="';
			tmpNode += 'checkboxspecifictype_' + val.id_specific_type;
			tmpNode += '" value="';
			tmpNode += val.id_specific_type;
			tmpNode += '" name="checkboxspecifictype" />&nbsp;';
			tmpNode +=  val.value_specific_type;
			tmpNode += '</label></li>';
			$( 'ul#list_specific_type' ).append( tmpNode );
		});
	};

	var setSpecificTypes = function( types ){
		specific_types = types;
	};

	var setSurface = function( surface ){
		value_surface = surface;
	};
	
	var setIdSurface = function( idSurface ){
		id_surface = idSurface;	
	};

	var displaySurface = function(){
		$( 'span#current_surface_form' ).text( value_surface );	
	};

	var setCandidates = function( _candidates ){
		candidates = _candidates;
	};

	var displayCandidates = function(){
		for( var i = 0 ; i < candidates.id_candidate.length ; i++ ){
			$( 'article#validate_lemma_paradigm' ).append( buildCandidate( i ) );
		}
	};
	
	var buildCandidate = function( id ){
		var expanded = splitExpanded( candidates.value_expanded[ id ] );
		var tmpNode = '<div class="display_box">';
		tmpNode +=  '<div class="wraplemma">';
		tmpNode += '<input type="checkbox" name="validate_candidate[]" value="' + candidates.id_candidate[ id ] + '" id="id_candidate" ';
		if( candidates.validate && candidates.validate[ id ] == "1" ){ tmpNode += 'checked'; }
		tmpNode += ' />';
		tmpNode += '<span class="display_lemma">' + candidates.value_lemma[ id ] + '</span>';
		tmpNode += '<span class="display_paradigm">' + candidates.value_paradigm[ id ] + '</span>';
		//tmpNode += '<span class= "display_probability">' + candidates.probability[ id ] + '</span>';
		tmpNode += '</div>';
		tmpNode += expanded;
		tmpNode += '</div>';
		return tmpNode;
	};

	var splitExpanded = function( expanded ){
		var tmpNode = '<div class="display_expanded">';
		var tmp = expanded.split( "::" );
		$.each( tmp, function( key, val ){
			var couple = val.split( "__" );
			tmpNode += '<span class="expanded_lemma">' + couple[ 0 ] + '</span>';
			tmpNode += '<span class="expanded_paradigm">' + couple[ 1 ] + '</span>';
		});
		tmpNode += '</div>';
		return tmpNode;
	};

	var checkCandidate = function( idCandidate ){
		$.get( '//abumatran.eu:59999/?candidate=' + idCandidate, function( response ){
			if( response && response.length > 0 ){
				//console.log( response[ 0 ] );
			}
		});
	};

	var checkSpecific = function( idSpecific, val ){
		$.get( '//abumatran.eu:59999/?specific=' + idSpecific + '&spevalue=' + val, function( response ){
			if( response && response.length > 0 ){
				//console.log( response[ 0 ] );
			}
		});
	};

	var checkFlag = function( idFlag ){
		$.get( '//abumatran.eu:59999/?flag=' + idFlag, function( response ){
			if( response && response.length > 0 ){
				//console.log( response[ 0 ] );
			}
		});
	};

	var checkPreviousTask = function(){
		$.get( '//abumatran.eu:59999/?previous=' + previous, function( response ){
			if( response && response.length > 0 ){
				content = response[ 0 ];
				Builder.pushContent( content );
			}
		});
	};

	var checkNextTask = function(){
		$.get( '//abumatran.eu:59999/?next=' + next, function( response ){
			if( response && response.length > 0 ){
				content = response[ 0 ];
				Builder.pushContent( content );
			}
		});
	};

	var setPrevious = function( idPrevious ){
		$( '#button_previous' ).hide();
		if( idPrevious != "-1" ){
			previous = idPrevious;
			$( '#button_previous' ).show();
		}
	};
	
	var setFlagged = function( idFlag ){
		if( idFlag ){
			$( 'select#categories option[value="' + idFlag + '"]' ).prop( 'selected', true );
		}
	};

	var setSpecific = function( spec ){
		if( spec ){
			spec = spec.split( "," );
			$.each( spec, function( key, val ){
				$( 'ul#list_specific_type input[value="' + val + '"]' ).prop( 'checked', true );
			});
		}
	};

	var setNext = function( idNext ){
		next = idNext;
	};

	var displayHistory = function( history, flagged ){
		if( history || flagged ){
			var dates = [];
			var dateCheck = []
			$.each( history, function( key, val ){
				var tmp = val.date_done;
				var check = tmp.substr( 0, 8 );
				if( dateCheck.indexOf( check ) == -1 ){
					dateCheck.push( check );
					var year = parseInt( tmp.substr( 0, 4 ) );
					var month = parseInt( tmp.substr( 4, 2 ) ) - 1;
					var day = parseInt( tmp.substr( 6, 2 ) );
					var d = new Date( year, month, day );
					dates.push( d );
				}
			});
                        $.each( flagged, function( key, val ){
                                var tmp = val.date_flag;
                                var check = tmp.substr( 0, 8 );
                                if( dateCheck.indexOf( check ) == -1 ){
                                        dateCheck.push( check );
                                        var year = parseInt( tmp.substr( 0, 4 ) );
                                        var month = parseInt( tmp.substr( 4, 2 ) ) - 1;
                                        var day = parseInt( tmp.substr( 6, 2 ) );
                                        var d = new Date( year, month, day );
                                        dates.push( d );
                                }
                        });
			$.each( dates, function( key, val ){
				$( 'ul#list_history' ).append( '<li value="' + dateCheck[ key ] + '">' + val.toDateString() + '</li>' );
			});
		}
	};

	var setUserLanguages = function( langs ){
		if( langs ){
			var toappend = '<span>Select a language: </span><select id="language_selector">';
			$.each( langs, function( key, val ){
				toappend += '<option val="' + val.id_lang + '" name="' + val.id_lang + '" id="' + val.shortname_lang + '">' + val.longname_lang + ' (' + val.shortname_lang + ')</option>'
			});
			$( "div#languagepicker" ).html( toappend );
			$( "div#toolbar" ).show();
			$( "select#language_selector" ).off().on( 'change', languageListener );
		}
	};

	var languageListener = function( eventObject ){
		eventObject.preventDefault();
		var selectedLang = $( eventObject.currentTarget ).children( 'option:selected' ).attr( 'id' );
		$.get( "//abumatran.eu:59999/?selectedLang=" + selectedLang, function( response ){
                        if( response && response.length > 0 ){
				Builder.pushContent( response[ 0 ] );		
                        }
		});
	};

	var setStatistics = function( data, langs ){
		if( !data || !langs ){ return -1; }
		var stats = {};
		$.each( data, function( key, val ){
			if( typeof stats[ val.lang ] === 'undefined' ){
				stats[ val.lang ] = {};
			}
			if( typeof stats[ val.lang ][ val.label_pos ] === 'undefined' ){
				stats[ val.lang ][ val.label_pos ] = 0;
			}
			stats[ val.lang ][ val.label_pos ] += 1;
		});
		displayStatistics( stats, langs );
	};

	var displayStatistics = function( data, langs ){
		var toappend = '';
		$.each( langs, function( key, val ){
			if( typeof data[ val.id_lang ] !== 'undefined' ){
				toappend += '<li><span class="langname">' + val.longname_lang + '</span>';
				toappend += '<ol>';
				$.each( data[ val.id_lang ], function( pos, value ){
					toappend += '<li><span class="posname">' + pos + '</span>';
					toappend += '<span class="poscount">' + value + '</span><li>';
				}); 
				toappend += '</ol></li>';
			}
		});
		$( 'ul#list_stats' ).append( toappend );
	};

	var setLang = function( _lang ){
		lang = _lang;
	};

	var displayLang = function(){
		$( 'i#current_language' ).html( lang );
	};

	var setHistoryPage = function( date ){
		$.get( '//abumatran.eu:59999/?history=' + date, function( response ){
			if( response && response.length > 0 ){
				content = response[ 0 ];
				Builder.pushContent( content );
			}
		});	
	};

	var setSelectedLang = function( lang ){
		if( lang ){
			$( 'select#language_selector option[id="' + lang + '"]' ).prop( 'selected', true );
		}
	};

	return {
		setupTask: setupTask,
		setFlags: setFlags,
		setSpecificTypes: setSpecificTypes,
		displayFlags: displayFlags,
		displaySpecificTypes: displaySpecificTypes,
		setSurface: setSurface,
		setIdSurface: setIdSurface,
		displaySurface: displaySurface,
		setCandidates: setCandidates,
		displayCandidates: displayCandidates,
		checkCandidate: checkCandidate,
		checkSpecific: checkSpecific,
		checkFlag: checkFlag,
		checkNextTask: checkNextTask,
		checkPreviousTask: checkPreviousTask,
		setPrevious: setPrevious,
		setFlagged: setFlagged,
		setSpecific: setSpecific,
		setNext: setNext,
		displayHistory: displayHistory,
		setLang: setLang,
		displayLang: displayLang,
		setHistoryPage: setHistoryPage,
		setStatistics: setStatistics,
		setUserLanguages: setUserLanguages,
		setSelectedLang: setSelectedLang,
	};

})();

var History = ( function(){

	var content = -1;
	var flaggedContent = -1;

	var history = function( _content, flagged_content ){
		content = _content;
		flaggedContent = flagged_content;
		displayContent();
	};

	var displayContent = function(){
		var toappend = '<table><tr><th>Date</th><th>Language</th><th>Category</th><th>Surface Form</th><th>Lemma</th><th>Paradigm</th><th>Flag</th></tr> ';
		var candidates = [];
		var surfaces = [];
		$.each( content, function( key, val ){
			if( candidates.indexOf( val.id_candidate ) == -1 ){
				$.each( flaggedContent, function( key_flag, val_flag ){
					if( val_flag.id_surface == val.id_surface ){
						val.value_flag = val_flag.value_flag;
					}
				});
				surfaces.push( val.id_surface );
				candidates.push( val.id_candidate );
				toappend += buildRow( val );
			}
		});
		$.each( flaggedContent, function( key, val ){
			if( surfaces.indexOf( val.id_surface ) == -1 ){
	                        toappend += buildRowFlagged( val );
			}
                });
		toappend += '</table>';
		$( 'div#display_contenthistory' ).html( toappend );
	};

	var buildRow = function( val ){
		toappend = '';
		toappend += '<tr name="' + val.id_candidate + '">';
		toappend += '<td>' + buildDate( val.date_done ) + '</td>';
		toappend += '<td>' + val.longname_lang + '</td>';
		toappend += '<td>' + val.label_pos + '</td>';
		toappend += '<td>' + val.value_surface + '</td>';
		toappend += '<td>' + val.value_lemma + '</td>';
		toappend += '<td>' + val.value_paradigm + '</td>';
		if( !val.value_flag ){ value_flag = 'None'; }else{ value_flag = val.value_flag; }
		toappend += '<td>' + value_flag + ' </td>';
		toappend += '</tr>';
		return toappend;
	};

        var buildRowFlagged = function( val ){
                toappend = '';
                toappend += '<tr name="' + val.id_candidate + '">';
                toappend += '<td>' + buildDate( val.date_flag ) + '</td>';
                toappend += '<td>' + val.longname_lang + '</td>';
                toappend += '<td>-</td>';
                toappend += '<td>' + val.value_surface + '</td>';
                toappend += '<td>-</td>';
                toappend += '<td>-</td>';
		toappend += '<td>' + val.value_flag + ' </td>';
                toappend += '</tr>';
                return toappend;
        };

	var buildDate = function( data ){
		tmp = data;
		var year = parseInt( tmp.substr( 0, 4 ) );
		var month = parseInt( tmp.substr( 4, 2 ) );
		var day = parseInt( tmp.substr( 6, 2 ) );
		var hour = parseInt( tmp.substr( 8, 2 ) );
		var minute = parseInt( tmp.substr( 10, 2 ) );
		var second = parseInt( tmp.substr( 12, 2 ) );
		var d = new Date( year, month, day, hour, minute, second );
		return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
	};
	
	return {
		construct: history,
	};

})();

var Builder = ( function(){

	var position = 'login';
	var overview = -1;

	var pushContent = function( data ){
		pushHTML( data );
		pushData( data );
		Listeners.candidates();
		Listeners.specific();
		Listeners.flag();
		Listeners.navigationButtons();
		Listeners.history();
		Login.logout();
	};

	var pushHTML = function( data ){
		$.each( data, function( key, val ){
			if( key == 'position' ){
				if( val == 'overview' ){ overview = data; }
				position = val;
				$( 'body' ).attr( 'id', position );
			}else if( key == 'nav' ){
				$( 'nav' ).html( val );
			}else if( key == 'main' ){
				$( 'main' ).html( val );
			}
		});
	};

	var pushData = function( data ){
		if( data[ 'id_surface' ] == -1 ){
			Task.setPrevious( -1 );
			Task.setNext( -1 );
			Task.setSurface( 'Nothing to do...' );
			Task.displaySurface();
			return -1; 
		}
		$.each( data, function( key, val ){
			if( key == 'user' ){
				displayUserName( val );
			}else if( key == 'specific_types' ){
				Task.setSpecificTypes( val );		
				Task.displaySpecificTypes();
			}else if( key == 'flags' ){
				Task.setFlags( val );
				Task.displayFlags( position );
			}else if( key == 'value_surface' ){
				Task.setSurface( val );
				Task.displaySurface();
			}else if( key == 'candidates' ){
				console.log( data );
				Task.setCandidates( val );
				Task.displayCandidates();	
			}else if( key == 'last' ){
				Task.setPrevious( val );
			}else if( key == 'next' ){
				Task.setNext( val );
			}else if( key == 'lang' ){
				Task.setLang( val );
				Task.displayLang();
			}else if( key == 'langs' ){
				Task.setUserLanguages( data[ 'langs' ] );
			}else if( key == 'contentHistoryDate' || key == 'contentFlagDate' ){
				History.construct( data[ 'contentHistoryDate' ], data[ 'contentFlagDate' ] );
			}
		});
		Task.displayHistory( data[ 'history' ], data[ 'historyFlagged' ] );
		Task.setStatistics( data[ 'history' ], data[ 'langs' ] );
		Task.setFlagged( data[ 'flagged' ] );
		Task.setSpecific( data[ 'specific' ] );
		Task.setSelectedLang( data[ 'selectedLang' ] );
		return 1;
	};

	var displayUserName = function( userName ){
		 $( 'span#welcome_user' ).append( " " + userName );
	};

	var getOverview = function(){
		$.get( '//abumatran.eu:59999/?content=overview', function( response ){
			overview = response[ 0 ];
			pushContent( overview );
		});
	};

	var getTask = function(){
		Task.setupTask( position );
	};

	var navigate = function( el ){
		position = el.attr( 'id' );
		$( 'body' ).attr( 'id', position );
		if( position == 'overview' ){
			getOverview();
		}else{
			getTask();
		}
		//pushContent( position );
	};

	return {
		pushContent: pushContent,
		navigate: navigate,
	};

})();

var Login = ( function(){

	var login = function(){
		$( 'main' ).on( 'click', 'form#form_login button', submitLogin );
		$( 'main' ).on( 'submit', 'form#form_login', submitLogin );
	};

	var submitLogin = function( eventObject ){
		eventObject.stopPropagation();
		if( $( 'input#username' ).val() != '' && $( 'input#pwd' ).val() != '' ){
			eventObject.preventDefault();
			$.get( '//abumatran.eu:59999/?username=' 
					+ $( 'input#username' ).val() 
					+ '&pwd=' 
					+ $( 'input#pwd' ).val(), 
				function( response ){
					Builder.pushContent( response[ 0 ] );
				}
			);
		}
	};

	var logout = function(){
		$( 'a#logout' ).off().on( 'click', submitLogout );
	};

	var submitLogout = function( eventObject ){
		eventObject.preventDefault();
		$.get( '//abumatran.eu:59999/?logout=1', function( response ){
			$( 'div#toolbar' ).hide();
			Builder.pushContent( response[ 0 ] );
		} );
	};


	return {
		login: login,
		logout: logout,
	};

})();

var Listeners = ( function(){

	var currentFlag = -1;

	var listeners = function(){
		Login.login();
		Login.logout();
		menu();
		candidates();
		specific();
		navigationButtons();
		history();
	};

	var navigationButtons = function(){
		$( 'article#button_set' ).off().on( 'click', 'button', checkButton );
	};

	var checkButton = function( eventObject ){
		eventObject.preventDefault();
		actionType = $( eventObject.currentTarget ).attr( 'id' );
		if( actionType == 'button_previous' ){
			Task.checkPreviousTask();
		}else if( actionType == 'button_next' ){
			Task.checkNextTask();
		}
	};

	var menu = function(){
		$( 'nav' ).off().on( 'click', 'a.navmenu', function( eventObject ){
			eventObject.preventDefault();
			Builder.navigate( $( this ) );
		});
	};

	var specific = function(){
		$( "ul#list_specific_type" ).off().on( "click", "input[type='checkbox']", checkSpecific );
	};

	var checkSpecific = function( eventObject ){
		eventObject.stopPropagation();
		spe = $( eventObject.currentTarget ).val();
		checked = $( eventObject.currentTarget ).prop('checked');
		Task.checkSpecific( spe, checked );
	};

	var flag = function(){
		$( "select#categories" ).off().on( "change", checkFlag );
	};

	var checkFlag = function( eventObject ){
		fl = $( eventObject.currentTarget ).val();
		if( fl != currentFlag ){
			Task.checkFlag( fl );
		}
	};

	var candidates = function(){
		$( "div.wraplemma" ).off().on( "mousedown", checkCandidate );
		/*$( "div.wraplemma input[type='checkbox']" ).off().on( "mousedown", function( eventObject ){
			eventObject.stopPropagation();
		});*/

	};

	var checkCandidate = function( eventObject ){
		var wrapper = eventObject.currentTarget;
		var input = $( wrapper ).children( "input" );
		input.trigger( 'click' );
		Task.checkCandidate( input.attr( 'value' ) );
	};

	var history = function(){
		listHistoryItems()
		leaveHistoryButton()
	};
	
	var listHistoryItems = function(){
		$( 'ul#list_history li' ).off().on( 'click', pickHistory );
	};

	var leaveHistoryButton = function(){
		$( 'ul#menu_general a#back' ).off().on( 'click', function( eventObject ){
			eventObject.preventDefault();
			Builder.navigate( $( '<a id="overview">' ) );
		});
	}

	var pickHistory = function( eventObject ){
		Task.setHistoryPage( $( eventObject.currentTarget ).val() );
	};

	return {
		construct: listeners,
		candidates: candidates,
		specific: specific,
		flag: flag,
		navigationButtons: navigationButtons,
		history: history,
	};
})();

var Page = ( function(){
	

	var page = function(){
		initContent();
	};

	var goHome = function(){
		
	};

	var initContent = function(){
		$.get( '//abumatran.eu:59999/?content=init', function( response ){
			Builder.pushContent( response[ 0 ] );
			Listeners.construct();
		});
	};

	return {
		construct: page,
	};
})();

var customAjax = function(){
	$.xhrPool = [];
	$.xhrPool.abortAll = function(){
		$( this ).each( function( idx, jqXHR ){
			jqXHR.abort();
		});
		$.xhrPool.length = 0;
	};
	$.ajaxSetup({
		dataType: 'jsonp',
		xhrFields: {
			withCredentials: true,
		},
		crossDomain: true,
		beforeSend: function( jqXHR ){
			$.xhrPool.push( jqXHR );
			$( "#overlay" ).show();
			$( "div#loading" ).show();
		},
		complete: function( jqXHR ){
			var index = $.xhrPool.indexOf( jqXHR );
			if( index > -1 ){
				$.xhrPool.splice( index, 1 );
			}
			if( $.xhrPool.length == 0 ){
				$( "div#loading" ).off().hide();
				$( "#overlay" ).off().hide();
			}
		},
		success: function( jqXHR ){
			//console.log( jqXHR );
		},
	});
};

$( document ).ready( function(){
	customAjax();
	Page.construct();
});
