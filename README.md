Postcode.nl API REST Client
=============

A PHP 5.2+ class, which offers methods to directly talk with the [Postcode.nl API](https://api.postcode.nl).
You will need to create an account with the [Postcode.nl API](https://api.postcode.nl) service.

Usage
=============

Include the class in your PHP project, instantiate the PHP class with your authentication details and call the 'lookupAddress' method.
You can handle errors by catching the defined Exception classes.
(See the 'library/PostcodeNl/Api/RestClient.php' file for details on which exceptions can be thrown)

```PHP
<?php
	require_once('/PATH/TO/library/PostcodeNl/Api/RestClient.php';
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