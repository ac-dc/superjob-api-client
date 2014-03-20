<?php

class SuperjobAPIClient
{
    const API_URI = 'api.superjob.ru/2.0/';
    const OAUTH_URL = 'https://api.superjob.ru/2.0/oauth2/';

    const OAUTH_AUTHORIZE_URL = 'http://www.superjob.ru/authorize';
	
	/**
     * {@link setTimeout()}
     *
     * @var integer
     */
    protected $_timeout = 15;

    /**
     * {@link setObjectOutput()}
     * @var bool
     **/
    protected $_object_output = false;

    /**
     * HTTP Code of the last Curl Request
     *
     * @var bool|int
     */
    protected $_http_code = false;

    /**
     * HTTP Stored data of the last Curl Request
     *
     * @var mixed
     */
    protected $_data;	
	
    /**
     * Instance of SuperjobAPIClient
     *
     * @param SuperjobAPIClient $_instance
     */
    static protected $_instance;


    public function __construct($timeout = 10)
    {
        $this->setTimeout($timeout);
    }

    /**
     * Singletone
     *
     * @return SuperjobAPIClient
     */
    static public function instance()
    {
        if (empty(self::$_instance))
        {
            $class = __CLASS__;
            self::$_instance = new $class;
        }
        return self::$_instance;
    }

    /**
     * Call of Superjob API's vacancies method implementation
     *
     * @param array $data
     * @param OAuthToken $access_token
     * @return string
     */
    public function vacancies($data = array(), $access_token = null)
    {
        return $this->_sendGetRequest('vacancies', $data, $access_token);
    }


    /**
     * Call of Superjob API's vacancies/:id method implementation
     *
     * @param int $id - ID of vacancy
     * @param array $data
     * @param OAuthToken $access_token
     * @return string
     */
    public function vacancy($id, $data = array(), $access_token = null)
    {
        return $this->_sendGetRequest('vacancies/'.$id, $data, $access_token);
    }


    /**
     * Call of Superjob API's clients method implementation
     *
     * @param array $data
     * @return string
     */
    public function clients($data = array())
    {
        return $this->_sendGetRequest('clients', $data);
    }

    /**
     * Call of Superjob API's client/:id method implementation
     *
     * @param int $id - ID of client
     * @param array $data
     * @return string
     */
    public function client($id, $data = array())
    {
        return $this->_sendGetRequest('clients/'.$id, $data);
    }

    /**
     * Call of Superjob API's institutes method implementation
     *
     * @param array $data
     * @return string
     */
    public function institutes($data = array())
    {
        return $this->_sendGetRequest('institutes', $data);
    }

    /**
     * Call of Superjob API's towns method implementation
     *
     * @param array $data
     * @return string
     */
    public function towns($data = array())
    {
        return $this->_sendGetRequest('towns', $data);
    }

    /**
     * Call of Superjob API's countries method implementation
     *
     * @param array $data
     * @return string
     */
    public function countries($data = array())
    {
        return $this->_sendGetRequest('countries', $data);
    }


    /**
     * Call of Superjob API's regions method implementation
     *
     * @param array $data
     * @return string
     */
    public function regions($data = array())
    {
        return $this->_sendGetRequest('regions', $data);
    }

    /**
     * Call of Superjob API's catalogues method implementation
     *
     * @param array $data
     * @return string
     */
    public function catalogues()
    {
        return $this->_sendGetRequest('catalogues');
    }

    /**
     * Call of Superjob API's catalogues/:id method implementation
     *
     * @param array $data
     * @return string
     */
    public function catalogue($id)
    {
        return $this->_sendGetRequest('catalogues/'.(int)$id);
    }


    /**
     * Call of Superjob API's catalogues/parent/:id method implementation
     *
     * @param array $data
     * @return string
     */
    public function cataloguesByParent($id)
    {
        return $this->_sendGetRequest('catalogues/parent/'.(int)$id);
    }


    /**
     * Call of Superjob API's forgot_password method implementation
     *
     * @param array $data
     * @return string
     */
    public function forgot_password($data = array())
    {
        return $this->_sendPostRequest('forgot_password', $data);
    }

    /**
     * Call of Superjob API's send_cv_on_vacancy method implementation
     *
     * @param array $data
     * @param OAuthToken $access_token
     * @return string
     */
    public function send_cv_on_vacancy($data = array(), $access_token)
    {
        return $this->_sendPostRequest('send_cv_on_vacancy', $data, $access_token);
    }

    /**
     * Call of Superjob API's user/current method implementation
     *
     * @param OAuthToken $access_token
     * @return string
     */
    public function current_user($access_token)
    {
        return $this->_sendGetRequest('user/current', array(), $access_token);
    }

    /**
     * Call of Superjob API's user_cvs method implementation
     *
     * @param OAuthToken $access_token
     * @return string
     */
    public function user_cvs($access_token)
    {
        return $this->_sendGetRequest('user_cvs', array(), $access_token);
    }


    /**
     * Sets the length of time (in seconds) to wait for a respnse from Superjob before timing out.
     *
     * Provides a fluent interface.
     *
     * @param integer $timeout Length of time (in seconds) before timeout
     *
     * @return SuperjobAPIClient
     */
    public function setTimeout($timeout)
    {
        assert(is_numeric($timeout));

        $this->_timeout = $timeout;

        return $this;
    }

    /**
     * Returns all data as an objects
     *
     *
     * @return void
     */
    public function setObjectOutput()
    {
        $this->_object_output = true;
    }

    /**
     * Tells was the last request successfull or not
     *
     * @return bool
     */
    public function hasError()
    {
        return strpos((string)$this->_http_code, '2') !== 0;
    }
	
    /**
     * Tells was the last request successfull or not
     *
     * @return bool
     */
    public function lastError()
    {
		if ($this->_object_output)
		{
			 return (!empty($this->_data) && !empty($this->_data->error->message)) 
					? $this->_data->error->message 
					: false;
		}
        return (!empty($this->_data) && !empty($this->_data['error']['message'])) 
					? $this->_data['error']['message'] 
					: false;
    }	

    /**
     * Sends custom request to API
     *
     * @param string $name - API Method
     * @param array $data - API Method's parameters
     * @param OAuthToken $access_token
     * @param string $method Specifies the HTTP method to be used for this request
     * @return string
     */
    public function customQuery($name, $data = array(), $access_token = null, $method = 'GET', $no_processing = false)
    {
        $url = $method === 'GET' ? $this->_buildUrl($name, $this->_buildQueryString($data)) : $this->_buildUrl($name);

        $url = (!empty($access_token))
            ? $this->_signRequest($url, $access_token)
            : $url;

        return $this->_sendRequest($url, $method, $method === 'POST' ? $data : '', $no_processing);
    }

    public function parallelResults($data)
    {
        $mas = explode("\n", $data);
        foreach ($mas as $k => $v)
        {
            $mas[$k] = json_decode($v, !$this->_object_output);
        }

        return $mas;
    }

    /**
     * Sends the GET request to API
     *
     * @param string $name - API Method
     * @param array $data - API Method's parameters
     * @param OAuthToken $access_token
     * @return string
     */
    protected function _sendGetRequest($name, $data = array(), $access_token = null)
    {
        $url = $this->_buildUrl($name, $this->_buildQueryString($data));

        $url = (!empty($access_token))
            ? $this->_signRequest($url, $access_token)
            : $url;

        return $this->_sendRequest($url, 'GET');
    }


    /**
     * Sends the POST request to API
     *
     * @param string $name - API Method
     * @param array $data - API Method's parameters
     * @param OAuthToken $access_token
     * @return string
     */
    protected function _sendPostRequest($name, $data = array(), $access_token = null)
    {
        $url = $this->_buildUrl($name);

        $url = (!empty($access_token))
            ? $this->_signRequest($url, $access_token)
            : $url;

        return $this->_sendRequest($url, 'POST', $data);
    }


    /**
     * Sends an HTTP request to Superjob API
     *
     * @param string  $uri    Target URI for this request (relative to the API root)
     * @param string  $method Specifies the HTTP method to be used for this request
     * @param mixed   $data   x-www-form-urlencoded data (or array) to be sent in a POST request body
     *
     * @return array|null
     * @throws SuperjobAPIException
     */
    protected function _sendRequest($url, $method = 'GET', $data = '', $no_processing = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ($ch, CURLOPT_HEADER, true);

        if('POST' == ($method = strtoupper($method)))
        {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        else if('GET' != $method)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);

        $data = curl_exec($ch);
        $this->_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($data === false)
        {
            $this->_throwException(curl_error($ch));
        }

        $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $data = substr($data, $size);
        if (!empty($data) && ($no_processing === false))
        {
            $data = json_decode($data, !$this->_object_output);
			$this->_data = $data;
			// If it is an error - let's there be an exception
			if ($error = $this->lastError())
			{
				$this->_throwException($error);
			}			
        }
        return $data;
    }

    /**
     * Makes an URL
     *
     * @param string $uri
     * @return string
     */
    protected function _buildUrl($url, $params = '')
    {
        return (stripos($url, self::API_URI) === false)
            ? "https://".self::API_URI.$url.'/'.$params
            : $url.'/'.$params;
    }

    /**
     * Builds a query string from an array of parameters and values.
     *
     * @param array $args    Parameter/value pairs to be evaluated for this query string
     *
     * @return string
     * @throws SuperjobAPIException
     */
    protected function _buildQueryString(array $args)
    {
        // Set Custom Output Format
        if (!empty($this->_format) && !isset($args['type']))
        {
            $args['type'] = $this->_format;
        }

        return count($args) ? '?' . http_build_query($args) : '';
    }

    /**
     * Throws an SuperjobAPIException
     *
     * @param string $message Message to be provided with the exception
     *
     * @throws SuperjobAPIException
     */
    protected static function _throwException($message)
    {
        throw new SuperjobAPIException($message);
    }

    /**
     * Refreshes the Access Token
     *
     * @param string $refresh_token
     * @param string $client_id		- app's id
     * @param string $client_secret - apps's secret key
     *
     * @return array
     */
    public function refreshAccessToken($refresh_token, $client_id, $client_secret)
    {
        $data = compact('refresh_token', 'client_id', 'client_secret');

        return $this->_sendGetRequest(self::OAUTH_URL.'access_token' , $data);
    }

    /**
     * Acquires the Access Token
     *
     * @param string $code			- code GET-paramenter
     * @param string $redirect_uri
     * @param string $client_id		- app's id
     * @param string $client_secret - apps's secret key
     *
     * @return array
     */
    public function fetchAccessToken($code, $redirect_uri, $client_id, $client_secret)
    {
        $data = compact('code', 'redirect_uri', 'client_id', 'client_secret');

        return $this->_sendGetRequest(self::OAUTH_URL.'access_token' , $data);
    }

    /**
     * Makes a redirect to the authorize page
     *
     * @param int $client_id
     * @param string $return_uri
     */
    public function redirectToAuthorizePage($client_id, $return_uri)
    {
        $auth_url = self::OAUTH_AUTHORIZE_URL.'?client_id='.$client_id.'&redirect_uri='.urlencode($return_uri);

        header('Location: '.$auth_url);
        exit;
    }

    /**
     * Signs the request
     *
     * @param string $url
     * @param string $access_token
     * @return string
     */
    protected function _signRequest($url, $access_token)
    {
        $parsed = parse_url($url);
        $sign = empty($parsed['query']) ? '?' : '&';

        return $url.$sign.'access_token='.$access_token;
    }
}

class SuperjobAPIException extends Exception {}
?>