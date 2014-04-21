<?php

class SuperjobAPI
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
     * Instance of SuperjobAPI
     *
     * @param SuperjobAPI $_instance
     */
    static protected $_instance;

    /**
     * {@link setParallelMode()}
     *
     * @var bool
     */	
	protected $_parallel = false;
	
    /**
     * Parallel storage
     *
     * @var array
     */	
	protected $_parallel_data = array();
	
    public function __construct($timeout = 10)
    {
        $this->setTimeout($timeout);
    }

    /**
     * Singleton
     *
     * @return SuperjobAPI
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
     * Call of Superjob API's catalogues method implementation
     *
     * @return array
     */
    public function catalogues()
    {
        return $this->_sendGetRequest('catalogues');
    }

    /**
     * Call of Superjob API's catalogues/:id method implementation
     *
     * @param int $id
     * @return array
     */
    public function catalogue($id)
    {
        return $this->_sendGetRequest('catalogues/'.(int)$id);
    }


    /**
     * Call of Superjob API's catalogues/parent/:id method implementation
     *
     * @param int $id
     * @return array
     */
    public function cataloguesByParent($id)
    {
        return $this->_sendGetRequest('catalogues/parent/'.(int)$id);
    }

    /**
     * Call of Superjob API's client/:id method implementation
     *
     * @param int $id - ID of client
     * @param array $data
     * @return array
     */
    public function client($id, array $data = array())
    {
        return $this->_sendGetRequest('clients/'.$id, $data);
    }

    /**
     * Call of Superjob API's clients method implementation
     *
     * @param array $data
     * @return array
     */
    public function clients(array $data = array())
    {
        return $this->_sendGetRequest('clients', $data);
    }

    /**
     * Call of Superjob API's countries method implementation
     *
     * @param array $data
     * @return array
     */
    public function countries(array $data = array())
    {
        return $this->_sendGetRequest('countries', $data);
    }

    /**
     * Call of Superjob API's user/current method implementation
     *
     * @param string $access_token
     * @param string $app_key - Secret key of your app. Used for employer's API
     * @return array
     */
    public function current_user($access_token, $app_key = null)
    {
        return $this->_sendGetRequest(($app_key ? rawurlencode($app_key).'/' : '').'user/current', array(), $access_token);
    }

    /**
     * Call of Superjob API's forgot_password method implementation
     *
     * @param array $data
     * @return array
     */
    public function forgot_password($data = array())
    {
        return $this->_sendPostRequest('forgot_password', $data);
    }

    /**
     * Call of Superjob API's institutes method implementation
     *
     * @param array $data
     * @return array
     */
    public function institutes($data = array())
    {
        return $this->_sendGetRequest('institutes', $data);
    }

    /**
     * Call of Superjob API's towns method implementation
     *
     * @param array $data
     * @return array
     */
    public function towns($data = array())
    {
        return $this->_sendGetRequest('towns', $data);
    }

    /**
     * Call of Superjob API's regions method implementation
     *
     * @param array $data
     * @return array
     */
    public function regions($data = array())
    {
        return $this->_sendGetRequest('regions', $data);
    }

    /**
     * Call of Superjob API's resumes/received/ method implementation
     *
     * @param string $app_key
     * @param string $access_token
     * @param $params
     * @return array
     */
    public function received_resumes($app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes/received', $params, $access_token, 'GET');
    }

    /**
     * Call of Superjob API's resumes/received/:id method implementation
     *
     * @param int $id - ID of vacancy
     * @param string $app_key
     * @param string $access_token
     * @param $params
     * @return array
     */
    public function received_resumes_on_vacancy($id, $app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes/received/'.$id, $params, $access_token, 'GET');
    }

    /**
     * Call of Superjob API's resumes/:id method implementation
     *
     * @param int $id - ID of cv
     * @param string $app_key
     * @param string $access_token
     * @param array $params
     * @return array
     */
    public function resume($id, $app_key, $params = array(), $access_token = null)
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes/'.$id, $params, $access_token,'GET');
    }

    /**
     * Call of Superjob API's resumes method implementation
     *
     * @param string $app_key
     * @param array $params - search parameters
     * @param string $access_token
     * @return array
     */
    public function resumes($app_key, $params = array(), $access_token = null)
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes', $params, $access_token, 'GET');
    }

    /**
     * Create cv implementation
     *
     * @param string $app_key
     * @param string $access_token
     * @param $params
     * @return array
     */
    public function create_resume($app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes', $params, $access_token, 'POST');
    }


    /**
     * Update cv implementation
     *
     * @param int $id - ID of cv
     * @param string $app_key
     * @param string $access_token
     * @param $params - update data
     * @return array
     */
    public function update_resume($id, $app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes/'.$id.'/', $params, $access_token, 'PUT');
    }


    /**
     * Delete cv implementation
     *
     * @param int $id - ID of cv
     * @param string $app_key
     * @param string $access_token
     * @return void
     */
    public function delete_resume($id, $app_key, $access_token)
    {
        $this->customQuery(rawurlencode($app_key).'/resumes/'.$id.'/', array(), $access_token, 'DELETE');
    }

    /**
     * Call of Superjob API's send_cv_on_vacancy method implementation
     *
     * @param array $data
     * @param string $access_token
     * @return array
     */
    public function send_cv_on_vacancy($data = array(), $access_token)
    {
        return $this->_sendPostRequest('send_cv_on_vacancy', $data, $access_token);
    }

    /**
     * Call of Superjob API's user_cvs method implementation
     *
     * @param string $access_token
     * @return array
     */
    public function user_cvs($access_token)
    {
        return $this->_sendGetRequest('user_cvs', array(), $access_token);
    }


    /**
     * Call of Superjob API's user/list method implementation
     *
     * @param string $app_key - Secret key of your app. Used for employer's API
     * @param string $access_token

     * @return array
     */
    public function user_list($app_key, $access_token)
    {
        return $this->_sendGetRequest(rawurlencode($app_key).'/user/list', array(), $access_token);
    }

    /**
     * Call of Superjob API's vacancies method implementation
     *
     * @param string $app_key	 
     * @param array $data
     * @param string $access_token
     * @return array
     */
    public function vacancies($app_key, array $data = array(), $access_token = null)
    {
		assert(is_string($app_key));
        return $this->_sendGetRequest(rawurlencode($app_key).'/vacancies', $data, $access_token);
    }

    /**
     * Call of Superjob API's vacancies/:id method implementation
     *
     * @param int $id - ID of vacancy
     * @param string $app_key	 
     * @param array $data
     * @param string $access_token
     * @return array
     */
    public function vacancy($id, $app_key, $data = array(), $access_token = null)
    {
        return $this->_sendGetRequest(rawurlencode($app_key).'/vacancies/'.$id, $data, $access_token);
    }
	
    /**
     * Create vacancy implementation
     *
     * @param string $app_key
     * @param string $access_token
     * @param $params
     * @return array
     */
    public function create_vacancy($app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/vacancies', $params, $access_token, 'POST');
    }


    /**
     * Update vacancy implementation
     *
     * @param int $id - ID of vacancy
     * @param string $app_key
     * @param string $access_token
     * @param $params - update data
     * @return array
     */
    public function update_vacancy($id, $app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/vacancies/'.$id.'/', $params, $access_token, 'PUT');
    }


    /**
     * Delete vacancy implementation
     *
     * @param int $id - ID of vacancy
     * @param string $app_key
     * @param string $access_token
     * @return void
     */
    public function delete_vacancy($id, $app_key, $access_token)
    {
        $this->customQuery(rawurlencode($app_key).'/vacancies/'.$id.'/', array(), $access_token, 'DELETE');
    }	

    /**
     * Sets the length of time (in seconds) to wait for a response from Superjob before timing out.
     *
     * Provides a fluent interface.
     *
     * @param integer $timeout Length of time (in seconds) before timeout
     *
     * @return SuperjobAPI
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
     * Sets a parallel mode. It means that all requests will be sent 
	 * to a server in one request
     *
     * @return void
     */	
	public function setParallelMode()
	{
		$this->_parallel = true;
	}
	
	/**
	*	Executes a stack of requests
	**/
	public function executeParallel()
	{
		$this->_parallel = false;
		$res = $this->parallelResults($this->customQuery('parallel', $this->_parallel_data, null, 'POST', true));
		$this->_parallel_data = array();

		return $res;
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
     * Tells wether the last request successfull or not
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
     * @param string $access_token
     * @param string $method Specifies the HTTP method to be used for this request
     * @param bool $no_processing - Do not put the API's answer through json_decode() function
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

	/**
	*	Parse received parallel data
	*	@param array $data - received data from /parallel method
	**/
    public function parallelResults($data)
    {
        $mas = explode("\n", $data);
        foreach ($mas as $k => $v)
        {
            if ($this->_data = json_decode($v, !$this->_object_output))
			{
				$mas[$k] = $this->_data;
			}

            if ($error = $this->lastError())
            {
                $this->_throwException($error);
            }
			elseif (is_null($this->_data))
			{
				$this->_throwException($mas[$k]);
			}
        }

        return $mas;
    }

    /**
     * Sends the GET request to API
     *
     * @param string $name - API Method
     * @param array $data - API Method's parameters
     * @param string $access_token
     * @return mixed
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
     * Sends POST request to API
     *
     * @param string $name - API Method
     * @param array $data - API Method's parameters
     * @param string $access_token
     * @return mixed
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
     * Sends an HTTP request to the Superjob API
     *
     * @param string  $url    Target URI for this request (relative to the API root)
     * @param string  $method Specifies the HTTP method to be used for this request
     * @param mixed   $data   x-www-form-urlencoded data (or array) to be sent in a POST request body
     * @param bool $no_processing - Do not make json decoding of acquired results
     *
     * @return array|null
     * @throws SuperjobAPIException
     */
    protected function _sendRequest($url, $method = 'GET', $data = '', $no_processing = false)
    {
		// parallel mode collects data to be processed in future
		if ($this->_parallel && ($method === 'GET' || $method === 'POST'))
		{
			$this->_parallel_data[] = array($url => $data);
			return;
		}
		
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ($ch, CURLOPT_HEADER, true);

        if ('POST' === ($method = strtoupper($method)))
        {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        elseif ('GET' !== $method)
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
     * @param string $url
     * @param string $params
     * @return string
     */
    protected function _buildUrl($url, $params = '')
    {
        return (stripos($url, self::API_URI) === false)
            ? 'https://'.self::API_URI.$url.'/'.$params
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
     * @param string $client_id		- apps id
     * @param string $client_secret - apps' secret key
     *
     * @return array
     */
    public function refreshAccessToken($refresh_token, $client_id, $client_secret)
    {
        $data = compact('refresh_token', 'client_id', 'client_secret');

        return $this->_sendGetRequest(self::OAUTH_URL.'refresh_token' , $data);
    }

    /**
     * Acquires the Access Token
     *
     * @param string $code			- code GET-parameter
     * @param string $redirect_uri
     * @param string $client_id		- apps id
     * @param string $client_secret - apps' secret key
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
	 * @param string $state
     */
    public function redirectToAuthorizePage($client_id, $return_uri, $state = null)
    {
        $auth_url = self::OAUTH_AUTHORIZE_URL.'?client_id='.$client_id.
			'&redirect_uri='.urlencode($return_uri).
			(!empty($state) ? '&state='.urlencode($state) : '');

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