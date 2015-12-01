var FormAddLanguageListener = ( function(){

	var pos = -1;
	var pos_flag = -1;

	var __init__ = function(){
	        $( "div#wrap_addfeatures" ).hide();
                $( "div#link_addfeatures" ).off().on( 'click', function(){
			$( "div#wrap_addfeatures" ).slideToggle( "fast" );
		});
                $( "form#form_add_language" ).off().on( 'submit', submitLanguage );
                $( "button#button_addtype" ).off().on( "click", addFeature );
                $( "button#button_addflag" ).off().on( "click", addFlag );
		FormMngLanguagesListener.__init__();
	};

	var setPosFlag = function( idPos ){
		pos_flag = idPos;
	};

	var setPos = function( idPos ){
		pos = idPos;
	};

	var showAdditionalFeatures = function( eventObject ){
		eventObject.preventDefault();
		$( "div#wrap_addfeatures" ).slideToggle( "fast" );
	};

	var submitLanguage = function( eventObject ){
		eventObject.preventDefault();
		var form_values = $( "form#form_add_language" ).serialize();
                var tmpTypes = FormMngLanguagesListener.getSpecificTypes();
		var tmpFlags = FormMngLanguagesListener.getFlags();
		form_values += "&specific_types=" + JSON.stringify( tmpTypes );
		form_values += "&flags=" + JSON.stringify( tmpFlags );
                form_values += '&update=' + FormMngLanguagesListener.getUpdate();
		$.post( $( location ).attr( 'pathname' ) + "/class/language.php", form_values, function( response ){
			$( "div#add_language_msg" ).html( response );
			$( "div#add_language_msg" ).fadeIn( 160, "linear" );
			$( "div#add_language_msg" ).fadeOut( 2600, "linear" );
			FormMngLanguagesListener.setUpdate( -1 );
			FormAddLanguageListener.__init__();
		});
	};

	var formatSpecificTypes = function( types ){
		var format = {};
		$.each( types, function( key, val ){
			format[ key ] = val;
		});
		return format
	};

	var formatFlags = function( flags ){
		var format = "";
		$.each( flags, function( key, val ){
                        format += key + ":" + JSON.stringify( val ) + ",";
                });
                return "[" + format + "]"
	};

	var addFeature = function( eventObject ){
		eventObject.preventDefault();
		if( pos == -1 ){
			$( "div#add_language_msg" ).html( "Please select a grammatical category (POS)" );
			$( "div#add_language_msg" ).fadeIn( 160, "linear" );
                        $( "div#add_language_msg" ).fadeOut( 2600, "linear" );
		}else{
			FormMngLanguagesListener.addSpecificType( pos, $( "input#addtype" ).val() );
		}
	};

	var addFlag = function( eventObject ){
		eventObject.preventDefault();
                if( pos_flag == -1 ){
                        $( "div#add_language_msg" ).html( "Please select a grammatical category (POS)" );
                        $( "div#add_language_msg" ).fadeIn( 160, "linear" );
                        $( "div#add_language_msg" ).fadeOut( 2600, "linear" );
                }else{
                        FormMngLanguagesListener.addFlag( pos_flag, $( "input#addflag" ).val() );
                }
	};

	return {
		__init__: __init__,
		setPos: setPos,
		setPosFlag: setPosFlag,
	};

})();

var FormMngLanguagesListener = ( function(){

	var languages = {};
	var update = -1;
	var specific_types = {};
	var flags = {};

	var __init__ = function(){
		reinitForm();
		getLanguages();
	};

	var getSpecificTypes = function(){
		return specific_types;
	};

	var getFlags = function(){
		return flags;
	};

	var setUpdate = function( updateValue ){
		update = updateValue;
	};

	var getUpdate = function(){
		return update;
	};

	var addSpecificType = function( idPos, feature ){
		if( specific_types[ idPos ] == undefined || specific_types[ idPos ] == -1 ){
                	specific_types[ idPos ] = [];
		}
                specific_types[ idPos ].push( [ -1, feature ] );
		fillSpecific( idPos );
	};

	var setPosTypes = function(){
		$( "ul#list_specific_pos" ).off().on( 'click', 'li', function(){
			$( "ul#list_specific_pos li.select_specific_type" ).removeClass( "select_specific_type" );
			$( this ).addClass( "select_specific_type" );
			var tmpId = $( this ).attr( 'id' );
			if( tmpId == 'specific_type_noun' ){ tmpId = "1"; }
			else if( tmpId == 'specific_type_adjective' ){ tmpId = "2"; }
			else if( tmpId == 'specific_type_verb' ){ tmpId = "3"; }
			FormAddLanguageListener.setPos( tmpId );
			fillSpecific( tmpId );
		});
	};

	var addFlag = function( idPos, feature ){
                if( flags[ idPos ] == undefined || flags[ idPos ] == -1 ){
                        flags[ idPos ] = [];
                }
                flags[ idPos ].push( [ -1, feature ] );
                fillFlag( idPos );
        };

        var setPosFlags = function(){
                $( "ul#list_flag_pos" ).off().on( 'click', 'li', function(){
                        $( "ul#list_flag_pos li.select_flag" ).removeClass( "select_flag" );
                        $( this ).addClass( "select_flag" );
                        var tmpId = $( this ).attr( 'id' );
                        if( tmpId == 'flag_noun' ){ tmpId = "1"; }
                        else if( tmpId == 'flag_adjective' ){ tmpId = "2"; }
                        else if( tmpId == 'flag_verb' ){ tmpId = "3"; }
                        FormAddLanguageListener.setPosFlag( tmpId );
                        fillFlag( tmpId );
                });
        };


	var getLanguages = function(){
		$.get( $( location ).attr( 'pathname' ) + "/class/language.php", { "list": "all" }, function( data ){
			var listLanguages = $.parseJSON( data );
			var toappend = "<tr><th>#</th><th>ISO</th><th>Name</th><th>edit</th><th>del.</th></tr>";
			$.each( listLanguages, function( key, val ){
				languages[ val.id ] = [ val.shortname, val.longname ];
				toappend += "<tr name= '" + val.id + "' class='table_languages_content'><td>" + val.id + "</td>",
				toappend += "<td>" + val.shortname + "</td>";
				toappend += "<td>" + val.longname + "</td>";
				toappend += "<td title='Edit Language' class='table_languages_edit'><i class='fa fa-pencil-square-o'></i></td>";
				toappend += "<td title='Delete Language' class='table_languages_delete'><i class='fa fa-trash-o'></i></td></tr>";
			});
			$( "div#listlanguages table" ).html( toappend );
			setOptions();	   
		});
	};

	var setOptions = function(){
		setEdit();
		setDelete();
		setPosTypes();
		setPosFlags();
	};

	var setEdit = function(){
		$( 'td.table_languages_edit' ).off().on( 'click', editLanguage );       
	};

	var setDelete = function(){
		$( 'td.table_languages_delete' ).off().on( 'click', deleteLanguage );
	};

	var reinitForm = function(){
		specific_types = {};
		$( "div#specific_type" ).html( '' );
		$( "div#flag" ).html( '' );
		$( 'input#shortname' ).val( '' );
                $( 'input#longname' ).val( '' );
                $( 'button[type="submit"]' ).text( 'Validate Language' );	
	};

	var editLanguage = function( eventObject ){
		eventObject.preventDefault();
		var tmpEl = $( eventObject.currentTarget );
		var tmpId = tmpEl.parent( 'tr' ).attr( 'name' );
		update = tmpId;
		if( update != -1 ){
			$( 'input#shortname' ).val( languages[ tmpId ][ 0 ] );
			$( 'input#longname' ).val( languages[ tmpId ][ 1 ] );
			$( 'button[type="submit"]' ).text( 'Update Language' );
			setSpecificTypes( tmpId );
			setFlags( tmpId );
		}
	};

	var setSpecificTypes = function( idLang ){
		specific_types = {};
		$.get( $( location ).attr( 'pathname' ) + "/class/language.php", { "specific_types": idLang }, function( data ){
			var listTypes = $.parseJSON( data );
			$.each( listTypes, function( key, val ){
				if( specific_types[ val.id_pos ] == undefined ){
					specific_types[ val.id_pos ] = [];
				}
				specific_types[ val.id_pos ].push( [ val.id, val.value ] );
			});
			var tmpId = "1";
			FormAddLanguageListener.setPos( tmpId );
			fillSpecific( tmpId );
		});
	};

        var setFlags = function( idLang ){
                flags = {};
                $.get( $( location ).attr( 'pathname' ) + "/class/language.php", { "flags": idLang }, function( data ){
                        var listFlags = $.parseJSON( data );
                        $.each( listFlags, function( key, val ){
                                if( flags[ val.id_pos ] == undefined ){
                                        flags[ val.id_pos ] = [];
                                }
                                flags[ val.id_pos ].push( [ val.id, val.value ] );
                        });
                        var tmpId = "1";
                        FormAddLanguageListener.setPosFlag( tmpId );
                        fillFlag( tmpId );
                });
        };

	var fillSpecific = function( idPos ){
		$( "ul#list_specific_pos li.select_specific_type" ).removeClass( "select_specific_type" );
		if( idPos == "1" ){
			$( "ul#list_specific_pos li#specific_type_noun" ).addClass( "select_specific_type" );
		}else if( idPos == "2" ){
			$( "ul#list_specific_pos li#specific_type_adjective" ).addClass( "select_specific_type" );
		}else if( idPos == "3" ){
			$( "ul#list_specific_pos li#specific_type_verb" ).addClass( "select_specific_type" );
		}
		if( typeof specific_types[ idPos ] === 'undefined' ){
			specific_types[ idPos ] = [];
		}
		specificFilling( idPos );
	};

        var fillFlag = function( idPos ){
                $( "ul#list_flag_pos li.select_flag" ).removeClass( "select_flag" );
                if( idPos == "1" ){
                        $( "ul#list_flag_pos li#flag_noun" ).addClass( "select_flag" );
                }else if( idPos == "2" ){
                        $( "ul#list_flag_pos li#flag_adjective" ).addClass( "select_flag" );
                }else if( idPos == "3" ){
                        $( "ul#list_flag_pos li#flag_verb" ).addClass( "select_flag" );
                }
                if( typeof flags[ idPos ] === 'undefined' ){
                        flags[ idPos ] = [];
                }
                flagFilling( idPos );
        };

	var specificFilling = function( idPos ){
		var toadd = "";
		$( "div#specific_type" ).html( toadd );
		if( specific_types[ idPos ] != -1 ){
			$.each( specific_types[ idPos ], function( key, val ){
				toadd += "<div><span name='" + val[ 0 ] + "' class='feature'>" + val[ 1 ] + "</span><span id='" + val[ 1 ] + "' name='" + val[ 0 ] + "' class='delete_feature'><i class='fa fa-trash-o' alt='" + val[ 0 ] + "'></i></span></div>";
			});
			$( "div#specific_type" ).html( toadd );
			$( "div#specific_type div" ).off().on( 'click', 'i', function( eventObject ){
				eventObject.preventDefault();
				deleteFeature( idPos, eventObject );
			});
		}
	};

        var flagFilling = function( idPos ){
                var toadd = "";
                $( "div#flag" ).html( toadd );
                if( flags[ idPos ] != -1 ){
                        $.each( flags[ idPos ], function( key, val ){
                                toadd += "<div><span name='" + val[ 0 ] + "' class='feature'>" + val[ 1 ] + "</span><span id='" + val[ 1 ] + "' name='" + val[ 0 ] + "' class='delete_feature'><i class='fa fa-trash-o' alt='" + val[ 0 ] + "'></i></span></div>";
                        });
                        $( "div#flag" ).html( toadd );
                        $( "div#flag div" ).off().on( 'click', 'i', function( eventObject ){
                                eventObject.preventDefault();
                                deleteFlag( idPos, eventObject );
                        });
                }
        };

	var deleteFeature = function( idPos, eventObject ){
		var check = confirm( 'Are you sure you want to delete this specific feature?' );
		var featureId = $( eventObject.currentTarget.parentNode ).attr( 'name' );
                if( check ){
			var featureVal = $( eventObject.currentTarget.parentNode ).attr( 'id' );
			var tmpIndex = specific_types[ idPos ].indexOf( [ -1, featureVal ] );
			specific_types[ idPos ].splice( tmpIndex, 1 );
			if( featureId != -1 ){
				$.get( "class/language.php", { "delete_feature": featureId }, function( response ){
	       	                });
			}
		}
		specificFilling( idPos );
	};

        var deleteFlag = function( idPos, eventObject ){
                var check = confirm( 'Are you sure you want to delete this flag?' );
                var featureId = $( eventObject.currentTarget.parentNode ).attr( 'name' );
                if( check ){
                        var featureVal = $( eventObject.currentTarget.parentNode ).attr( 'id' );
                        var tmpIndex = flags[ idPos ].indexOf( [ -1, featureVal ] );
                        flags[ idPos ].splice( tmpIndex, 1 );
                        if( featureId != -1 ){
                                $.get( "class/language.php", { "delete_flag": featureId }, function( response ){
                                });
                        }
                }
                flagFilling( idPos );
        };

	var deleteLanguage = function( eventObject ){
		eventObject.preventDefault();
		var tmpEl = $( eventObject.currentTarget );
		var tmpId = tmpEl.parent( 'tr' ).attr( 'name' );
		if( tmpId != -1 ){
			var check = confirm( 'Are you sure you want to delete this language and all its specific features?' );
			if( check ){
				$.get( "class/language.php", { "delete": tmpId }, function( response ){
					FormMngLanguagesListener.__init__();
				});
			}
		}
	};

	return {
		__init__: __init__,
		addFlag: addFlag,
		getFlags: getFlags, 
		addSpecificType: addSpecificType,
		getSpecificTypes: getSpecificTypes, 
		getUpdate: getUpdate,
		setUpdate: setUpdate,
	};

})();


$( document ).ready( function(){
	$.ajaxSetup ({
		cache: false,
	});

});



