<?php
	error_reporting(E_ALL);
	require_once '../library/PostcodeNl/Api/RestClient.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Postcode.nl API REST client example</title>
	</head>
	<body>
	<h1>Postcode.nl API REST client example</h1>
	<ul>
		<li><a href="https://api.postcode.nl/documentation">Postcode.nl API documentation</a></li>
	</ul>
	<form method="POST">
		API key:<br />
		<input type="text" name="key" value="<?php echo isset($_POST['key']) ? htmlspecialchars($_POST['key']) : ''; ?>" size="60"><br />
		API secret:<br />
		<input type="text" name="secret" value="<?php echo isset($_POST['secret']) ? htmlspecialchars($_POST['secret']) : ''; ?>" size="60"><br />
		<br />
		Postcode:<br />
		<input type="text" name="postcode" value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>" size="8"><br />
		House number:<br />
		<input type="text" name="houseNumber" value="<?php echo isset($_POST['houseNumber']) ? htmlspecialchars($_POST['houseNumber']) : ''; ?>" size="8"><br />
		House number addition:<br />
		<input type="text" name="houseNumberAddition" value="<?php echo isset($_POST['houseNumberAddition']) ? htmlspecialchars($_POST['houseNumberAddition']) : ''; ?>" size="8">
			<label><input type="checkbox" name="validateHouseNumberAddition" value="1" <?php echo !empty($_POST['validateHouseNumberAddition']) ? 'checked="checked"' : ''; ?>> Strictly validate addition</label><br />
		<br />

		<input type="submit" value="Send"> <label><input type="checkbox" name="showRawRequestResponse" value="1" <?php echo !empty($_POST['showRawRequestResponse']) ? 'checked="checked"' : ''; ?>> Show raw HTTP request and response</label><br />
	</form>
<?php
	if (isset($_POST['key']))
	{
		try
		{
			$client = new PostcodeNl_Api_RestClient($_POST['key'], $_POST['secret']);

			if (!empty($_POST['showRawRequestResponse']))
				$client->setDebugEnabled();

			$result = $client->lookupAddress($_POST['postcode'], $_POST['houseNumber'], $_POST['houseNumberAddition'], !empty($_POST['validateHouseNumberAddition']));

			echo '<hr>';
			echo '<h2>Validated address</h2>';
			echo '<pre>';
			echo htmlspecialchars($result['street']) .' '. htmlspecialchars($result['houseNumber']) .' '. htmlspecialchars(isset($result['houseNumberAddition']) ? $result['houseNumberAddition'] : $_POST['houseNumberAddition']) . "\n";
			echo htmlspecialchars($result['postcode']) .' '. htmlspecialchars($result['city']) . "\n";
			echo '</pre>';
			echo '<h3>Response data:</h3>';
			echo '<pre>'. var_export($result, true) .'</pre>';
			echo '</pre>';
		}
		catch (Exception $e)
		{
			echo '<hr>';

			$type = 'Error';
			if ($e instanceof PostcodeNl_Api_RestClient_ClientException)
				$type = 'Client error';
			else if ($e instanceof PostcodeNl_Api_RestClient_ServiceException)
				$type = 'Service error';
			else if ($e instanceof PostcodeNl_Api_RestClient_InputInvalidException)
				$type = 'Input error';
			else if ($e instanceof PostcodeNl_Api_RestClient_AuthenticationException)
				$type = 'Authentication error';
			else if ($e instanceof PostcodeNl_Api_RestClient_AddressNotFoundException)
				$type = 'Address not found';

			echo '<h2>'. $type .'</h2>';
			echo htmlspecialchars($e->getMessage()) .'<br />';
			echo '(class: <em>'. get_class($e) .'</em>)<br />';
		}

		if (!empty($_POST['showRawRequestResponse']) && isset($client))
		{
			$debugData = $client->getDebugData();

			echo '<h2>Raw HTTP request and response</h2>';
			echo '<fieldset><legend>Raw HTTP request headers</legend><pre>';
			echo htmlspecialchars($debugData['request'] ? $debugData['request'] : 'Not sent.');
			echo '</pre></fieldset>';
			echo '<fieldset><legend>Raw HTTP response headers + body</legend><pre>';
			echo htmlspecialchars($debugData['response'] ? $debugData['response'] : 'Not received.');
			echo '</pre></fieldset>';
		}
	}
?>
	<hr>
	<h2>Example data:</h2>
	<h3>Existing postcode, with no housenumber addition</h3>
	<p>
		Postcode: `2012ES`<br />
		Housenumber: `30`<br />
		Housenumber addition: ``<br />
	</p>
	<h3>Existing postcode, with only one possible housenumber addition</h3>
	<p>
		Postcode: `2011DW`<br />
		Housenumber: `8`<br />
		Housenumber addition: `RD`<br />
	</p>
	<h3>Existing postcode, with multiple housenumber additions, incorrect addition</h3>
	<p>
		Postcode: `2011DW`<br />
		Housenumber: `9`<br />
		Housenumber addition: `ZZZ`<br />
	</p>
	<h3>Non-existing postcode</h3>
	<p>
		Postcode: `1234ZZ`<br />
		Housenumber: `1234`<br />
		Housenumber addition: ``<br />
	</p>
	</body>
</html>
