<?php

class SuperjobAPI
{
    const API_URI = 'https://api.superjob.ru/2.0/';
    const OAUTH_URL = 'https://api.superjob.ru/2.0/oauth2/';

    const OAUTH_AUTHORIZE_URL = 'http://www.superjob.ru/authorize';

    /**
     * {@link setTimeout()}
     *
     * @var integer
     */
    protected $_timeout = 20;

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
     * @var string
     */
    protected $_filename;

    /**
     * {@link setDebugMode()}
     *
     * @var bool
     */
    protected $_debug = false;

    protected $_no_debug_output = false;
	
    /**
     * Parallel storage
     *
     * @var array
     */	
	protected $_parallel_data = array();

    /**
     * Режим повторных запросов при засадах
     * @var bool
     */
    protected $_fallback = false;
	
	public $replace_domain = false;

    /**
     * Turn off json-processing
     * @var bool
     */
    protected $_no_processing = false;

    const DEFAULT_HEADER = 'Cache-Control:max-age=0';
    /**
     * Headers
     * @var array
     */
    protected $_headers = array(self::DEFAULT_HEADER);

    /**
     * {@link setUserAgent()}
     * @var bool|string
     */
    protected $_user_agent = false;

    protected $_no_exceptions = false;
	
    /**
     * {@link setLocal()}
     *
     * @var bool
     */
    protected $_local = false;

	protected $_secret_key;
	protected $_access_token;
	
    public function __construct($timeout = null)
    {
        if ($timeout)
        {
            $this->setTimeout($timeout);
        }

        if (!empty($_SERVER['HTTP_USER_AGENT']))
        {
            $this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }
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

	public function setSecretKey($val)
	{
		$this->_secret_key = $val;
		$this->setSecretKeyHeader();
	}

	public function setAccessToken($val)
	{
		$this->_access_token = $val;
		$this->setAuthorizationHeader();
	}

	protected function setAuthorizationHeader()
	{
		if (!$this->isHeaderSet('Authorization') && !empty($this->_access_token))
		{
			$this->addHeader('Authorization: Bearer '.$this->_access_token);
		}
	}

	protected function setSecretKeyHeader()
	{
		if (!empty($this->_secret_key))
		{
			$this->removeHeader('X-Api-App-Id')->addHeader('X-Api-App-Id: '.$this->_secret_key);
		}
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
     * @param string $keyword
     * @param array $data
     * @return array
     */
    public function countries($keyword = '', $data = array())
    {
		if (!empty($keyword))
		{
			$data['keyword'] = $keyword;
		}
        return $this->_sendGetRequest('countries', $data);
    }

    /**
     * Call of Superjob API's user/current method implementation
     *
     * @return array
     */
    public function current_user()
    {
        return $this->_sendGetRequest('user/current');
    }


	/**
	 * Call of Superjob API's favorites method implementation
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function favorites($data = array())
    {
        return $this->_sendGetRequest('favorites', $data);
    }
	
	

	/**
	 * Adds the vacancy to the favorites
	 *
	 * @param $id - Id of vacancy
	 * @return array
	 */
	public function add_favorite($id)
    {
        return $this->_sendPostRequest('favorites/'.(int)$id, array());
    }
	

	/**
	 * Deletes the vacancy from the favorites
	 *
	 * @param $id - ID of the vacancy
	 */
	public function delete_favorite($id)
    {
		$this->customQuery('favorites/'.(int)$id, array(), 'DELETE');
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
	 * Call of Superjob API's hr/subscriptions/:id method implementation
	 * 
	 * @param $id
	 * @param array $params
	 * @return array
	 */
	public function hr_subscription($id, $params = array())
    {
        return $this->customQuery('hr/subscriptions/'.(int)$id, $params, 'GET');
    }


	/**
	 * Call of Superjob API's hr/subscriptions method implementation
	 * 
	 * @param array $params
	 * @return array
	 */
	public function hr_subscriptions($params = array())
    {
        return $this->customQuery('hr/subscriptions', $params, 'GET');
    }

	/**
	 * Create HR subscription implementation
	 * 
	 * @param array $data
	 * @return array
	 */
	public function create_hr_subscription($data = array())
    {
        return $this->customQuery('hr/subscriptions', $data, 'POST');
    }


	/**
	 * Update HR subscription implementation
	 * 
	 * @param $id
	 * @param array $data
	 * @return array
	 */
	public function update_hr_subscription($id, $data = array())
    {
        assert(is_numeric($id));
        return $this->customQuery('hr/subscriptions/'.$id, $data, 'PUT');
    }


	/**
	 * Delete HR subscription implementation
	 * 
	 * @param $id
	 * @param array $data
	 * @return array
	 */
	public function delete_hr_subscription($id, $data = array())
    {
        assert(is_numeric($id));
        return $this->customQuery('hr/subscriptions/'.$id, $data, 'DELETE');
    }


	/**
	 * Call of Superjob API's hr/user/:id method implementation
	 * 
	 * @param $id
	 * @param array $data
	 * @return mixed
	 */
	public function hr_user($id, $data = array())
    {
        return $this->_sendGetRequest('hr/user/'.(int)$id, $data);
    }


	/**
	 * Create HR user implementation
	 * 
	 * @param array $data
	 * @return array
	 */
	public function create_hr_user($data = array())
    {
        return $this->customQuery('hr/user', $data, 'POST');
    }


	/**
	 * Update HR user implementation
	 * 
	 * @param $id
	 * @param array $data
	 * @return array
	 */
	public function update_hr_user($id, $data = array())
    {
        assert(is_numeric($id));
        return $this->customQuery('hr/user/'.$id, $data, 'PUT');
    }

	/**
	 * Delete HR user implementation
	 * 
	 * @param $id
	 * @param array $data
	 * @return array|void
	 */
	public function delete_hr_user($id, $data = array())
    {
        assert(is_numeric($id));
        return $this->customQuery('hr/user/'.$id, $data, 'DELETE');
    }
	

	/**
	 * Call of Superjob API's messages/:id method implementation
	 * 
	 * @param $id
	 * @param array $data
	 * @return mixed
	 */
	public function messages_on_resume($id, $data = array())
    {
        return $this->_sendGetRequest('messages/'.(int)$id, $data);
    }

	/**
	 * Call of Superjob API's messages method implementation
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function messages($data = array())
    {
        return $this->_sendGetRequest('messages', $data);
    }


	/**
	 * Call of Superjob API's messages/list method implementation
	 *
	 * @param array $data
	 * @return array
	 */
	public function messages_list($data = array())
    {
        return $this->_sendGetRequest('messages/list', $data);
    }
	

	/**
	 * Call of Superjob API's messages/history/all method implementation
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function messages_history($data = array())
    {
        return $this->_sendGetRequest('messages/history/all', $data);
    }	
	

	/**
	 * Call of Superjob API's messages/history/all/:id method implementation
	 *
	 * @param $id
	 * @param array $data
	 * @return mixed
	 */
	public function messages_history_of_resume($id, $data = array())
    {
        return $this->_sendGetRequest('messages/history/all/'.(int)$id, $data);
    }
	
    /**
     * Call of Superjob API's metro/:id_town/lines method implementation
     *
	 * @param int $id_town
     * @return array
     */
    public function metro_lines($id_town)
    {
        return $this->_sendGetRequest('metro/'.(int)$id_town.'/lines');
    }		

    /**
     * Call of Superjob API's towns method implementation
     *
     * @param string $keyword	
	 * @param bool $all			- show all list of towns (no pages)
	 * @param bool $genitive	- show additional data
     * @param array $data
     * @return array
     */
    public function towns($keyword = '', $all = false, $genitive = false, $data = array())
    {
		if (!empty($keyword))
		{
			$data['keyword'] = $keyword;
		}
		if (!empty($all))
		{
			$data['all'] = $all;
		}
		if (!empty($genitive))
		{
			$data['genitive'] = $genitive;
		}
        return $this->_sendGetRequest('towns', $data);
    }

    /**
     * Call of Superjob API's towns/geoip/ method implementation
     *
     * @param string $ip
     * @param bool $genitive	- show additional data
     * @param array $data
     * @return array
     */
    public function town_by_ip($ip, $genitive = false, $data = array())
    {
        $data['ip'] = $ip;
        if (!empty($all))
        {
            $data['all'] = $all;
        }
        if (!empty($genitive))
        {
            $data['genitive'] = $genitive;
        }
        return $this->_sendGetRequest('towns/geoip', $data);
    }

    /**
     * Call of Superjob API's regions method implementation
     *
     * @param string $keyword	
	 * @param bool $all			- show all list of regions (no pages)
     * @param array $data
     * @return array
     */
    public function regions($keyword = '', $all = false, $data = array())
    {
		if (!empty($keyword))
		{
			$data['keyword'] = $keyword;
		}
		if (!empty($all))
		{
			$data['all'] = $all;
		}	
        return $this->_sendGetRequest('regions', $data);
    }


	/**
	 * Call of Superjob API's resumes/received/ method implementation
	 *
	 * @param array $params
	 * @return array
	 */
	public function received_resumes($params = array())
    {
        return $this->customQuery('resumes/received', $params, 'GET');
    }


	/**
	 * Call of Superjob API's resumes/received/:id method implementation
	 *
	 * @param $id - ID of vacancy
	 * @param array $params
	 * @return array
	 */
	public function received_resumes_on_vacancy($id, $params = array())
    {
        return $this->customQuery('resumes/received/'.$id, $params, 'GET');
    }


	/**
	 * Call of Superjob API's resumes/:id method implementation
	 *
	 * @param $id - ID of cv
	 * @param array $params
	 * @return array
	 */
	public function resume($id, $params = array())
    {
        return $this->customQuery('resumes/'.$id, $params,'GET');
    }

    /**
     * Call of Superjob API's resumes method implementation
     *
     * @param array $params - search parameters
     * @return array
     */
    public function resumes($params = array())
    {
        return $this->customQuery('resumes', $params, 'GET');
    }
	

	/**
	 * Call of Superjob API's resumes/:id/copy method implementation
	 *
	 * @param $id - ID of cv
	 * @param array $params - search parameters
	 * @return array
	 */
	public function copy_resume($id, $params = array())
    {
        return $this->customQuery('resumes/'.(int)$id.'/copy', $params, 'GET');
    }
	

	/**
	 * Call of Superjob API's resumes/:id/upload method implementation
	 *
	 * @param $id - ID of cv
	 * @param $file - path to file (can be taken from $_FILES['file_name']['tmp_name'])
	 * @param array $data
	 * @return array
	 */
	public function upload_photo_to_resume($id, $file, $data = array())
    {
		$this->_filename = $file;
        return $this->customQuery('resumes/'.(int)$id.'/upload', $data, 'FILE');
    }	


	/**
	 * Create cv implementation
	 *
	 * @param array $params
	 * @return array
	 */
	public function create_resume($params = array())
    {
        return $this->customQuery('resumes', $params, 'POST');
    }



	/**
	 * Update cv implementation
	 *
	 * @param $id - ID of cv
	 * @param array $params - update data
	 * @return array
	 */
	public function update_resume($id, $params = array())
    {
        return $this->customQuery('resumes/'.$id, $params, 'PUT');
    }



	/**
	 * Delete cv implementation
	 *
	 * @param $id - ID of cv
	 * @param array $params
	 * @return void
	 */
	public function delete_resume($id, $params = array())
    {
        $this->customQuery('resumes/'.$id, $params, 'DELETE');
    }
	

	/**
	 * Call of Superjob API's user_cvs/update_datepub/:id/ method implementation
	 *
	 * @param $id
	 * @return array
	 */
	public function update_resume_date_published($id)
    {
        return $this->_sendPostRequest('user_cvs/update_datepub/'.(int)$id, array());
    }
	

	/**
	 * Call of Superjob API's resumes/:id/views/ method implementation
	 *
	 * @param $id - ID of cv
	 * @param array $data
	 * @return array
	 */
	public function resume_views($id, $data = array())
    {
        return $this->_sendGetRequest('resumes/'.$id.'/views/', $data);
    }		


	/**
	 * Call of Superjob API's send_cv_on_vacancy method implementation
	 *
	 * @param $id_cv		    - ID of cv
	 * @param $id_vacancy	    - ID of vacancy
	 * @param string $comment	- text message
	 * @param array $data 		- may contain auth or other tech data
	 * @return mixed
	 */
	public function send_cv_on_vacancy($id_cv, $id_vacancy, $comment = '', $data = array())
    {
        return $this->_sendPostRequest('send_cv_on_vacancy', array_merge($data, array('id_cv' => (int)$id_cv, 'id_vacancy' => $id_vacancy, 'comment' => $comment)));
    }

    /**
     * Call of Superjob API's user_cvs method implementation
     *
     * @return array
     */
    public function user_cvs()
    {
        return $this->_sendGetRequest('user_cvs', array());
    }


    /**
     * Call of Superjob API's user/list method implementation
     *
     * @return array
     */
    public function user_list()
    {
        return $this->_sendGetRequest('user/list', array());
    }

	/**
	 * Call of Superjob API's vacancies method implementation
	 * 
	 * @param array $data
	 * @return mixed|array
	 */
	public function vacancies(array $data = array())
    {
        return $this->_sendGetRequest('vacancies', $data);
    }

	/**
	 * Call of Superjob API's vacancies/:id method implementation
	 * 
	 * @param $id - ID of vacancy
	 * @param array $data
	 * @return array
	 */
	public function vacancy($id, $data = array())
    {
        return $this->_sendGetRequest('vacancies/'.$id, $data);
    }

	
	/**
	 * vacancies/:id/archive implementation
	 * 
	 * @param $id - ID of vacancy
	 * @param array $params
	 * @return array
	 */
	public function archive_vacancy($id, $params = array())
    {
        return $this->customQuery('vacancies/'.(int)$id.'/archive', $params, 'PUT');
    }


	/**
	 * vacancies/archive implementation
	 * 
	 * @param array $vacancies - Vacancies to republish. Each item of it is vacancy array
	 * @return array
	 */
	public function archive_vacancies($vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery('vacancies/archive', $params, 'PUT');
    }
	

	/**
	 * vacancies/:id/republish implementation
	 *
	 * @param $id - ID of vacancy
	 * @param array $params
	 * @return array
	 */
	public function republish_vacancy($id, $params = array())
    {
        return $this->customQuery('vacancies/'.(int)$id.'/republish', $params, 'PUT');
    }


	/**
	 * vacancies/republish implementation
	 *
	 * @param array $vacancies - Vacancies to republish. Each item of it is vacancy array
	 * @return array
	 */
	public function republish_vacancies($vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery('vacancies/republish', $params, 'PUT');
    }


	/**
	 * Create vacancy implementation
	 *
	 * @param array $params
	 * @return array
	 */
	public function create_vacancy($params = array())
    {
        return $this->customQuery('vacancies', $params, 'POST');
    }


	/**
	 * Create vacancies implementation
	 *
	 * @param array $vacancies - Vacancies to create. Each item of it is vacancy array
	 * @return array
	 */
	public function create_vacancies($vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery('vacancies', $params, 'POST');
    }


	/**
	 * Update vacancy implementation
	 *
	 * @param $id - ID of vacancy
	 * @param array $params - update data
	 * @return array
	 */
	public function update_vacancy($id, $params = array())
    {
        return $this->customQuery('vacancies/'.$id, $params, 'PUT');
    }


	/**
	 * Update vacancies implementation
	 *
	 * @param array $vacancies  - Vacancies to update. Each item of it is vacancy array
	 * @return array
	 */
	public function update_vacancies($vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery('vacancies', $params, 'PUT');
    }


	/**
	 * Delete vacancy implementation
	 *
	 * @param $id - ID of vacancy
	 * @param array $params
	 * @return void|mixed
	 */
	public function delete_vacancy($id, $params = array())
    {
        return $this->customQuery('vacancies/'.$id, $params, 'DELETE');
    }


	/**
	 * Delete vacancies implementation
	 *
	 * @param array $vacancies - Vacancies to delete. Each item of it is vacancy array
	 * @return array
	 */
	public function delete_vacancies($vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery('vacancies', $params, 'DELETE');
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
     * Debug mode
     *
     * @param $debug
     * @return void
     */
    public function setDebugMode($debug = true)
    {
        $this->_debug = $debug;
    }

    public function setNoDebugOutput($val = true)
    {
        $this->_no_debug_output = $val;
    }

    public function setNoExceptions($val = true)
    {
        $this->_no_exceptions = $val;
    }

    /**
     * Sets UA to be used in request to API
     * @param string $val
     */
    public function setUserAgent($val)
    {
        $this->_user_agent = $val;
    }
	
    public function localOn()
    {
		$this->_local = true;
		return $this;
    }

    public function localOff()
    {
		$this->_local = false;
		return $this;
    }

	/**
	*	Adds header to http-request
	*	@param string $header
	*	@return void
	**/
    public function addHeader($header)
    {
        $this->_headers[] = $header;
    }

    /**
     *	Reset headers to default value
     *	@return void
     **/
    public function resetHeaders()
    {
        $this->_headers = array(self::DEFAULT_HEADER);
    }


	/**
	 * Check if header key already set
	 * @param string $header_key e.g. 'Cache-Control'
	 * @return bool
	 */
	public function isHeaderSet($header_key)
	{
		foreach($this->_headers as $header)
		{
			if(mb_strstr($header, $header_key) !== false)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Removes header
	 * @param string $header_key
	 * @return SuperjobAPI
	 */
	public function removeHeader($header_key)
	{
		foreach($this->_headers as $k => $header)
		{
			if(mb_strstr($header, $header_key) !== false)
			{
				unset($this->_headers[$k]);
				break;
			}
		}
		return $this;
	}

	/**
	*	Устанавливаем режим fallback
	* 	@param bool $val
	*	@return void
	**/
    public function setFallback($val)
    {
        $this->_fallback = (bool)$val;
    }

    /**
     * @param $val
     * @return SuperjobAPI
     */
    public function setProcessing($val)
    {
        $this->_no_processing = !(bool)$val;
        return $this;
    }

    /**
     * Returns all data as an objects
     *
     * @param $mode
     * @return void
     */
    public function setObjectOutput($mode = true)
    {
        $this->_object_output = $mode;
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
		$res = $this->parallelResults($this->customQuery('parallel', $this->_parallel_data, 'POST', true));
		$this->_parallel_data = array();

		return $res;
	}	
	
    /**
     * Whether the last request was successful or not
     *
     * @return bool
     */
    public function hasError()
    {
        return strpos((string)$this->_http_code, '2') !== 0;
    }

    /**
     * Returns an error's text description
     *
     * @return bool|mixed
     */
    public function lastError()
    {
        if ($this->_object_output)
        {
            if (!empty($this->_data) && !empty($this->_data->error->message))
            {
                if (is_scalar($this->_data->error->message))
                {
                    return (array)$this->_data->error;
                }
                $error = current($this->_data->error->message);
                return $error->description;
            }

        }
        elseif((!empty($this->_data) && !empty($this->_data['error']['message'])))
        {
            if (is_scalar($this->_data['error']['message']))
            {
                return $this->_data['error'];
            }
            $error =  current($this->_data['error']['message']);
            return $error['description'];
        }
        return false;
    }

    /**
     * Sends custom request to API
     *
     * @param string $name - API Method
     * @param array $data - API Method's parameters
     * @param string $access_token
     * @param string $method Specifies the HTTP method to be used for this request
     * @param bool $no_processing - Do not put the API answer through json_decode() function
     * @return array
     */
    public function customQuery($name, $data = array(), $method = 'GET', $no_processing = false)
    {
        $url = ($method === 'GET' || $method === 'FILE') ? $this->_buildUrl($name, $this->_buildQueryString($data)) : $this->_buildUrl($name);

        $url = (!empty($access_token))
            ? $this->_signRequest($url, $access_token)
            : $url;

        return $this->_sendRequest($url, $method, $method !== 'GET' ? $data : '', $no_processing);
    }

	/**
	*	Parse received parallel data
	*	@param array $data - received data from /parallel method
	*	@return array
	**/
    public function parallelResults($data)
    {
        $mas = explode("\r\n", $data);

        foreach ($mas as $k => $v)
        {
            if ($this->_data = json_decode($v, !$this->_object_output))
			{
				$mas[$k] = $this->_data;
			}

            if ($error = $this->lastError())
            {
                $this->_throwException($error['message'], $error['code']);
            }
			elseif (is_null($this->_data))
			{
				$this->_throwException('Data was empty or corrupted: "'.$v.'"');
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
     * @param int $count - Fallback count
     *
     * @return array|null|string
     * @throws SuperjobAPIException|UnexpectedValueException
     */
    protected function _sendRequest($url, $method = 'GET', $data = '', $no_processing = false, $count = 0)
    {
        if ($this->replace_domain)
        {
            $replace_domain = str_replace(array('https://', 'http://'), '', $this->replace_domain);
            $url = str_replace('https://api.superjob.ru',
                (stripos($this->replace_domain , 'api.superjob.') !== false
                    ? 'https://'.$replace_domain
                    : 'http://'.$replace_domain
                ), $url
            );
        }
		else
		{
			$replace_domain = 'api.superjob.ru';
		}
		
		if($this->_local)
		{
			$url = str_replace(
				$replace_domain, '127.0.0.1', str_replace(
					'https://', 'http://', $url
				)
			);

			if(!in_array('Host:'.$replace_domain, $this->_headers))
			{
				$this->_headers []= 'Host:'.$replace_domain;
			}
		}

		// parallel mode collects data to be processed in future
		if ($this->_parallel && ($method === 'GET' || $method === 'POST'))
		{
			$this->_parallel_data[] = array($url => $data);
			return null;
		}

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

	    // Если есть авторизация, а заголовки очистили
	    $this->setAuthorizationHeader();
	    $this->setSecretKeyHeader();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
		//curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if ($this->_user_agent)
        {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->_user_agent);
        }

        if ('GET' !== ($method = strtoupper($method)) && ($method !== 'FILE'))
        {
            if ($method === 'POST')
            {
                curl_setopt($ch, CURLOPT_POST, TRUE);
            }
            else
            {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        elseif ('GET' !== $method && 'FILE' !== $method)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        // for /upload method
		if ($method === 'FILE' && !empty($this->_filename))
		{
			if ($fp = fopen($this->_filename, 'r'))
            {
                curl_setopt($ch, CURLOPT_POST,  1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $data = array( );
                $data['file'] = curl_file_create($this->_filename, mime_content_type($this->_filename), 'file');

                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            else
            {
                $this->_throwException('Can not load file '.$this->_filename);
            }
		}

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);

        $verbose = false;
        if ($this->_debug && !$this->_no_debug_output)
        {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_FILETIME, true);
            curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+'));
        }

        $resp = curl_exec($ch);

        if ($this->_debug && !$this->_no_debug_output)
        {
            $s = !rewind($verbose). stream_get_contents($verbose). "\n". $resp;
            if ($data && is_array($data))
            {
                $input_data = "\n".http_build_query($data)."\n\n";
                if (stripos($s, '< HTTP/1.1') !== false)
                {
                    $s = str_replace('< HTTP/1.1', $input_data.'< HTTP/1.1', $s);
                }
                else
                {
                    $s.= $input_data;
                }
            }
            if (curl_error($ch))
            {
                $s.= "\n".curl_error($ch);
            }
            echo $s;
        }

		$curl_info = curl_getinfo($ch);
        $this->_http_code = $curl_info['http_code'];

		if ($curl_info['connect_time'] >= 1)
		{
			$this->saveConnectDescription($curl_info['connect_time'], $curl_info['url']);
		}

        if ($resp === false && $this->_http_code != 200)
        {
			// В режиме fallback делаем ещё один запрос при ошибке
            if ($this->_fallback && $count == 0)
            {
                sleep(1);
                return $this->_sendRequest($url, $method, $data, $no_processing, ++$count);
            }
            $this->_throwException(curl_error($ch));
        }

        curl_close($ch);

        if (!empty($resp) && ($no_processing === false  && $this->_no_processing === false))
        {
            $response = json_decode($resp, !$this->_object_output);
            if ($response !== NULL)
            {
                $this->_data = $response;
                // If it is an error - let's there be an exception
                if ($error = $this->lastError())
                {
					$this->_throwException($error['message'], $error['code']);
				}
				$resp = $response;
			}
			else
			{
                // В режиме fallback делаем ещё один запрос при ошибке
                if ($this->_fallback && $count == 0)
                {
                    sleep(1);
                    return $this->_sendRequest($url, $method, $data, $no_processing, ++$count);
                }

				return false;
			}

        }
        return $resp;
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
        $res = (stripos($url, self::API_URI) === false) && ((stripos($url, 'http://') === false) && (stripos($url, 'https://') === false))
            ? self::API_URI.$url
            : $url;

        if (empty($params))
        {
            return $res.((stripos($res, '?') === false) ? '/' : '');
        }

        return $res.(((stripos($res, '?') === false)) ? '/'.$params : '&'.$params);
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
     * @param string $message Message to be provided with the exception
     * @param int $code
     * @throws SuperjobAPIException
     */
    protected function _throwException($message, $code = 500)
    {
        if (!$this->_no_exceptions)
            throw new SuperjobAPIException($message, $code);
    }

    /**
     * @param $login
     * @param $password
     * @param $client_id
     * @param $client_secret
     * @return mixed
     */
    public function getAccessTokenByPassword($login, $password, $client_id, $client_secret)
    {
        return $this->_sendGetRequest(self::OAUTH_URL.'password' , compact('login', 'password', 'client_id', 'client_secret'));
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

if (!function_exists('curl_file_create'))
{
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
        . ($postname ?: basename($filename))
        . ($mimetype ? ";type=$mimetype" : '');
    }
}

if (!function_exists('mime_content_type'))
{
    function mime_content_type($filename)
    {

        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types))
        {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else
        {
            return 'application/octet-stream';
        }
    }
}
