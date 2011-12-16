<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="imagetoolbar" content="no" />
	<title>FancyBox</title>
	
<link rel="stylesheet" type="text/css" href="/proteoerp/assets/shared/css/jquery.fancybox.css" media="screen" />
<script type="text/javascript" src="/proteoerp/assets/shared/script/jquery.js"></script>
<script type="text/javascript" src="/proteoerp/assets/shared/script/jquery.easing.js"></script>
<script type="text/javascript" src="/proteoerp/assets/shared/script/jquery.fancybox.pack.js"></script>

	
	<script type="text/javascript">
		$(document).ready(function() {
			$("a").fancybox();
		});
	</script>
	<style>
		html, body {
			font: normal 11px Tahoma;
			color: #333;
		}
		
		a {
			outline: none;	
		}
		
		div#wrap {
			width: 500px;
			margin: 50px auto;	
		}

		img {
			border: 1px solid #CCC;
			padding: 2px;	
			margin: 10px 5px 10px 0;
		}
	</style>
</head>
<body>
<div id="wrap">
	<h1>FancyBox - sample page</h1>


	<p>
		Single image <br />
	
		<a title="Sample title" href="1_b.jpg"><img src="1_s.jpg" /></a>
	</p>
	
	<p>
		Image group <br />
	<a rel="group" title="Group title #1" href="http://192.168.0.99/proteoerp/assets/shared/images/3_b.jpg"><img src="http://192.168.0.99/proteoerp/assets/shared/images/3_b.jpg" /></a>

<a title="Sample title" href="http://192.168.0.99/proteoerp/assets/shared/images/3_b.jpg"><img src="http://192.168.0.99/proteoerp/assets/shared/images/3_s.jpg" /></a>
	</p>
</div>
</body>
</html>