Postcode.nl API REST Client
=============

A PHP 5.3+ class, which offers methods to directly talk with the [Postcode.nl API](https://api.postcode.nl/documentation) through the REST endpoint offered.
You will need to create an account with the [Postcode.nl API](https://services.postcode.nl/adresdata/api) service.

Implements both the [Address service](https://services.postcode.nl/adres-api) and the [Signal service](https://services.postcode.nl/adres-api/signaal).

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

* See our [Address API description](https://services.postcode.nl/adresdata/api) for more information
* See our [Address API method documentation](https://api.postcode.nl/documentation/address-api) for the possible fields

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