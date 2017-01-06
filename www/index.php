<?php
	error_reporting(E_ALL);
	require_once '../library/PostcodeNl/Api/RestClient.php';

	header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Postcode.nl API REST client example</title>
		<style>
			fieldset {
				margin: 8px 0;
				padding: 16px;
			}
		</style>
	</head>
	<body>
	<h1>Postcode.nl API REST client example</h1>
	<ul>
		<li><a href="https://api.postcode.nl/documentation">Postcode.nl API documentation</a></li>
	</ul>
	<form method="POST">
		<fieldset>
			<legend>Authentication</legend>
			<label>
				API url:<br />
				<input type="text" name="apiUrl" value="<?php echo isset($_POST['apiUrl']) ? htmlspecialchars($_POST['apiUrl']) : PostcodeNl_Api_RestClient::DEFAULT_URL; ?>" size="60">
			</label><br />
			<label>
				API key:<br />
				<input type="text" name="key" value="<?php echo isset($_POST['key']) ? htmlspecialchars($_POST['key']) : ''; ?>" size="60">
			</label><br />
			<label>
				API secret:<br />
				<input type="text" name="secret" value="<?php echo isset($_POST['secret']) ? htmlspecialchars($_POST['secret']) : ''; ?>" size="60">
			</label><br />
		</fieldset>
		<br />

		<fieldset>
			<legend>Input parameters</legend>
			<label>
				Postcode:<br />
				<input type="text" name="postcode" placeholder="2012ES" value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>" size="8"><br />
			</label>
			<label>
				House number:<br />
				<input type="text" name="houseNumber" placeholder="30" value="<?php echo isset($_POST['houseNumber']) ? htmlspecialchars($_POST['houseNumber']) : ''; ?>" size="8"><br />
			</label>
			<label>
				House number addition:<br />
				<input type="text" name="houseNumberAddition" value="<?php echo isset($_POST['houseNumberAddition']) ? htmlspecialchars($_POST['houseNumberAddition']) : ''; ?>" size="8">
			</label>
			<label><input type="checkbox" name="validateHouseNumberAddition" value="1" <?php echo !empty($_POST['validateHouseNumberAddition']) ? 'checked="checked"' : ''; ?>> Strictly validate addition</label><br />
		</fieldset>
		<br />
		<input type="submit" value="Send">

	</form>
<?php
	if (isset($_POST['key']))
	{
		$start = microtime(true);
		try
		{
			$client = new PostcodeNl_Api_RestClient($_POST['key'], $_POST['secret'], !empty($_POST['apiUrl']) ? $_POST['apiUrl'] : null);

			$client->setDebugEnabled();

			if (isset($_POST['postcode']))
			{
				$result = $client->lookupAddress($_POST['postcode'], $_POST['houseNumber'], $_POST['houseNumberAddition'], !empty($_POST['validateHouseNumberAddition']));
				$addressResult = $result;

				echo '<hr>';
				echo '<h2>Validated address</h2>';
				echo '<pre>';
				echo htmlspecialchars($addressResult['street']) .' '. htmlspecialchars($addressResult['houseNumber']) .' '. htmlspecialchars(isset($addressResult['houseNumberAddition']) ? $addressResult['houseNumberAddition'] : $_POST['houseNumberAddition']) . "\n";
				echo htmlspecialchars($addressResult['postcode']) .' '. htmlspecialchars($addressResult['city']) . "\n";
				echo '</pre>';
			}

			if (isset($result))
			{
				echo '<h3>Response data:</h3>';
				echo '<pre>'. var_export($result, true) .'</pre>';
				echo '</pre>';
			}
		}
		catch (Exception $e)
		{
			echo '<hr>';

			$type = 'Error';
			if ($e instanceof PostcodeNl_Api_RestClient_ClientException)
				$type = 'Client error';
			else if ($e instanceof PostcodeNl_Api_RestClient_ServiceException)
				$type = 'Service error';
			else if ($e instanceof PostcodeNl_Api_RestClient_AddressNotFoundException)
				$type = 'Address not found';
			else if ($e instanceof PostcodeNl_Api_RestClient_InputInvalidException)
				$type = 'Input error';
			else if ($e instanceof PostcodeNl_Api_RestClient_AuthenticationException)
				$type = 'Authentication error';

			echo '<h2>'. $type .'</h2>';
			echo htmlspecialchars($e->getMessage()) .'<br />';
			echo '(class: <em>'. get_class($e) .'</em>)<br />';

			if (isset($client))
			{
				echo '<h3>Response data:</h3>';
				echo '<pre>'. var_export($client->getLastResponseData(), true) .'</pre>';
				echo '</pre>';
			}
		}
		echo '<h4>Time taken:</h4>';
		echo '<p>'. round(microtime(true) - $start, 3) .' sec</p>';

		if ($client)
		{
			$debugData = $client->getDebugData();

			echo '<h2>Raw HTTP request and response</h2>';
			echo '<fieldset><legend>Raw HTTP request headers + body</legend><pre>';
			echo htmlspecialchars($debugData['request'] ? $debugData['request'] : 'Not sent.');
			echo '</pre></fieldset>';
			echo '<fieldset><legend>Raw HTTP response headers + body</legend><pre>';
			echo htmlspecialchars($debugData['response'] ? $debugData['response'] : 'Not received.');
			echo '</pre></fieldset>';
		}
	}
?>
	<br />
	<hr>
	<br />
	<h2>Example address data:</h2>
	<h3>Existing postcode, with no housenumber addition</h3>
	<p>
		Postcode: `2012ES`<br />
		Housenumber: `30`<br />
		Housenumber addition: ``<br />
	</p>
	<h3>Existing postcode, with only one possible housenumber addition</h3>
	<p>
		Postcode: `1011AE`<br />
		Housenumber: `36`<br />
		Housenumber addition: `B`<br />
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
