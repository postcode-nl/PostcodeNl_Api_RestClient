<?php
/**
 * PostcodeNl
 *
 * LICENSE:
 * This source file is subject to the Simplified BSD license that is
 * bundled * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://api.postcode.nl/license/simplified-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@postcode.nl so we can send you a copy immediately.
 *
 * Copyright (c) 2017 Postcode.nl B.V. (https://services.postcode.nl)
 */

/**
 * Base superclass for Exceptions raised by this class, also exposes any 'exceptionId' received from the service.
 */
class PostcodeNl_Api_RestClient_Exception extends Exception
{
	protected $_exceptionId = null;

	/**
	 * PostcodeNl_Api_RestClient_Exception constructor.
	 *
	 * @param null $message
	 * @param null $exceptionId
	 * @param int $code
	 * @param Exception|null $previous
	 */
	public function __construct($message = null, $exceptionId = null, $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->_exceptionId = $exceptionId;
	}

	public function getExceptionId()
	{
		return $this->_exceptionId;
	}
}

/**
 * Exception raised when user input is invalid.
 */
class PostcodeNl_Api_RestClient_InputInvalidException extends PostcodeNl_Api_RestClient_Exception {}

/**
 * Exception raised when address input contains no formatting errors, but no address could be found.
 */
class PostcodeNl_Api_RestClient_AddressNotFoundException extends PostcodeNl_Api_RestClient_Exception {}

/**
 * Exception raised when an unexpected error occurred in this client.
 */
class PostcodeNl_Api_RestClient_ClientException extends PostcodeNl_Api_RestClient_Exception {}

/**
 * Exception raised when an unexpected error occurred on the remote service.
 */
class PostcodeNl_Api_RestClient_ServiceException extends PostcodeNl_Api_RestClient_Exception {}

/**
 * Exception raised when there is a authentication problem.
 * In a production environment, you probably always want to catch, log and hide these exceptions.
 */
class PostcodeNl_Api_RestClient_AuthenticationException extends PostcodeNl_Api_RestClient_Exception {}

/**
 * Class to connect to the Postcode.nl API web services via the REST endpoint.
 *
 * @see https://api.postcode.nl/
 */
class PostcodeNl_Api_RestClient
{
	/** @var string Default URL where the REST web service is located */
	const DEFAULT_URL = 'https://api.postcode.nl/rest';
	/** @var string Version of the client */
	const VERSION = '1.1.5.0';
	/** @var int Maximum number of seconds allowed to set up the connection. */
	const CONNECTTIMEOUT = 3;
	/** @var int Maximum number of seconds allowed to receive the response. */
	const TIMEOUT = 10;

	/** @var string URL where the REST web service is located */
	protected $_restApiUrl = self::DEFAULT_URL;
	/** @var string Internal storage of the application key of the authentication. */
	protected $_appKey = '';
	/** @var string Internal storage of the application secret of the authentication. */
	protected $_appSecret = '';

	/** @var boolean If debug data is stored. */
	protected $_debugEnabled = false;
	/** @var array|null Debug data storage. */
	protected $_debugData = null;
	/** @var array|null Last response */
	protected $_lastResponseData;


	/**
	 * PostcodeNl_Api_RestClient constructor.
	 *
	 * @param string $appKey Application Key as provided by Postcode.nl
	 * @param string $appSecret string Application Secret as provided by Postcode.nl
	 * @param string|null $restApiUrl Service URL to call. Will default to self::DEFAULT_URL
	 * @throws PostcodeNl_Api_RestClient_ClientException
	 */
	public function __construct($appKey, $appSecret, $restApiUrl = null)
	{
		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;

		if (isset($restApiUrl))
			$this->_restApiUrl = $restApiUrl;

		if (empty($this->_appKey) || empty($this->_appSecret))
			throw new PostcodeNl_Api_RestClient_ClientException('No application key / secret configured, you can obtain these at https://services.postcode.nl.');

		if (!extension_loaded('curl'))
			throw new PostcodeNl_Api_RestClient_ClientException('Cannot use Postcode.nl API client, the server needs to have the PHP `cURL` extension installed.');

		$version = curl_version();
		$sslSupported = ($version['features'] & CURL_VERSION_SSL);
		if (!$sslSupported)
			throw new PostcodeNl_Api_RestClient_ClientException('Cannot use Postcode.nl API client, the server cannot connect to HTTPS urls. (`cURL` extension needs support for SSL)');
	}


	/**
	 * Toggle debug option.
	 *
	 * @param bool $debugEnabled
	 */
	public function setDebugEnabled($debugEnabled = true)
	{
		$this->_debugEnabled = (boolean)$debugEnabled;
		if (!$this->_debugEnabled)
			$this->_debugData = null;
	}


	/**
	 * Get the debug data gathered so far.
	 *
	 * @return array|null
	 */
	public function getDebugData()
	{
		return $this->_debugData;
	}


	/**
	 * Perform a REST call to the Postcode.nl API
	 *
	 * @param string $url
	 * @param array $data
	 * @return array
	 * @throws PostcodeNl_Api_RestClient_ClientException
	 */
	protected function _doRestCall($url, array $data = [])
	{
		// Connect using cURL
		$ch = curl_init();
		// Set the HTTP request type
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		// Set URL to connect to
		curl_setopt($ch, CURLOPT_URL, $url);
		// We want the response returned to us.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Maximum number of seconds allowed to set up the connection.
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
		// Maximum number of seconds allowed to receive the response.
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);

		// The Postcode.nl API uses HTTP BASIC authentication (https://en.wikipedia.org/wiki/Basic_access_authentication)
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		// Use key as 'username' and secret as 'password'
		curl_setopt($ch, CURLOPT_USERPWD, $this->_appKey .':'. $this->_appSecret);
		// Identify this client with a User Agent
		curl_setopt($ch, CURLOPT_USERAGENT, 'PostcodeNl_Api_RestClient/' . self::VERSION .' PHP/'. phpversion());

		// Various debug options
		if ($this->_debugEnabled)
		{
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
		}

		// Do the request
		$response = curl_exec($ch);
		// Remember the HTTP status code we receive
		$responseStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$responseStatusCodeClass = floor($responseStatusCode/100)*100;
		// Any errors? Remember them now.
		$curlError = curl_error($ch);
		$curlErrorNr = curl_errno($ch);

		if ($this->_debugEnabled)
		{
			$this->_debugData['request'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
			$this->_debugData['response'] = $response;

			// Strip off header that was added for debug purposes.
			$response = substr($response, strpos($response, "\r\n\r\n") + 4);
		}

		// And close the cURL handle
		curl_close($ch);

		if ($curlError)
		{
			// We could not connect, cURL has the reason. (we hope)
			throw new PostcodeNl_Api_RestClient_ClientException('Connection error `'. $curlErrorNr .'`: `'. $curlError .'`', $curlErrorNr);
		}

		// Parse the response as JSON, will be null if not parsable JSON.
		$jsonResponse = json_decode($response, true);

		$this->_lastResponseData = $jsonResponse;

		return [
			'statusCode' => $responseStatusCode,
			'statusCodeClass' => $responseStatusCodeClass,
			'data' => $jsonResponse,
		];
	}


	/**
	 * Check the JSON response of the Address API result data.
	 * Will throw an exception if there is an exception or other not expected response.
	 *
	 * @param array $response Response data
	 * @throws PostcodeNl_Api_RestClient_AddressNotFoundException
	 * @throws PostcodeNl_Api_RestClient_AuthenticationException
	 * @throws PostcodeNl_Api_RestClient_InputInvalidException
	 * @throws PostcodeNl_Api_RestClient_ServiceException
	 */
	protected function _checkResponse(array $response)
	{
		// Data present and status code class is 200-299: all is ok
		if (is_array($response['data']) && $response['statusCodeClass'] == 200)
			return;

		// No valid exception message was returned in the JSON (or no JSON at all)
		// Make our own messages based on the HTTP status code
		if (!is_array($response['data']) || !isset($response['data']['exceptionId']))
		{
			if ($response['statusCode'] == 503)
			{
				throw new PostcodeNl_Api_RestClient_ServiceException('Postcode.nl API returned no valid JSON data. HTTP status code `'. $response['statusCode'] .'`: Service Unavailable. You might be rate-limited if you are sending too many requests.');
			}

			throw new PostcodeNl_Api_RestClient_ServiceException('Postcode.nl API returned no valid JSON data. HTTP status code `'. $response['statusCode'] .'`.');
		}

		// Some specific exceptionIds we clarify within the context of our client.
		if ($response['statusCode'] == 401)
		{
			if ($response['data']['exceptionId'] === 'PostcodeNl_Controller_Plugin_HttpBasicAuthentication_PasswordNotCorrectException')
				throw new PostcodeNl_Api_RestClient_AuthenticationException('`Secret` specified in HTTP authentication password is incorrect. ("'. $response['data']['exception'] .'")', $response['data']['exceptionId']);
			if ($response['data']['exceptionId'] === 'PostcodeNl_Controller_Plugin_HttpBasicAuthentication_NotAuthorizedException')
				throw new PostcodeNl_Api_RestClient_AuthenticationException('`Key` specified in HTTP authentication is incorrect. ("'. $response['data']['exception'] .'")', $response['data']['exceptionId']);
		}

		// Specific exception for the Address API when input is correct, but no address is found
		if ($response['statusCode'] == 404)
		{
			if ($response['data']['exceptionId'] === 'PostcodeNl_Service_PostcodeAddress_AddressNotFoundException')
				throw new PostcodeNl_Api_RestClient_AddressNotFoundException($response['data']['exception'], $response['data']['exceptionId']);
		}

		// Our exception types are based on the HTTP status of the response
		if ($response['statusCode'] == 401 || $response['statusCode'] == 403)
		{
			throw new PostcodeNl_Api_RestClient_AuthenticationException($response['data']['exception'], $response['data']['exceptionId']);
		}
		else if ($response['statusCodeClass'] == 400)
		{
			throw new PostcodeNl_Api_RestClient_InputInvalidException($response['data']['exception'], $response['data']['exceptionId']);
		}

		throw new PostcodeNl_Api_RestClient_ServiceException($response['data']['exception'], $response['data']['exceptionId']);
	}


	/**
	 * Look up an address by postcode and house number.
	 *
	 * @param string $postcode Dutch postcode in the '1234AB' format
	 * @param int|string $houseNumber House number (may contain house number addition, will be separated automatically)
	 * @param string $houseNumberAddition House number addition
	 * @param bool $validateHouseNumberAddition Enable to validate the addition
	 * @return array
	 * @throws PostcodeNl_Api_RestClient_InputInvalidException
	 *
	 * @see https://api.postcode.nl/documentation
	 * street - (string) Street name in accordance with "BAG (Basisregistraties adressen en gebouwen)". In capital and lowercase letters, including punctuation marks and accents. This field is at most 80 characters in length. Filled with "Postbus" in case it is a range of PO boxes.
	 * streetNen - (string) Street name in NEN-5825 notation, which has a lower maximum length. In capital and lowercase letters, including punctuation marks and accents. This field is at most 24 characters in length. Filled with "Postbus" in case it is a range of PO boxes.
	 * houseNumber - (int) House number of a 'perceel'. In case of a Postbus match the house number will always be 0. Range: 0-99999
	 * houseNumberAddition - (string|null) Addition of the house number to uniquely define a location. These additions are officially recognized by the municipality. Null if addition not found (see houseNumberAdditions result field).
	 * postcode - (string) Four number neighborhood code (first part of a postcode). Range: 1000-9999 plus two character letter combination (second part of a postcode). Range: "AA"-"ZZ"
	 * city - (string) Official city name in accordance with "BAG (Basisregistraties adressen en gebouwen)". In capital and lowercase letters, including punctuation marks and accents. This field is at most 80 characters in length.
	 * cityShort - (string) City name, shortened to fit a lower maximum length. In capital and lowercase letters, including punctuation marks and accents. This field is at most 24 characters in length.
	 * municipality - (string) Municipality name in accordance with "BAG (Basisregistraties adressen en gebouwen)". In capital and lowercase letters, including punctuation marks and accents. This field is at most 80 characters in length. Examples: "Baarle-Nassau", "'s-Gravenhage", "Haarlemmerliede en Spaarnwoude".
	 * municipalityShort - (string) Municipality name, shortened to fit a lower maximum length. In capital and lowercase letters, including punctuation marks and accents. This field is at most 24 characters in length. Examples: "Baarle-Nassau", "'s-Gravenhage", "Haarlemmerliede c.a.".
	 * province - (string) Official name of the province, correctly cased and with dashes where applicable.
	 * rdX - (int) X coordinate according to Dutch Rijksdriehoeksmeting "(EPSG) 28992 Amersfoort / RD New". Values range from 0 to 300000 meters. Null for PO Boxes.
	 * rdY - (int) Y coordinate according to Dutch Rijksdriehoeksmeting "(EPSG) 28992 Amersfoort / RD New". Values range from 300000 to 620000 meters. Null for PO Boxes.
	 * latitude - (float) Latitude of address. Null for PO Boxes.
	 * longitude - (float) Longitude of address. Null for PO Boxes.
	 * bagNumberDesignationId - (string) Dutch term used in BAG: "nummeraanduiding id".
	 * bagAddressableObjectId - (string) Dutch term used in BAG: "adresseerbaar object id". Unique identification for objects which have 'building', 'house boat site', or 'mobile home site' as addressType.
	 * addressType - (string) Type of address, see reference link
	 * purposes - (array) Array of strings, each indicating an official Dutch 'usage' category, see reference link
	 * surfaceArea - (int) Surface area of object in square meters (all floors). Null for PO Boxes.
	 * houseNumberAdditions - (array) List of all house number additions having the postcode and houseNumber which was input.
	 */
	public function lookupAddress($postcode, $houseNumber, $houseNumberAddition = '', $validateHouseNumberAddition = false)
	{
		// Remove spaces in postcode ('1234 AB' should be '1234AB')
		$postcode = str_replace(' ', '', trim($postcode));
		$houseNumber = trim($houseNumber);
		$houseNumberAddition = trim($houseNumberAddition);

		if ($houseNumberAddition == '')
		{
			// If people put the housenumber addition in the housenumber field - split this.
			list($houseNumber, $houseNumberAddition) = $this->splitHouseNumber($houseNumber);
		}

		// Test postcode format
		if (!$this->isValidPostcodeFormat($postcode))
			throw new PostcodeNl_Api_RestClient_InputInvalidException('Postcode `'. $postcode .'` needs to be in the 1234AB format.');
		// Test housenumber format
		if (!ctype_digit($houseNumber))
			throw new PostcodeNl_Api_RestClient_InputInvalidException('House number `'. $houseNumber .'` must contain digits only.');

		// Use the regular validation function
		$url = $this->_restApiUrl .'/addresses/postcode/' . rawurlencode($postcode). '/'. rawurlencode($houseNumber) . '/'. rawurlencode($houseNumberAddition);

		$response = $this->_doRestCall($url);

		$this->_checkResponse($response);

		// Strictly enforce housenumber addition validity
		if ($validateHouseNumberAddition)
		{
			if ($response['data']['houseNumberAddition'] === null)
				throw new PostcodeNl_Api_RestClient_InputInvalidException('Housenumber addition `'. $houseNumberAddition .'` is not known for this address, valid additions are: `'. implode('`, `', $response['data']['houseNumberAdditions']) .'`.');
		}

		// Successful response!
		return $response['data'];
	}


	/**
	 * Validate if string has a correct Dutch postcode format.
	 * Syntax: 1234AB, or 1234ab - no space in between. First digit cannot be a zero.
	 *
	 * @param string $postcode
	 * @return bool
	 */
	public function isValidPostcodeFormat($postcode)
	{
		return (boolean)preg_match('~^[1-9][0-9]{3}[a-zA-Z]{2}$~', $postcode);
	}

	/**
	 * Split a housenumber addition from a housenumber.
	 *
	 * Examples: "123 2", "123 rood", "123a", "123a4", "123-a", "123 II"
	 * (the official notation is to separate the housenumber and addition with a single space)
	 *
	 * @param string $houseNumber
	 * @return array Array with houseNumber and houseNumberAddition values
	 */
	public function splitHouseNumber($houseNumber)
	{
		$houseNumberAddition = '';
		if (preg_match('~^(?<number>[0-9]+)(?:[^0-9a-zA-Z]+(?<addition1>[0-9a-zA-Z ]+)|(?<addition2>[a-zA-Z](?:[0-9a-zA-Z ]*)))?$~', $houseNumber, $match))
		{
			$houseNumber = $match['number'];
			$houseNumberAddition = isset($match['addition2']) ? $match['addition2'] : (isset($match['addition1']) ? $match['addition1'] : '');
		}

		return [$houseNumber, $houseNumberAddition];
	}

	/**
	 * Return the last decoded JSON response received, can be used to get more information from exceptions, or debugging.
	 *
	 * @return array|null
	 */
	public function getLastResponseData()
	{
		return $this->_lastResponseData;
	}
}
