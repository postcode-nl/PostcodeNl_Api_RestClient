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
			API url:<br />
			<input type="text" name="apiUrl" value="<?php echo isset($_POST['apiUrl']) ? htmlspecialchars($_POST['apiUrl']) : PostcodeNl_Api_RestClient::DEFAULT_URL; ?>" size="60"><br />
			API key:<br />
			<input type="text" name="key" value="<?php echo isset($_POST['key']) ? htmlspecialchars($_POST['key']) : ''; ?>" size="60"><br />
			API secret:<br />
			<input type="text" name="secret" value="<?php echo isset($_POST['secret']) ? htmlspecialchars($_POST['secret']) : ''; ?>" size="60"><br />
		</fieldset>
		<br />
		<b>Select service:</b><br />
		<select name="service">
			<option value="Address" <?php echo isset($_POST['service']) && $_POST['service'] == 'Address' ? 'selected' : '' ?>>Postcode.nl Address API: Address lookup</option>
			<option value="Signal" <?php echo isset($_POST['service']) && $_POST['service'] == 'Signal' ? 'selected' : '' ?>>Postcode.nl Signal API: Check</option>
		</select> <input type="submit" value="Select"><br />
		<br />
		<?php if (isset($_POST['service']) && $_POST['service'] == "Address") {?>
			<fieldset>
				<legend>Input parameters</legend>
				Postcode:<br />
				<input type="text" name="postcode" value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>" size="8"><br />
				House number:<br />
				<input type="text" name="houseNumber" value="<?php echo isset($_POST['houseNumber']) ? htmlspecialchars($_POST['houseNumber']) : ''; ?>" size="8"><br />
				House number addition:<br />
				<input type="text" name="houseNumberAddition" value="<?php echo isset($_POST['houseNumberAddition']) ? htmlspecialchars($_POST['houseNumberAddition']) : ''; ?>" size="8">
					<label><input type="checkbox" name="validateHouseNumberAddition" value="1" <?php echo !empty($_POST['validateHouseNumberAddition']) ? 'checked="checked"' : ''; ?>> Strictly validate addition</label><br />
			</fieldset>
			<br />
			<input type="submit" value="Send"> <label><input type="checkbox" name="showRawRequestResponse" value="1" <?php echo !empty($_POST['showRawRequestResponse']) ? 'checked="checked"' : ''; ?>> Show raw HTTP request and response</label><br />
		<?php } else if (isset($_POST['service']) && $_REQUEST['service'] == "Signal") {?>
			<fieldset>
				<legend>Input parameters</legend>

				<fieldset>
					<legend>Customer</legend>
					First name:<br />
					<input type="text" name="customer[firstName]" value="<?php echo isset($_POST['customer']['firstName']) ? htmlspecialchars($_POST['customer']['firstName']) : ''; ?>" size="32"><br />
					Last name:<br />
					<input type="text" name="customer[lastName]" value="<?php echo isset($_POST['customer']['lastName']) ? htmlspecialchars($_POST['customer']['lastName']) : ''; ?>" size="32"><br />
					Birth date:<br />
					<input type="text" name="customer[birthDate]" value="<?php echo isset($_POST['customer']['birthDate']) ? htmlspecialchars($_POST['customer']['birthDate']) : ''; ?>" size="32"><br />
					Email:<br />
					<input type="text" name="customer[email]" value="<?php echo isset($_POST['customer']['email']) ? htmlspecialchars($_POST['customer']['email']) : ''; ?>" size="32"><br />
					Email domain:<br />
					<input type="text" name="customer[emailDomain]" value="<?php echo isset($_POST['customer']['emailDomain']) ? htmlspecialchars($_POST['customer']['emailDomain']) : ''; ?>" size="32"><br />
					Phone number:<br />
					<input type="text" name="customer[phoneNumber]" value="<?php echo isset($_POST['customer']['phoneNumber']) ? htmlspecialchars($_POST['customer']['phoneNumber']) : ''; ?>" size="32"><br />
					Bank number:<br />
					<input type="text" name="customer[bankNumber]" value="<?php echo isset($_POST['customer']['bankNumber']) ? htmlspecialchars($_POST['customer']['bankNumber']) : ''; ?>" size="32"><br />
					Site:<br />
					<input type="text" name="customer[site]" value="<?php echo isset($_POST['customer']['site']) ? htmlspecialchars($_POST['customer']['site']) : ''; ?>" size="32"><br />
					Internal ID:<br />
					<input type="text" name="customer[internalId]" value="<?php echo isset($_POST['customer']['internalId']) ? htmlspecialchars($_POST['customer']['internalId']) : ''; ?>" size="32"><br />
					<fieldset>
						<legend>Address</legend>
						Postcode:<br />
						<input type="text" name="customer[address][postcode]" value="<?php echo isset($_POST['customer']['address']['postcode']) ? htmlspecialchars($_POST['customer']['address']['postcode']) : ''; ?>" size="8"><br />
						House number:<br />
						<input type="text" name="customer[address][houseNumber]" value="<?php echo isset($_POST['customer']['address']['houseNumber']) ? htmlspecialchars($_POST['customer']['address']['houseNumber']) : ''; ?>" size="8"><br />
						House number addition:<br />
						<input type="text" name="customer[address][houseNumberAddition]" value="<?php echo isset($_POST['customer']['address']['houseNumberAddition']) ? htmlspecialchars($_POST['customer']['address']['houseNumberAddition']) : ''; ?>" size="8"><br />
						Street:<br />
						<input type="text" name="customer[address][street]" value="<?php echo isset($_POST['customer']['address']['street']) ? htmlspecialchars($_POST['customer']['address']['street']) : ''; ?>" size="32"><br />
						City:<br />
						<input type="text" name="customer[address][city]" value="<?php echo isset($_POST['customer']['address']['city']) ? htmlspecialchars($_POST['customer']['address']['city']) : ''; ?>" size="32"><br />
						Region:<br />
						<input type="text" name="customer[address][region]" value="<?php echo isset($_POST['customer']['address']['region']) ? htmlspecialchars($_POST['customer']['address']['region']) : ''; ?>" size="32"><br />
						Country:<br />
						<select name="customer[address][country]">
							<option value="">- Unknown -</option>
							<option value="NL" <?php echo isset($_POST['customer']['address']['country']) && $_POST['customer']['address']['country'] == 'NL' ? ' selected ':'' ?>>Netherlands</option>
							<option value="" <?php echo isset($_POST['customer']['address']['country']) && $_POST['customer']['address']['country'] != 'NL' ? ' selected ':'' ?>>Not Netherlands</option>
						</select><br />
					</fieldset>
					<fieldset>
						<legend>Company</legend>
						Name:<br />
						<input type="text" name="customer[company][name]" value="<?php echo isset($_POST['customer']['company']['name']) ? htmlspecialchars($_POST['customer']['company']['name']) : ''; ?>" size="32"><br />
						Government ID:<br />
						<input type="text" name="customer[company][governmentId]" value="<?php echo isset($_POST['customer']['company']['governmentId']) ? htmlspecialchars($_POST['customer']['company']['governmentId']) : ''; ?>" size="32"><br />
						Country:<br />
						<select name="customer[company][country]">
							<option value="">- Unknown -</option>
							<option value="NL" <?php echo isset($_POST['customer']['company']['country']) && $_POST['customer']['company']['country'] == 'NL' ? ' selected ':'' ?>>Netherlands</option>
							<option value="" <?php echo isset($_POST['customer']['company']['country']) && $_POST['customer']['company']['country'] != 'NL' ? ' selected ':'' ?>>Not Netherlands</option>
						</select><br />
					</fieldset>
				</fieldset>
				<fieldset>
					<legend>Access</legend>
					IP address:<br />
					<input type="text" name="access[ipAddress]" value="<?php echo isset($_POST['access']['ipAddress']) ? htmlspecialchars($_POST['access']['ipAddress']) : ''; ?>" size="32"><br />
					Additional IP addresses:<br />
					<textarea name="access[additionalIpAddresses]"><?php echo isset($_POST['access']['additionalIpAddresses']) ? htmlspecialchars($_POST['access']['additionalIpAddresses']) : '' ?></textarea>(newline separated)<br />
					Session ID:<br />
					<input type="text" name="access[sessionId]" value="<?php echo isset($_POST['access']['sessionId']) ? htmlspecialchars($_POST['access']['sessionId']) : ''; ?>" size="32"><br />
					Time:<br />
					<input type="text" name="access[time]" value="<?php echo isset($_POST['access']['time']) ? htmlspecialchars($_POST['access']['time']) : ''; ?>" size="32"><br />
					<fieldset>
						<legend>Browser</legend>
						User agent:<br />
						<input type="text" name="access[browser][userAgent]" value="<?php echo isset($_POST['access']['browser']['userAgent']) ? htmlspecialchars($_POST['access']['browser']['userAgent']) : ''; ?>" size="32"><br />
						Accept language:<br />
						<input type="text" name="access[browser][acceptLanguage]" value="<?php echo isset($_POST['access']['browser']['acceptLanguage']) ? htmlspecialchars($_POST['access']['browser']['acceptLanguage']) : ''; ?>" size="32"><br />
					</fieldset>
				</fieldset>
				<fieldset>
					<legend>Transaction</legend>
					Internal ID:<br />
					<input type="text" name="transaction[internalId]" value="<?php echo isset($_POST['transaction']['internalId']) ? htmlspecialchars($_POST['transaction']['internalId']) : ''; ?>" size="32"><br />
					Status:<br />
					<select name="transaction[status]">
						<option value="" <?php echo isset($_POST['transaction']['status']) && $_POST['transaction']['status'] == '' ? ' selected ':'' ?>>- Not set -</option>
						<?php foreach (array(
							'new', // Transaction is currently being 'built', ie someone is shopping
							'new-checkout', // Transaction is currently being 'built', and customer is in 'checkout' step
							'pending', // Transaction has been agreed upon by customer, but customer needs to finish some agreed upon actions (unknown actions)
							'pending-payment', // Transaction has been agreed upon by customer, but customer needs to finish external payment
							'processing', // Transaction has been agreed upon and payment is 'completed', shop needs to package & ship order
							'complete', // Transaction has been shipped (but not necessarily delivered)
							'closed', // Transaction has been shipped, and (assumed) delivered
							'cancelled', // Transaction has been cancelled from any state. Order will not continue and will not be revived later
							'cancelled-by-customer', // Transaction has been cancelled by customer, shop needs to reverse any payments made, if necessary
							'cancelled-by-shop', // Transaction has been cancelled by shop, shop needs to reverse any payments made, if necessary
							'onhold', // Transaction needs some custom interaction by customer or shop before it can continue.
							'other', // Another status not listed here
						) as $status) { ?>
							<option value="<?php echo htmlspecialchars($status) ?>" <?php echo isset($_POST['transaction']['status']) && $_POST['transaction']['status'] == $status ? ' selected ':'' ?>><?php echo htmlspecialchars($status)?></option>
						<?php } ?>
					</select><br />
					Cost:<br />
					<input type="text" name="transaction[cost]" value="<?php echo isset($_POST['transaction']['cost']) ? htmlspecialchars($_POST['transaction']['cost']) : ''; ?>" size="8"><br />
					Cost Currency:<br />
					<input type="text" name="transaction[costCurrency]" value="<?php echo isset($_POST['transaction']['costCurrency']) ? htmlspecialchars($_POST['transaction']['costCurrency']) : ''; ?>" size="3"><br />
					Payment type:<br />
					<input type="text" name="transaction[paymentType]" value="<?php echo isset($_POST['transaction']['paymentType']) ? htmlspecialchars($_POST['transaction']['paymentType']) : ''; ?>" size="32"><br />
					Weight:<br />
					<input type="text" name="transaction[weight]" value="<?php echo isset($_POST['transaction']['weight']) ? htmlspecialchars($_POST['transaction']['weight']) : ''; ?>" size="32"><br />
					<fieldset>
						<legend>Delivery Address</legend>
						Postcode:<br />
						<input type="text" name="transaction[deliveryAddress][postcode]" value="<?php echo isset($_POST['transaction']['deliveryAddress']['postcode']) ? htmlspecialchars($_POST['transaction']['deliveryAddress']['postcode']) : ''; ?>" size="8"><br />
						House number:<br />
						<input type="text" name="transaction[deliveryAddress][houseNumber]" value="<?php echo isset($_POST['transaction']['deliveryAddress']['houseNumber']) ? htmlspecialchars($_POST['transaction']['deliveryAddress']['houseNumber']) : ''; ?>" size="8"><br />
						House number addition:<br />
						<input type="text" name="transaction[deliveryAddress][houseNumberAddition]" value="<?php echo isset($_POST['transaction']['deliveryAddress']['houseNumberAddition']) ? htmlspecialchars($_POST['transaction']['deliveryAddress']['houseNumberAddition']) : ''; ?>" size="8"><br />
						Street:<br />
						<input type="text" name="transaction[deliveryAddress][street]" value="<?php echo isset($_POST['transaction']['deliveryAddress']['street']) ? htmlspecialchars($_POST['transaction']['deliveryAddress']['street']) : ''; ?>" size="32"><br />
						City:<br />
						<input type="text" name="transaction[deliveryAddress][city]" value="<?php echo isset($_POST['transaction']['deliveryAddress']['city']) ? htmlspecialchars($_POST['transaction']['deliveryAddress']['city']) : ''; ?>" size="32"><br />
						Region:<br />
						<input type="text" name="transaction[deliveryAddress][region]" value="<?php echo isset($_POST['transaction']['deliveryAddress']['region']) ? htmlspecialchars($_POST['transaction']['deliveryAddress']['region']) : ''; ?>" size="32"><br />
						Country:<br />
						<select name="transaction[deliveryAddress][country]">
							<option value="">- Unknown -</option>
							<option value="NL" <?php echo isset($_POST['transaction']['deliveryAddress']['country']) && $_POST['transaction']['deliveryAddress']['country'] == 'NL' ? ' selected ':'' ?>>Netherlands</option>
							<option value="" <?php echo isset($_POST['transaction']['deliveryAddress']['country']) && $_POST['transaction']['deliveryAddress']['country'] != 'NL' ? ' selected ':'' ?>>Not Netherlands</option>
						</select><br />
					</fieldset>
				</fieldset>
				<fieldset>
					<legend>Config</legend>
					Select Services:<br />
					<textarea name="config[selectServices]"><?php echo isset($_POST['config']['selectServices']) ? htmlspecialchars($_POST['config']['selectServices']) : '' ?></textarea>(newline separated)<br />
					Exclude Services:<br />
					<textarea name="config[excludeServices]"><?php echo isset($_POST['config']['excludeServices']) ? htmlspecialchars($_POST['config']['excludeServices']) : '' ?></textarea>(newline separated)<br />
					Select Result Types:<br />
					<textarea name="config[selectTypes]"><?php echo isset($_POST['config']['selectTypes']) ? htmlspecialchars($_POST['config']['selectTypes']) : '' ?></textarea>(newline separated)<br />
					Exclude Result Types:<br />
					<textarea name="config[excludeTypes]"><?php echo isset($_POST['config']['excludeTypes']) ? htmlspecialchars($_POST['config']['excludeTypes']) : '' ?></textarea>(newline separated)<br />
				</fieldset>
			</fieldset>
			<br />
			<input type="submit" value="Send"> <label><input type="checkbox" name="showRawRequestResponse" value="1" <?php echo !empty($_POST['showRawRequestResponse']) ? 'checked="checked"' : ''; ?>> Show raw HTTP request and response</label><br />
		<?php } ?>

	</form>
<?php
	if (isset($_POST['key']))
	{
		$start = microtime(true);
		try
		{
			$client = new PostcodeNl_Api_RestClient($_POST['key'], $_POST['secret'], !empty($_POST['apiUrl']) ? $_POST['apiUrl'] : null);

			if (!empty($_POST['showRawRequestResponse']))
				$client->setDebugEnabled();

			if ($_POST['service'] == 'Address' && isset($_POST['postcode']))
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
			else if ($_POST['service'] == 'Signal' && isset($_POST['config']))
			{
				// Split array inputs, and clear specific unconfigured parameters
				// (Only needed because we use simple HTML text input fields for things like arrays and null values)
				$_POST['config']['selectServices'] = empty($_POST['config']['selectServices']) ? null : explode("\r\n", $_POST['config']['selectServices']);
				$_POST['config']['excludeServices'] = empty($_POST['config']['excludeServices']) ? null : explode("\r\n", $_POST['config']['excludeServices']);
				$_POST['config']['selectTypes'] = empty($_POST['config']['selectTypes']) ? null : explode("\r\n", $_POST['config']['selectTypes']);
				$_POST['config']['excludeTypes'] = empty($_POST['config']['excludeTypes']) ? null : explode("\r\n", $_POST['config']['excludeTypes']);
				$_POST['access']['additionalIpAddresses'] = empty($_POST['access']['additionalIpAddresses']) ? null : explode("\r\n", $_POST['access']['additionalIpAddresses']);
				if ($_POST['config'] === array('selectServices' => null, 'excludeServices' => null, 'selectTypes' => null, 'excludeTypes' => null))
					$_POST['config'] = null;
				if (isset($_POST['access']['ipAddress']) && $_POST['access']['ipAddress'] == '')
					$_POST['access']['ipAddress'] = null;

				$result = $client->doSignalCheck($_POST['customer'], $_POST['access'], $_POST['transaction'], $_POST['config']);

				echo '<hr>';
				echo '<h2>Signal check response</h2>';

				if (isset($result['signals']))
					echo count($result['signals']) .' signal(s) reported: ';

				if (isset($result['reportPdfUrl']))
					echo '<a href="'. htmlspecialchars($result['reportPdfUrl']) .'" target="_blank">PDF report</a><br />';
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

		if (!empty($_POST['showRawRequestResponse']) && isset($client))
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
