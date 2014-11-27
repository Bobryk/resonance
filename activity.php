<?php
require_once 'soundcloudapi/Services/Soundcloud.php';
session_start();
//session_destroy();

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Resonance</title>
		<link rel="stylesheet" href="style.css" type="text/css">
		<link href='http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	</head>
	<body>
	
	<script>
		$( document ).ready(function(){
			var header = $("#header").outerHeight(true);
		 	var button = $("#button").outerHeight();
		 	var thewindow = $(window).outerHeight();
		 	var windowwid = $(window).outerWidth();
		 	var content = (thewindow - header);
		 	var article = $("article").outerWidth();
		 	$('article').css("left", (windowwid/2) - (article*0.5));
		 	$('#closeMap').css("left", (windowwid/2) + (article*0.5) + 30);
		 	$('#content').css("height", content);
		 	$('#content').css("padding-top",header);
		 	$('#button').css("margin-top", (content/2) - (button*0.5));
		 	$('#tint').css("height", thewindow);
		 	$('#tint').css("width",$(window).width());
		 	$('article').css("top",($(window).outerHeight()/2)-($('article').outerHeight()/2));
		 });

		function getLocation() {
		    if (navigator.geolocation) {
		        navigator.geolocation.getCurrentPosition(getPosition);
		    } else { 
		        document.getElementById("demo").innerHTML = "Geolocation is not supported by this browser.";
		    }
		}

		var code = "<?php echo $_GET['code']; ?>";

		function getPosition(position) {
			window.location.href = "activity.php?lat=" + position.coords.latitude + "&lon=" + position.coords.longitude + "&code=" + code; 
		}
		 window.onresize = function (){
		 	var header = $("#header").outerHeight(true);
		 	var button = $("#button").outerHeight();
		 	var thewindow = $(window).innerHeight();
		 	var windowwid = $(window).outerWidth();
		 	var content = (thewindow - header);
		 	var article = $("article").outerWidth();
		 	$('article').css("left", (windowwid/2) - (article*0.5));
		 	$('#closeMap').css("left", (windowwid/2) + (article*0.5) + 30);
		 	$('#content').css("height", content);
		 	$('#content').css("padding-top",header);
		 	$('#button').css("margin-top", (content/2) - (button*0.5));
		 	$('#tint').css("height", thewindow);
		 	$('#tint').css("width",$(window).width());
		 	$('article').css("top",($(window).outerHeight()/2)-($('article').outerHeight/2));
		 };
		</script>
	
	<?php
		if (!isset($_GET['lat']) && !isset($_GET['lon'])) {
	?>
		<div class="header" id="header" style="height:0;margin:0;width:0;padding:0;">
		<img src="images/resonanceLogo_noBack.png" >
		</div>
		<div id="content">
		<img id="button" onclick="getLocation()" src="images/button.png">

		<p id="demo"></p>

		
	<?php
		} else {?>
		

			<div class="header" id="header" style="opacity:0.9;">
			<img src="images/resonanceLogo_noBack.png">
			<img src="images/maplogo.png" id="maps" onclick="showMap()">
			<div id="tint"></div>
			</div>
			<img src="images/delete.png" id="closeMap" onclick="hideMap()">
			<div id="content">
			<p style="width:100%;text-align:center;font-size:2em;">Here are the songs that people are enjoying around you.</p>
	<?php
			$client = new Services_Soundcloud('7681d18716304f12fe0b0b7e45afbb06', '01318f693e41adf8eb12e97453e99df9', 'http://justinscheng.com/hack/activity.php');
			if(!isset($_SESSION['token'])){
				$code = $_GET['code'];
				$access_token = $client->accessToken($code);
				$_SESSION['token'] = $access_token['access_token'];
			} else {
				$client->setAccessToken($_SESSION['token']);
			}

			// exchange authorization code for access token
			//$code = $_GET['code'];

			//$access_token = $client->accessToken($code);

			// get user id
			$number = json_decode($client->get('me'), true);
			//print_r($number);
			$user = $number['id'];

			// get first favorite song
			$me = json_decode($client->get('me/favorites'), true);
			// print_r($me);
			$url = $me[0]['permalink_url'];
			// $url= str_replace(":", "%3A", $url);
			// $url = str_replace("/", "%2F", $url);
			// $url = str_replace("&", "%26", $url);
			// $url = str_replace("-", "%2D", $url);
			$poster = $me[0]['user']['username'];
			$title = $me[0]['title'];


			// include configuration file
			include('config.php');
				
			// connect to the database
			$db = mysqli_connect ($db_host, $db_user, $db_password, $db_name) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());

			?>
			<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
				<article style="visibility:hidden;position:fixed;">

		   		</article>
			<script>

		  function showMap(){
		  	$("article").css("visibility", "visible");
		  	$("#closeMap").css("visibility", "visible");
		  	$("#tint").css("visibility","visible");
		  }
		  function hideMap(){
		  	$("article").css("visibility", "hidden");
		  	$("#closeMap").css("visibility", "hidden");
		  	$("#tint").css("visibility","hidden");
		  }
		  function success(position) {
		    var mapcanvas = document.createElement('div');
		    mapcanvas.id = 'mapcontainer';
		    mapcanvas.style.height = '400px';
		    mapcanvas.style.width = '600px';

		    document.querySelector('article').appendChild(mapcanvas);

		    var coords = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		    
		    var options = {
		      zoom: 15,
		      center: coords,
		      mapTypeControl: false,
		      navigationControlOptions: {
		        style: google.maps.NavigationControlStyle.SMALL
		      },
		      mapTypeId: google.maps.MapTypeId.ROADMAP
		    };
		    var map = new google.maps.Map(document.getElementById("mapcontainer"), options);

		    var marker = new google.maps.Marker({
		        position: coords,
		        map: map,
		        title:"You are here!"
		    });
		    var infowindow = new google.maps.InfoWindow();

		    <?php

		    $sql9 = "SELECT latitude, longitude, song, poster, title FROM geofy WHERE id != {$user}";
			$result9 = mysqli_query($db, $sql9);
			while($row9 = mysqli_fetch_assoc($result9)){
				//distance calculations
				$dist = (3959 * acos(cos( $_GET['lat']*(3.14/180) ) * cos( $row9['latitude']*(3.14/180) ) * cos( $row9['longitude']*(3.14/180) - $_GET['lon']*(3.14/180) ) + sin( $_GET['lat']*(3.14/180) ) * sin( $row9['latitude']*(3.14/180) ) ));
				//if the distance form the coordinates is less than one mile, display their most recently liked track
				if ($dist  < 50){

				?>


			      marker = new google.maps.Marker({
			        position: new google.maps.LatLng(<?php echo $row9['latitude'] ?>, <?php echo $row9['longitude'] ?>),
			        map: map
			      });

				 
				<?php	
				}
			}
			?>

		  }
		  if (navigator.geolocation) {
		    navigator.geolocation.getCurrentPosition(success);
		  } else {
		    error('Geo Location is not supported');
		  }
		</script>
			<div id="map-canvas"></div>

			<?php

			//find out if the user has used the app before
			$sql2 = "SELECT id FROM geofy WHERE id = {$user}";
			$result2 = mysqli_query($db, $sql2);
			$row2 = mysqli_fetch_assoc($result2);

			//if not, create them an entry in the database
			if (!isset($row2)){
				$sql3 = "INSERT INTO geofy (
						id, 
						latitude, 
						longitude, 
						song,
						poster,
						title
					) VALUES (
						{$user},
						{$_GET['lat']},
						{$_GET['lon']},
						'{$url}',
						'{$poster}',
						'{$title}'
						)";
				$result3 = mysqli_query($db, $sql3);
			//if they have, simply update their file
			} else {
				$sql4 = "UPDATE geofy SET latitude = {$_GET['lat']}, longitude = {$_GET['lon']}, song = '{$url}', poster = '{$poster}', title = '{$title}' WHERE id = {$user}";
				$result4 = mysqli_query($db, $sql4);
			}

			//select all the coordinates in the database
			$sql = "SELECT latitude, longitude, song, poster, title FROM geofy WHERE id != {$user}";
			$result = mysqli_query($db, $sql);
			while($row = mysqli_fetch_assoc($result)){
				//distance calculations
				if($row['song']!=''){
					$distance = (3959 * acos(cos( $_GET['lat']*(3.14/180) ) * cos( $row['latitude']*(3.14/180) ) * cos( $row['longitude']*(3.14/180) - $_GET['lon']*(3.14/180) ) + sin( $_GET['lat']*(3.14/180) ) * sin( $row['latitude']*(3.14/180) ) ));
					//if the distance form the coordinates is less than one mile, display their most recently liked track
					if ($distance  < 50){
						// get a tracks oembed data
						$client->setCurlOptions(array(CURLOPT_FOLLOWLOCATION => 1));
							//echo "<div style='height:100px;overflow:hidden;'>";
							$track_url = $row['song'];
							$embed_info = json_decode($client->get('oembed', array('url' => $track_url)));
							echo $embed_info->html;
							
						// render the html for the player widget
							//print $embed_info->html;
							//echo "</div>";
						//echo "<a href='{$row['song']}'>{$row['poster']} {$row['title']}</a><br><br>";
					}
				}
			}
		}

	// get a tracks oembed data
		// $track_url = 'https://soundcloud.com/djolti-tar/dj-olti-feat-freakall-odikoto';
		// $embed_info = json_decode($client->get('oembed', array('url' => $track_url)));

	// render the html for the player widget
		//print $embed_info->html;

	?>
	</div>
	</body>
</html>