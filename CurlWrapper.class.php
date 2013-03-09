<?php
final class CurlWrapper
{

    // {{{ properties

    /**
     * @var object CurlWrapper instance
     * @access private
     */
    private static $_Instance = NULL;

    /**
     * @var string cUrl instance
     * @access private
     */
    private $_curlInstance = NULL;

    /**
     * @var string GET handler 
     * @access private
     */
    private $_GETHandler;

    /**
     * @var array all curl_opt's handler
     * @access private
     */
    private $_genOptsHandler = array();

    /**
     * @var array all request/response log handler
     * @access private
     */
    private $_logHandler = array();

    /**
     * @var integer 
     * @access private
     */
    private $_reqCounter = 0;

    // }}}
    // {{{ Owerride magic methods, construct, clone

    /**
     * 
     */
    private function __clone()
    {}
   
    private function __construct()
    {
        $this->_curlInstance = curl_init();
        $this->_restoreToFactory();
    }
    
    // }}}

    /**
     * Singleton meth
     */
    public static function getInstance() 
    {
        if (is_null(self::$_Instance)) {
           self::$_Instance = new CurlWrapper(); 
        }
        return self::$_Instance;
    }

    /**
     * Set url to request 
     * @access public
     * @param  string url - decoded url to resource
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setUrl($url ='')
    {
        if (empty($url)) {
            throw new Exception('Empty URL');
        }

        $this->_setOpt(CURLOPT_URL, trim($url));
        return $this;
    }

    /**
     * Set login / pass (IF NEEDED)
     * @access public
     * @param  string login
     * @param  string pass
     * @return CurlWrapper object 
     * @throws Exception
     */
    public function setAuth($login ='', $passwd ='')
    {
        if (empty($login)) {
            throw new Exception(
                'Invalid user/pass pair. Login empty'
            );
        }
        $this->_setOpt(
            CURLOPT_USERPWD,
            "{$login}:{$passwd}"
        );
        return $this;
    }
    
    /**
     * GET container 
     * @access public
     * @param  array getParams key => 'value' 
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setGet($getParams =array())
    {
        if (!is_array($getParams)) {
            throw new Exception(
                'Invalid get params, expecting array'
            );
        }
        $getTail = '';
        foreach($getParams as $param => $value) {
            if (!empty($param)) {
                $getTail .= urlencode($param) 
                          . '=' 
                          . urlencode($value) 
                          . '&';
            }
        }
        if (!empty($getTail)) {
            if (!empty($this->_GETHandler)) {
                $getTail = $this->_GETHandler 
                         . '&' 
                         . $getTail;
            }
            $this->_GETHandler = 
                substr($getTail, 0, strlen($getTail) -1); 
        }
        return $this;
    }

    /**
     * POST container 
     * @access public
     * @param  array postParams key => 'value' 
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setPost($postParams =array())
    {
        if (isset($this->_genOptsHandler[CURLOPT_POSTFIELDS]) &&
            !is_array($this->_genOptsHandler[CURLOPT_POSTFIELDS])
        ) {
            throw new Exception(
                'Please assign the POST parameters one of the methods: setPost()'
                . ' or by setAdditionalOpts(). Do not use both to one request'
            ); 
        }
        if (!is_array($postParams)) {
            throw new Exception(
                'Invalid post params, expecting array'
            ); 
        }

        $postTail = array();
        foreach ($postParams as $param => $value) {
            if (!empty($param)) {
                $postTail[$param] = $value;
            }
        }
        if (!empty($postTail)) {
            if (isset($this->_genOptsHandler[CURLOPT_POSTFIELDS])) {
                $postTail = array_merge(
                    $this->_genOptsHandler[CURLOPT_POSTFIELDS],
                    $postTail
                );
            }
            $this->_setOpt(CURLOPT_POST, TRUE);
            $this->_setOpt(CURLOPT_POSTFIELDS, $postTail);
        }
        return $this;
    }

    /**
     * COOKIE container 
     * @access public
     * @param  array cookiesParams key => 'value' 
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setCookie($cookiesParams =array())
    {
        if (!is_array($cookiesParams)) {
            throw new Exception(
                'Invalid cookies params, expecting array!'
            );
        }
        if (count($cookiesParams)) {
            $cookiesTail = '';
            foreach ($cookiesParams as $param => $value) {
                $cookiesTail .= $param 
                              . '=' . $value 
                              . '; ';
            }
            if (!empty($cookiesTail)) {
                if (!empty($this->_genOptsHandler[CURLOPT_COOKIE])) {
                    $cookiesTail = $this->_genOptsHandler[CURLOPT_COOKIE] 
                                 . $cookiesTail;
                }
                $this->_setOpt(CURLOPT_COOKIE, $cookiesTail);
            }
        }
        return $this;
    }


    /**
     * Set file to save & get cookies.
     * File saved in tmp directory of system
     * @access public
     * @param  string fileName 
     * @return CurlWrapper object
     */
    public function cookieFile($fileName) 
    {
        $file = sys_get_temp_dir() . $fileName;
        $this->_setOpt(CURLOPT_COOKIEFILE, $file);
        $this->_setOpt(CURLOPT_COOKIEJAR, $file);
        return $this;
    }

    /**
     * Set timeout to current request
     * @access public
     * @param  integer timeout timeout in seconds
     * @return CurlWrapper object
     */
    public function setTimeout($timeout)
    {
        if ($timeout) {
           $this->_setOpt(CURLOPT_TIMEOUT, $timeout); 
        }
        return $this;
    }

    /**
     * Add some headers to request
     * @access public
     * @param  array headerParams 'key' => 'value' container
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setHeaderFields($headerParams =array())
    {
        if (isset($this->_genOptsHandler[CURLOPT_HTTPHEADER]) 
            && !is_array($this->_genOptsHandler[CURLOPT_HTTPHEADER])
        ) {
            throw new Exception(
                'You must pass either an array to CURLOPT_HTTPHEADER parameter!'
            );
        }
        if (!is_array($headerParams)) {
            throw new Exception(
                'Wrong headers param, expecting array'
            );
        }
        $headers = array();
        foreach ($headerParams as $param => $value) {
            $headers[] = $param . ':' . $value;
        }
        if (count($headers)) {
            if (!empty($this->_genOptsHandler[CURLOPT_HTTPHEADER])) {
                $headers = array_merge(
                    $this->_genOptsHandler[CURLOPT_HTTPHEADER],
                    $headers
                );
            }
            $this->_setOpt(CURLOPT_HTTPHEADER, $headers);
        }
        return $this;
    }

    /**
     * Send file by request
     * @access public
     * @param  string full filePath path to sended file
     * @param  string post variable name
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setFile($filePath ='', $headerFileName ='')
    {
        if (!strlen($filePath)) {
            throw new Exception(
                'Empty argument given, filePath must be non-empty!'
            );
        }
        if (!file_exists($filePath)) {
            throw new Exception(
                'Error! File ' . $filePath . ' not exists!'
            );
        }
        if (strlen($headerFileName) == 0) {
            throw new Exception(
                'Error! You need to specify header name of file to send file by POST'
            );
        }
        if (!is_readable($filePath)) {
            throw new Exception(
                'Error! File ' . $filePath . ' is not readable! Permission deny'
            );
        }
        $this->setPost(array(
            $headerFileName => "@{$filePath}",
        ));
        return $this;
    }

    /**
     * Set additional fields 
     * @access public 
     * @param  array key => 'value'
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setAdditionalOpts($options =array())
    {
        if (!is_array($options)) {
            throw new Exception(
                'Invalid argument options - [array] expected!'
            );
        }   
        foreach($options as $param => $value) {
            $this->_setOpt($param, $value);
        }
        return $this;
    }

    /**
     * Allow cUrl follow by 'Location' header
     * @access public
     * @param bool allowRedirect
     * @param int maxRedirectCount
     * @return CurlWrapper object
     * @throws Exception
     */
    public function allowRedirect($allowRedirect =TRUE, $maxRedirectCount =0)
    {
        if (!is_bool($allowRedirect)) {
            throw new Exception(
                'Invalid argument given, allowRedirect - [bool] expected!'
            );
        }
        if (!is_int($maxRedirectCount)) {
            throw new Exception(
                'Invalid argument given, maxRedirectCount - [integer] expected!'
            );
        }
        $this->_setOpt(CURLOPT_FOLLOWLOCATION, $allowRedirect);
        if ($allowRedirect && $maxRedirectCount > 0) {
            $this->_setOpt(CURLOPT_MAXREDIRS, (int)$maxRedirectCount);
        }
        return $this;
    }

    /**
     * Set 'Referer' header
     * @access public
     * @param string referer
     * @return CurlWrapper object
     * @throws Exception
     */
    public function setReferer($referer ='')
    {
        if (!strlen($referer)) {
            throw new Exception(
                'Empty argument given, referer must be non-empty!'
            );
        }
        $this->_setOpt(CURLOPT_REFERER, $referer);
        return $this;
    }

    /**
     * Run current request,
     * after run add request and respond
     * @access public
     * @param  string reqSignature some custom signature to requests (optional)
     * @param  bool isDisplayed return respond to STDOUT
     * @return CurlWrapper object
     * @throws Exception
     */
    public function run($reqSignature ='', $isDisplayed =TRUE)
    {
        if (empty($this->_genOptsHandler[CURLOPT_URL])) {
            throw new Exception(
                'URL not set, or previous request executed, and URL cleaned'
            );
        }
        if (!empty($this->_GETHandler)) {
            $this->_genOptsHandler[CURLOPT_URL] .= '?' . $this->_GETHandler;
        }
        curl_setopt_array(
            $this->_curlInstance,
            $this->_genOptsHandler
        );
        $errorExec = NULL;
        try {
            $response = curl_exec($this->_curlInstance);
        } catch (Exception $e) {
            $errorExec = $e->getMessage();
        }
        $this->_setLog($reqSignature, $response, 
            curl_getinfo($this->_curlInstance), $errorExec
        );
        if ($isDisplayed) {
            echo $response;
        }
        $this->_restoreToFactory();
        return $this;
    }

    /**
     * Get log by current signature or
     * all request/ response
     * @access public
     * @param  string reqSignature signature of returned log 
     * @return CurlWrapper object
     */
    public function getLog($requestSignature ='')
    {
        // foreach if not unique signature => add to stack
        $logResponse = array();
        foreach($this->_logHandler as $logItem) {
            if (!empty($requestSignature)) {
                if ($logItem['requestSignature'] == $requestSignature) {
                    print_r($logItem);
                }
            } else {
                print_r($logItem); 
            }
        }
        return $this;
    }

    /**
     * Save log of current request 
     * @access private
     * @param string reqSignature set signature to current log 
     * @param string responseStr response 
     * @param array  requestInfo  
     * @param string errorMess error message
     */
    private function _setLog($reqSignature ='', $responseStr ='', 
        $requestInfo = '', $errorMess =''
    ) {
        $this->_logHandler[] = array(
            'requestSignature' => (empty($reqSignature)) ? 'UNSIGNED_' . ++$this->_reqCounter : $reqSignature,
            'response'         => $responseStr,
            'requestInfo'      => $requestInfo,
            'errorMessage'     => $errorMess,
        );
    }

    /**
     * Class main method to add some option to request
     * @access public
     * @param  string optKey option key
     * @param  string valueContainer option value
     * @throws Exception
     */
    private function _setOpt($optKey ='', $valueContainer)
    {
        if (empty($optKey)) {
            throw new Exception('Trying to set empty opt key');
        }
        $this->_genOptsHandler[$optKey] = $valueContainer;
    }

    /**
     * Set default opts per req/resp
     * @access private
     */
    private function _restoreToFactory()
    {
        $this->_genOptsHandler = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_AUTOREFERER    => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLINFO_HEADER_OUT    => TRUE,
            CURLOPT_TIMEOUT        => 60,
        ); 
        $this->_GETHandler = '';
        $this->_curlInstance = curl_init();
    }
}
