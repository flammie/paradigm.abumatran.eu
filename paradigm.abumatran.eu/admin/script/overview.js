
var TasksDetails = ( function(){

	var __init__ = function(){
		getTasksDetails();
	};

	var getTasksDetails = function(){
		getSurfaceForms();
		getValidatedCounts();
	};

	getValidatedCounts = function(){
		$.post( "class/task.php", { "overview": "validated_counts" }, function( response ){
			if( response != '' ){
				var response = $.parseJSON( response );
				var total = 0;
				for( var key in response ){
					total = total + parseInt( response[ key ].cc );
				}
				var toplot = [];
				for( var key in response ){
					toplot.push( [ response[ key ].label_pos, Math.floor( response[ key ].cc * 100 / total ) ] )
				}
                	        buildChart( [{
                        	        type: 'pie',
                                	name: 'annotations',
	                                data: toplot,
                        	}] );
			}
		});
	};

	getSurfaceForms = function(){
		$.post( "class/task.php", { "overview": "all" }, function( response ){
			response = $.parseJSON( response );
			$.each( response, function( key, val ){
				$( "article#overview_tasks fieldset ul" ).append( "<li><span>" + key + "</span><span>" + $.number( val ) + "</span></li>" );
			});
		});
	};

	var buildChart = function( data ){
		$( "article#details_overview_tasks form fieldset div" ).highcharts( {
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

	return {
		__init__: __init__,		
	};

})();

var UsersDetails = ( function(){

	var __init__ = function(){
		getUsersDetails();		
	};

	var getUsersDetails = function(){
		getAccounts();
//		getOnlineUsers();
	};

/*	getOnlineUsers = function(){
		$.post( "class/user.php", { "overview": "users" }, function( response ){
                        response = $.parseJSON( response );
                        $.each( response, function( key, val ){
                                $( "article#online_users fieldset ul" ).append( "<li><span>" + val.name_user + "</span></li>" );
		        });
		});
	};
	*/
	getAccounts = function(){
                $.post( "class/user.php", { "overview": "all" }, function( response ){
                        response = $.parseJSON( response );
                        $.each( response, function( key, val ){
				toappend = '<li><span class="label">' + key + '</span><span name="value_' + key + '">' + val + '</span></li>';
				$( "article#overview_users fieldset ul" ).append( toappend );
//                                $( "article#overview_users fieldset ul" ).append( "<li><span class='label'>" + key + "</span><span>" + $.number( val ) + "</span></li>" );
                        });
                });
        };

	return {
		__init__: __init__,
	};


})();
