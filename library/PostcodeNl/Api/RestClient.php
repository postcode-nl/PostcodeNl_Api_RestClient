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
	const VERSION = '1.1.4.0';
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
	 * @param string $method Http method
	 * @param string $url
	 * @param array $data
	 * @return array
	 * @throws PostcodeNl_Api_RestClient_ClientException
	 */
	protected function _doRestCall($method, $url, array $data = [])
	{
		// Connect using cURL
		$ch = curl_init();
		// Set the HTTP request type, GET / POST most likely.
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		// Set URL to connect to
		curl_setopt($ch, CURLOPT_URL, $url);
		// We want the response returned to us.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Maximum number of seconds allowed to set up the connection.
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
		// Maximum number of seconds allowed to receive the response.
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
		// How do we authenticate ourselves? Using HTTP BASIC authentication (https://en.wikipedia.org/wiki/Basic_access_authentication)
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		// Set our key as 'username' and our secret as 'password'
		curl_setopt($ch, CURLOPT_USERPWD, $this->_appKey .':'. $this->_appSecret);
		// To be tidy, we identify ourselves with a User Agent. (not required)
		curl_setopt($ch, CURLOPT_USERAGENT, 'PostcodeNl_Api_RestClient/' . self::VERSION .' PHP/'. phpversion());

		// Add any data as JSON encoded information
		if ($method != 'GET' && isset($data))
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		}

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

			if ($method != 'GET' && isset($data))
				$this->_debugData['request'] .= json_encode($data);

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
	 * street - (string) Official name of the street.
	 * houseNumber - (int) House number
	 * houseNumberAddition - (string|null) House number addition if given and validated, null if addition is not valid / not found
	 * postcode - (string) Postcode
	 * city - (string) Official city name
	 * municipality - (string) Official municipality name
	 * province - (string) Official province name
	 * rdX - (int) X coordinate of the Dutch Rijksdriehoeksmeting
	 * rdY - (int) Y coordinate of the Dutch Rijksdriehoeksmeting
	 * latitude - (float) Latitude of the address (front door of the premise)
	 * longitude - (float) Longitude of the address
	 * bagNumberDesignationId - (string) Official Dutch BAG id
	 * bagAddressableObjectId - (string) Official Dutch BAG Address Object id
	 * addressType - (string) Type of address, see reference link
	 * purposes - (array) Array of strings, each indicating an official Dutch 'usage' category, see reference link
	 * surfaceArea - (int) Surface area of object in square meters (all floors)
	 * houseNumberAdditions - (array) All housenumber additions which are known for the housenumber given.
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
		$url = $this->_restApiUrl .'/addresses/' . rawurlencode($postcode). '/'. rawurlencode($houseNumber) . '/'. rawurlencode($houseNumberAddition);

		$response = $this->_doRestCall('GET', $url);

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
