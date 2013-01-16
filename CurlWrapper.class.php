<?php
final class CurlWrapper
{

    /**
     * @var bool go to redirect option (by default TRUE) 
     * @access public
     */
    public $allowRedirect = TRUE;

    /**
     * @var obj CurlWrapper instance
     * @access private
     */
    private $_Instance = NULL;

    /**
     * @var string cUrl instance
     * @access private
     */
    private $_curlInstance = NULL;

    /**
     * @var string Url handler
     * @access private
     */
    private $_urlHandler = NULL;

    /**
     * @var array GET handler
     * @access private
     */
    private $_getHandler = NULL;

    /**
     * @var array POST handler
     */
    private $_postHandler = NULL;

    /**
     * @var array COOKIE handler
     * @access private
     */
    private $_cookiesHandler = NULL;

    /**
     * Owerride magic methods, construct, clone
     */
    public function __get($var){}
    public function __set($var, $val){}
    public function __call($meth, $props){}
    public function __callStatic($meth, $props) {}
    public function __clone() {}
    public function __construct() {}


    /**
     * Singleton meth
     */
    public static function getInstance() 
    {
        if (is_null(self::_Instance)) {
           $this->_Instance = new CurlWrapper(); 
        }

        return $this->_Instance;
    }

    /**
     * setUrl
     * set url to query
     * @access public
     * @var string url - decoded url to resource
     * #return bool
     */
    public function setUrl($url ='')
    {}

    /**
     * setAuth
     * set login / pass (IF NEEDED)
     * @access public
     * @var string login
     * @var string pass 
     */
    public function setAuth ($login ='', $passwd ='')
    {}

    public function setGet($getParams = array())
    {}

    public function setPost()
    {}

    public function setCookies()
    {}

    public function run()
    {}

    private function _setOpt()
    {}

    private function _cleanUp()
    {}

}
