<html>
<head>
	<title>SMS Send Page</title>
	<script src=./jquery-1.11.3.min.js></script>
	<link rel=stylesheet type=text/css href=./style.css>
</head>
<body>
<div id=select>
</div>
<div id=check>
</div>
<div id=result>
</div>

</body>
</html>

<script type=text/javascript>
	$(document).ready(function(){
			$('#select').load('./select.php');
			$('#check').load('./check.php');
			$('#result').load('./send.php');
	});
</script>