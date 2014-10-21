<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="<?php echo $ENCODING; ?>" />
		<title>Reportame</title>
		<base href="<?php echo $SCHEME.'://'.$HOST.':'.$PORT.$BASE.'/'; ?>" />
		<link rel="stylesheet" href="ui/css/base.css" type="text/css" />
		<link rel="stylesheet" href="ui/css/theme.css" type="text/css" />
		<link rel="stylesheet" href="lib/code.css" type="text/css" />
	</head>
	<body>
		<?php echo $this->render($content,$this->mime,get_defined_vars()); ?>
	</body>
	<footer>
		El footer
	</footer>
</html>
