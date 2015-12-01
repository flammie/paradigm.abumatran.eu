<?php
session_start();
header( 'Content-type:text/html; charset=utf-8; Content-Encoding: gzip' );
if(!ob_start("ob_gzhandler")) ob_start();

?>

<!DOCTYPE html>
<html lang="en">
	<head>

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">


		<!--[if lt IE 9]>
	        	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- External libs: online version
                <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
                <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
                -->
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

                <!-- External libs: offline version -->
                <link rel="stylesheet" href="style/pure.all.css">
                <script type="text/javascript" src="script/jquery-2.1.1.js"></script>
		<script type="text/javascript" src="script/highcharts.js"></script>
                <!-- -->

                <!-- plugins -->
                <script type="text/javascript" src="script/jquery-number.js"></script>
                <!-- -->

		<link rel="stylesheet" href="style/paradigm.css" />
		<script type="text/javascript" src="script/paradigm.js"></script> 

		<title>Abu-MaTran Paradigm -- Administration</title>

	</head>

	<body>

		<header>
			<a href="http://www.abumatran.eu" title="Abu-MaTran" alt="Abu-MaTran">
				<img src="http://www.abumatran.eu/wp-content/uploads/2013/04/Abumatran-logo-escap.png" title="Abu-MaTran" alt="Abu-MaTran" />
			</a>
			<h1>Paradigm - admin</h1>
			<h2></h2>
		</header>

		<nav>

			<ul>
				<li><a class="navmenu" id="overview" href="overview" title="Overview">Overview</a></li>
				<li><a class="navmenu" id="tasks" href="tasks" title="Tasks">Tasks</a></li>
				<li><a class="navmenu" id="languages" href="languages" title="Languages">Languages</a></li>
				<li><a class="navmenu" id="users" href="users" title="Users">Users</a></li>
				<li><a class="navmenu" id="flags" href="flags" title="Flags">Flags</a></li>
				<li><a class="navmenu" id="export" href="export" title="Export">Export</a></li>
			</ul>
	
			<div id="loading_stuff">
                                <div id="loading_gif" class="spinner">
                                        <div class="double-bounce1"></div>
                                        <div class="double-bounce2"></div>
                                </div>
                                <span id="loading_msg">Loading</span>
                        </div>

		</nav>
	
		<section id="main">

		</section>

		<footer></footer>

	</body>

</html>

