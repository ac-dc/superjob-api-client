<?php
// ID app
define("OA_CONSUMER_KEY", 1); 
// Secret key
define("OA_CONSUMER_SECRET", "Your secret here");

include_once('class.OAuth.php');

class SuperjobAPIClient
{
	const API_URI = 'api.superjob.ru/1.0/';
	const OAUTH_REQUEST_TOKEN_URL = 'https://api.superjob.ru/1.0/oauth/request_token';
	const OAUTH_ACCESS_TOKEN_URL = 'https://api.superjob.ru/1.0/oauth/access_token';
	const OAUTH_AUTHORIZE_URL = 'http://www.superjob.ru/authorize';
    /**
     * {@link setTimeout()}
     *
     * @var integer
     */
    protected $_timeout = 15;
    
    /**
     * Format of API's output
     * Equals to 'json', 'xml' or 'text'
     *
     * @var string
     */
    protected $_format = false;
    
    /**
     * HTTP Code of the last Curl Request
     *
     * @var int
     */
    protected $_http_code = false;
    
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
     * Sets the length of time (in seconds) to wait for a respnse from Superjob before timing out.
     *
     * Provides a fluent interface.
     *
     * @param string $timeout Length of time (in seconds) before timeout
     *
     * @return SuperjobAPIClient
     */
    public function setFormat($format)
    {
        assert(is_string($format));

        $this->_format = (in_array($format, array('json', 'xml', 'text')) ? $format : false);

        return $this;
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

    	$url = ($access_token instanceof OAuthToken) 
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

    	$url = ($access_token instanceof OAuthToken) 
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
     * @return string
     * @throws SuperjobAPIException
     */
    protected function _sendRequest($url, $method = 'GET', $data = '')
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
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        else if('GET' != $method)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);

        $data = curl_exec($ch);
        $this->_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($data === false)
        {
        	$this->_throwException(curl_error($ch));
        }

        $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
	return substr($data, $size);
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
     * @param array $allowed Optional array of allowed parameter keys
     *
     * @return string
     * @throws SuperjobAPIException
     */
    protected function _buildQueryString(array $args, array $allowed =array())
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
     * Allows a Consumer application to obtain an OAuth Request Token to request user authorization.
     *
     * @return OAuthToken
     * @throws OAuthException
     */
    public function fetchRequestToken()
    {
		$consumer = new OAuthConsumer(OA_CONSUMER_KEY, OA_CONSUMER_SECRET);
		$req = OAuthRequest::from_consumer_and_token(
			$consumer, 
			NULL, 
			"GET", 
			self::OAUTH_REQUEST_TOKEN_URL
		);

		$req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

		$parsed = OAuthUtil::parse_parameters($this->_sendRequest($req->to_url()));
		return new OAuthToken($parsed['oauth_token'], $parsed['oauth_token_secret']);
    }
    
    /**
     * Allows a Consumer application to exchange the OAuth Request Token for an OAuth Access Token.
     *
     * @param OAuthToken $request_token
     * 
     * @return OAuthToken
     * @throws OAuthException
     */
    public function fetchAccessToken($request_token, $data = array())
    {
		$consumer = new OAuthConsumer(OA_CONSUMER_KEY, OA_CONSUMER_SECRET);
		$req = OAuthRequest::from_consumer_and_token(
			$consumer, 
			$request_token, 
			"GET", 
			self::OAUTH_ACCESS_TOKEN_URL,
			$data
		);
		$req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $request_token);

		$parsed = OAuthUtil::parse_parameters($this->_sendRequest($req->to_url()));
		return new OAuthToken($parsed['oauth_token'], $parsed['oauth_token_secret']);
    }
    
    /**
     * Makes a redirect to authorize page
     *
     * @param OAuth $request_token
     * @param string $callback
     */
    public function redirectToAuthorizePage($request_token, $callback = null)
    {
		$auth_url = self::OAUTH_AUTHORIZE_URL.'?'.'&oauth_token='.$request_token->key
			.($callback ? '&oauth_callback='.urlencode($callback) : '');

		header('Location: '.$auth_url);
		exit();
    }
    
    /**
     * Signs the request for OAuth
     *
     * @param string $url
     * @param OAuthToken $access_token
     * @return string
     */
    protected function _signRequest($url, $access_token)
    {
    	$consumer = new OAuthConsumer(OA_CONSUMER_KEY, OA_CONSUMER_SECRET);
		$req = OAuthRequest::from_consumer_and_token(
				$consumer, $access_token, 
				'GET', $url
			);
		$req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $access_token);
		return $req->to_url();
    }
}

class SuperjobAPIException extends Exception {}
?>