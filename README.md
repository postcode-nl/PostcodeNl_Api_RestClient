Postcode.nl API REST Client
=============

A PHP 5.2+ class, which offers methods to directly talk with the [Postcode.nl API](https://api.postcode.nl/documentation) through the REST endpoint offered.
You will need to create an account with the [Postcode.nl API](https://api.postcode.nl) service.

Implements both the [Address service](https://api.postcode.nl/documentation/address-api-description) and the [Signal service](https://api.postcode.nl/documentation/signal-api-description).

License
=============

The code is available under the open source Simplified BSD license. (see LICENSE.txt)

Installation
=============

The best way to install is by using [PHP Composer](https://getcomposer.org/), get package [`postcode-nl/api-restclient`](https://packagist.org/packages/postcode-nl/api-restclient) and stay up to date easily.

Or download the source from our GitHub page: https://github.com/postcode-nl/PostcodeNl_Api_RestClient

Usage Address API
=============

Include the class in your PHP project, instantiate the PHP class with your authentication details and call the 'lookupAddress' method.
You can handle errors by catching the defined Exception classes.
(See the 'library/PostcodeNl/Api/RestClient.php' file for details on which exceptions can be thrown)

* See our [Address API description](https://api.postcode.nl/documentation/address-api-description) for more information
* See our [Address API method documentation](https://api.postcode.nl/documentation/rest-json-endpoint#address-api) for the possible fields

```PHP
<?php
	require_once '/PATH/TO/library/PostcodeNl/Api/RestClient.php';
	$client = new PostcodeNl_Api_RestClient('{your key}', '{your secret}');

	// Look up the address for Dutch postcode 2012ES, housenumber 30,
	// with no housenumber addition.
	try
	{
		$address = $client->lookupAddress('2012ES', '30', '');
	}
	catch (PostcodeNl_Api_RestClient_AddressNotFoundException $e)
	{
		die('There is no address on this postcode/housenumber combination: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_InputInvalidException $e)
	{
		die('We have input which can never return a valid address: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_ClientException $e)
	{
		die('We have a problem setting up our client connection: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_AuthenticationException $e)
	{
		die('The Postcode.nl API service does not know who we are: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_ServiceException $e)
	{
		die('The Postcode.nl API service reported an error: '. $e);
	}

	// Print the address data
	echo var_export($address, true);
```

Usage Signal API
=============

Include the class in your PHP project, instantiate the PHP class with your authentication details and call the 'doSignalCheck' method.
You can handle errors by catching the defined Exception classes.
(See the 'library/PostcodeNl/Api/RestClient.php' file for details on which exceptions can be thrown)

* See our [Signal API description](https://api.postcode.nl/documentation/signal-api-description) for more information
* See our [Signal API check method documentation](https://api.postcode.nl/documentation/rest-json-endpoint#signal-api) for the possible fields to pass.
* See our [basic example](https://api.postcode.nl/documentation/signal-api-example) for a practical example


```PHP
<?php
	require_once('/PATH/TO/library/PostcodeNl/Api/RestClient.php';
	$client = new PostcodeNl_Api_RestClient('{your key}', '{your secret}');

	// Do a Postcode.nl Signal check (information validation, enrichment and fraud warnings)
	try
	{
		$signalCheck = $client->doSignalCheck(
			array(
				// Customer information (see documentation)
			),
			array(
				// HTTP / Browser access information (see documentation)
			),
			array(
				// Transaction information (see documentation)
			),
			array(
				// Configuration for the signal services to use (see documentation)
			)
		);
	}
	catch (PostcodeNl_Api_RestClient_InputInvalidException $e)
	{
		die('We have input which can never return a valid signal check: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_ClientException $e)
	{
		die('We have a problem setting up our client connection: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_AuthenticationException $e)
	{
		die('The Postcode.nl API service does not know who we are: '. $e);
	}
	catch (PostcodeNl_Api_RestClient_ServiceException $e)
	{
		die('The Postcode.nl API service reported an error: '. $e);
	}

	// Print the Signal Check info
	echo var_export($signalCheck, true);
```
