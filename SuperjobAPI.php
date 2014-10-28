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
	
    /**
     * Parallel storage
     *
     * @var array
     */	
	protected $_parallel_data = array();
	
	public $replace_domain = false;

    /**
     * Headers
     * @var array
     */
    protected $_headers = array('Cache-Control:max-age=0');
	
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
     * @param string $access_token
     * @param string $app_key - Secret key of your app. Used for employer's API
     * @return array
     */
    public function current_user($access_token, $app_key = null)
    {
        return $this->_sendGetRequest(($app_key ? rawurlencode($app_key).'/' : '').'user/current', array(), $access_token);
    }

	
    /**
     * Call of Superjob API's favorites method implementation
     *
     * @param string $access_token
	 * @param array $data
     * @return array
     */
    public function favorites($access_token, $data = array())
    {
        return $this->_sendGetRequest('favorites', $data, $access_token);
    }
	
	
    /**
     * Adds the vacancy to the favorites
     *
     * @param int $id - Id of vacancy
	 * @param string $access_token
     * @return array
     */
    public function add_favorite($id, $access_token)
    {
        return $this->_sendPostRequest('favorites/'.(int)$id, array(), $access_token);
    }
	
    /**
     * Deletes the vacancy from the favorites
     *
     * @param int $id - ID of the vacancy
	 * @param string $access_token
     * @return void
     */
    public function delete_favorite($id, $access_token)
    {
		$this->customQuery('favorites/'.(int)$id, array(), $access_token, 'DELETE');
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
     * Call of Superjob API's hr/user/:id method implementation
     * @param $id
     * @param $app_key
     * @param $access_token
     * @param array $data
     * @return mixed
     */
    public function hr_user($id, $app_key, $access_token, $data = array())
    {
        return $this->_sendGetRequest(rawurlencode($app_key).'/hr/user/'.(int)$id, $data, $access_token);
    }

    /**
     * Create HR user implementation
     * @param $app_key
     * @param $access_token
     * @param array $data
     * @return array
     */
    public function create_hr_user($app_key, $access_token, $data = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/hr/user', $data, $access_token, 'POST');
    }

    /**
     * Update HR user implementation
     *
     * @param $id
     * @param $app_key
     * @param $access_token
     * @param array $data
     * @return array
     */
    public function update_hr_user($id, $app_key, $access_token, $data = array())
    {
        assert(is_numeric($id));
        return $this->customQuery(rawurlencode($app_key).'/hr/user/'.$id, $data, $access_token, 'PUT');
    }

    /**
     * Delete HR user implementation
     *
     * @param $id
     * @param $app_key
     * @param $access_token
     * @param array $data
     * @return array|void
     */
    public function delete_hr_user($id, $app_key, $access_token, $data = array())
    {
        assert(is_numeric($id));
        return $this->customQuery(rawurlencode($app_key).'/hr/user/'.$id, $data, $access_token, 'DELETE');
    }
	
    /**
     * Call of Superjob API's messages/:id method implementation
     *
	 * @param int $id
	 * @param string $access_token
     * @param array $data
     * @return array
     */
    public function messages_on_resume($id, $access_token, $data = array())
    {
        return $this->_sendGetRequest('messages/'.(int)$id, $data, $access_token);
    }
	
    /**
     * Call of Superjob API's messages method implementation
     *
	 * @param string $access_token
     * @param array $data
     * @return array
     */
    public function messages($access_token, $data = array())
    {
        return $this->_sendGetRequest('messages', $data, $access_token);
    }

    /**
     * Call of Superjob API's messages/list method implementation
     *
	 * @param string $access_token
     * @param array $data
     * @return array
     */
    public function messages_list($access_token, $data = array())
    {
        return $this->_sendGetRequest('messages/list', $data, $access_token);
    }
	
    /**
     * Call of Superjob API's messages/history/all method implementation
     *
	 * @param string $access_token
     * @param array $data
     * @return array
     */
    public function messages_history($access_token, $data = array())
    {
        return $this->_sendGetRequest('messages/history/all', $data, $access_token);
    }	
	
    /**
     * Call of Superjob API's messages/history/all/:id method implementation
     *
	 * @param int $id
	 * @param string $access_token
     * @param array $data
     * @return array
     */
    public function messages_history_of_resume($id, $access_token, $data = array())
    {
        return $this->_sendGetRequest('messages/history/all/'.(int)$id, $data, $access_token);
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
     * Call of Superjob API's resumes/:id/copy method implementation
     *
	 * @param int $id - ID of cv
     * @param string $app_key
     * @param array $params - search parameters
     * @param string $access_token
     * @return array
     */
    public function copy_resume($id, $app_key, $params = array(), $access_token)
    {
        return $this->customQuery(rawurlencode($app_key).'/resumes/'.(int)$id.'/copy', $params, $access_token, 'GET');
    }
	
    /**
     * Call of Superjob API's resumes/:id/upload method implementation
     *
	 * @param int $id - ID of cv
     * @param string $app_key
     * @param string $file - path to file (can be taken from $_FILES['file_name']['tmp_name'])
     * @param string $access_token
     * @return array
     */
    public function upload_photo_to_resume($id, $app_key, $file, $access_token)
    {
		$this->_filename = $file;
        return $this->customQuery(rawurlencode($app_key).'/resumes/'.(int)$id.'/upload', array(), $access_token, 'FILE');
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
        return $this->customQuery(rawurlencode($app_key).'/resumes/'.$id, $params, $access_token, 'PUT');
    }


    /**
     * Delete cv implementation
     *
     * @param int $id - ID of cv
     * @param string $app_key
     * @param string $access_token
     * @param array $params
     * @return void
     */
    public function delete_resume($id, $app_key, $access_token, $params = array())
    {
        $this->customQuery(rawurlencode($app_key).'/resumes/'.$id, $params, $access_token, 'DELETE');
    }
	
    /**
     * Call of Superjob API's user_cvs/update_datepub/:id/ method implementation
     *
     * @param int $id - ID of cv
     * @param string $access_token
     * @return array
     */
    public function update_resume_date_published($id, $access_token)
    {
        return $this->_sendPostRequest('user_cvs/update_datepub/'.(int)$id, array(), $access_token);
    }
	
    /**
     * Call of Superjob API's resumes/:id/views/ method implementation
     *
	 * @param int $id - ID of cv
	 * @param string $app_key
	 * @param string $access_token
     * @param array $data
     * @return array
     */
    public function resume_views($id, $app_key, $access_token, $data = array())
    {
        return $this->_sendGetRequest(rawurlencode($app_key).'/resumes/'.$id.'/views/', $data, $access_token);
    }		

    /**
     * Call of Superjob API's send_cv_on_vacancy method implementation
     *
     * @param int $id_cv		- ID of cv
	 * @param int $id_vacancy	- ID of vacancy
	 * @param string $comment	- text message
     * @param string $access_token
     * @return array
     */
    public function send_cv_on_vacancy($id_cv, $id_vacancy, $comment = '', $access_token)
    {
        return $this->_sendPostRequest('send_cv_on_vacancy', array('id_cv' => (int)$id_cv, 'id_vacancy' => $id_vacancy, 'comment' => $comment), $access_token);
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
     * vacancies/:id/archive implementation
     * @param int $id - ID of vacancy
     * @param string $app_key
     * @param string $access_token
     * @param $params
     * @return array
     */
    public function archive_vacancy($id, $app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/vacancies/'.(int)$id.'/archive', $params, $access_token, 'PUT');
    }

    /**
     * vacancies/archive implementation
     * @param string $app_key
     * @param string $access_token
     * @param array $vacancies - Vacancies to republish. Each item of it is vacancy array
     * @return array
     */
    public function archive_vacancies($app_key, $access_token, $vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery(rawurlencode($app_key).'/vacancies/archive', $params, $access_token, 'PUT');
    }
	
    /**
     * vacancies/:id/republish implementation
     * @param int $id - ID of vacancy
     * @param string $app_key
     * @param string $access_token
     * @param $params
     * @return array
     */
    public function republish_vacancy($id, $app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/vacancies/'.(int)$id.'/republish', $params, $access_token, 'PUT');
    }

    /**
     * vacancies/republish implementation
     * @param string $app_key
     * @param string $access_token
     * @param array $vacancies - Vacancies to republish. Each item of it is vacancy array
     * @return array
     */
    public function republish_vacancies($app_key, $access_token, $vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery(rawurlencode($app_key).'/vacancies/republish', $params, $access_token, 'PUT');
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
     * Create vacancies implementation
     *
     * @param string $app_key
     * @param string $access_token
     * @param array $vacancies - Vacancies to create. Each item of it is vacancy array
     * @return array
     */
    public function create_vacancies($app_key, $access_token, $vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
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
        return $this->customQuery(rawurlencode($app_key).'/vacancies/'.$id, $params, $access_token, 'PUT');
    }

    /**
     * Update vacancies implementation
     *
     * @param string $app_key
     * @param string $access_token
     * @param $vacancies  - Vacancies to update. Each item of it is vacancy array
     * @return array
     */
    public function update_vacancies($app_key, $access_token, $vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery(rawurlencode($app_key).'/vacancies', $params, $access_token, 'PUT');
    }


    /**
     * Delete vacancy implementation
     *
     * @param int $id - ID of vacancy
     * @param string $app_key
     * @param string $access_token
     * @param array $params
     * @return void|mixed
     */
    public function delete_vacancy($id, $app_key, $access_token, $params = array())
    {
        return $this->customQuery(rawurlencode($app_key).'/vacancies/'.$id, $params, $access_token, 'DELETE');
    }


    /**
     * Delete vacancies implementation
     *
     * @param string $app_key
     * @param string $access_token
     * @param array $vacancies  - Vacancies to delete. Each item of it is vacancy array
     * @return array
     */
    public function delete_vacancies($app_key, $access_token, $vacancies = array())
    {
        $params = empty($vacancies['vacancies']) ? array('vacancies' => $vacancies) : $vacancies;
        return $this->customQuery(rawurlencode($app_key).'/vacancies', $params, $access_token, 'DELETE');
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
     *
     * @return void
     */
    public function setDebugMode()
    {
        $this->_debug = true;
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
		$res = $this->parallelResults($this->customQuery('parallel', $this->_parallel_data, null, 'POST', true));
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
    public function customQuery($name, $data = array(), $access_token = null, $method = 'GET', $no_processing = false)
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
        $mas = explode("\n", $data);
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
     *
     * @return array|null|string
     * @throws SuperjobAPIException
     */
    protected function _sendRequest($url, $method = 'GET', $data = '', $no_processing = false)
    {
        if ($this->replace_domain)
        {
            $url = str_replace('https://api.superjob.ru',
                (stripos($this->replace_domain , 'api.superjob.') !== false
                    ? 'https://'.$this->replace_domain
                    : 'http://'.$this->replace_domain
                ), $url
            );
        }

		// parallel mode collects data to be processed in future
		if ($this->_parallel && ($method === 'GET' || $method === 'POST'))
		{
			$this->_parallel_data[] = array($url => $data);
			return null;
		}

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);

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
        if ($this->_debug)
        {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_FILETIME, true);
            curl_setopt($ch, CURLOPT_STDERR, $verbose = fopen('php://temp', 'rw+'));
        }

        $resp = curl_exec($ch);

        if ($this->_debug)
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

        $this->_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($resp === false)
        {
            $this->_throwException(curl_error($ch));
        }

        $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);



        curl_close($ch);
        $resp = substr($resp, $size);

        if (!empty($resp) && ($no_processing === false))
        {
            if ($response = json_decode($resp, !$this->_object_output))
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
				$resp = ($this->_debug) ? $resp : false;
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
        return (stripos($url, self::API_URI) === false)
            ? self::API_URI.$url.'/'.$params
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
     * @param string $message Message to be provided with the exception
     * @param int $code
     * @throws SuperjobAPIException
     */
    protected static function _throwException($message, $code = 500)
    {
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

?>