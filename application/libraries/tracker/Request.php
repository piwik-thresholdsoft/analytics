<?php
/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */

/*class Request {

    private $_request;

    public function __construct()
    {
        $this->_request = $_GET+$_POST;
    }

    public function getSiteID(){
        return isset($this->_request['idsite']) ? $this->_request['idsite'] : '0';
    }

}*/

class Request
{
    /**
     * @var array
     */
    protected $params;

    protected $forcedVisitorId = false;

    protected $isAuthenticated = true;

    protected $tokenAuth;

    const UNKNOWN_RESOLUTION = 'unknown';

    private $CI;

    /**
     * @param $args
     */
    public function __construct($args)
    {
        $params = $args[0];
        $tokenAuth = true;

        $this->CI = get_instance();
        $this->CI->load->library('common');


        if (!is_array($params)) {
            $params = array();
        }
        $this->params = $params;
        $this->tokenAuth = $tokenAuth;
        $this->timestamp = time();
        $this->enforcedIp = false;

        // When the 'url' and referrer url parameter are not given, we might be in the 'Simple Image Tracker' mode.
        // The URL can default to the Referrer, which will be in this case
        // the URL of the page containing the Simple Image beacon
        if (empty($this->params['urlref'])
            && empty($this->params['url'])
        ) {
            $url = @$_SERVER['HTTP_REFERER'];
            if (!empty($url)) {
                $this->params['url'] = $url;
            }
        }
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        if (is_null($this->isAuthenticated)) {
            return $this->tokenAuth;
        }

        return $this->isAuthenticated;
    }

    /**
     * @return float|int
     */
    public function getDaysSinceFirstVisit()
    {
        $cookieFirstVisitTimestamp = $this->getParam('_idts');
        if (!$this->isTimestampValid($cookieFirstVisitTimestamp)) {
            $cookieFirstVisitTimestamp = $this->getCurrentTimestamp();
        }
        $daysSinceFirstVisit = round(($this->getCurrentTimestamp() - $cookieFirstVisitTimestamp) / 86400, $precision = 0);
        if ($daysSinceFirstVisit < 0) {
            $daysSinceFirstVisit = 0;
        }
        return $daysSinceFirstVisit;
    }

    /**
     * @return bool|float|int
     */
    public function getDaysSinceLastOrder()
    {
        $daysSinceLastOrder = false;
        $lastOrderTimestamp = $this->getParam('_ects');
        if ($this->isTimestampValid($lastOrderTimestamp)) {
            $daysSinceLastOrder = round(($this->getCurrentTimestamp() - $lastOrderTimestamp) / 86400, $precision = 0);
            if ($daysSinceLastOrder < 0) {
                $daysSinceLastOrder = 0;
            }
        }
        return $daysSinceLastOrder;
    }

    /**
     * @return float|int
     */
    public function getDaysSinceLastVisit()
    {
        $daysSinceLastVisit = 0;
        $lastVisitTimestamp = $this->getParam('_viewts');
        if ($this->isTimestampValid($lastVisitTimestamp)) {
            $daysSinceLastVisit = round(($this->getCurrentTimestamp() - $lastVisitTimestamp) / 86400, $precision = 0);
            if ($daysSinceLastVisit < 0) {
                $daysSinceLastVisit = 0;
            }
        }
        return $daysSinceLastVisit;
    }

    /**
     * @return int|mixed
     */
    public function getVisitCount()
    {
        $visitCount = $this->getParam('_idvc');
        if ($visitCount < 1) {
            $visitCount = 1;
        }
        return $visitCount;
    }

    /**
     * Returns the language the visitor is viewing.
     *
     * @return string browser language code, eg. "en-gb,en;q=0.5"
     */
    public function getBrowserLanguage()
    {
        return $this->CI->common->getRequestVar('lang', $this->CI->common->getBrowserLanguage(), 'string', $this->params);
    }

    /**
     * @return string
     */
    public function getLocalTime()
    {
        $localTimes = array(
            'h' => (string)$this->CI->common->getRequestVar('h', $this->getCurrentDate("H"), 'int', $this->params),
            'i' => (string)$this->CI->common->getRequestVar('m', $this->getCurrentDate("i"), 'int', $this->params),
            's' => (string)$this->CI->common->getRequestVar('s', $this->getCurrentDate("s"), 'int', $this->params)
        );
        foreach ($localTimes as $k => $time) {
            if (strlen($time) == 1) {
                $localTimes[$k] = '0' . $time;
            }
        }
        $localTime = $localTimes['h'] . ':' . $localTimes['i'] . ':' . $localTimes['s'];
        return $localTime;
    }

    /**
     * Returns the current date in the "Y-m-d" PHP format
     *
     * @param string $format
     * @return string
     */
    protected function getCurrentDate($format = "Y-m-d")
    {
        return date($format, $this->getCurrentTimestamp());
    }

    public function getGoalRevenue($defaultGoalRevenue)
    {
        return $this->CI->common->getRequestVar('revenue', $defaultGoalRevenue, 'float', $this->params);
    }

    public function getParam($name)
    {
        static $supportedParams = array(
            // Name => array( defaultValue, type )
            '_refts'       => array(0, 'int'),
            '_ref'         => array('', 'string'),
            '_rcn'         => array('', 'string'),
            '_rck'         => array('', 'string'),
            '_idts'        => array(0, 'int'),
            '_viewts'      => array(0, 'int'),
            '_ects'        => array(0, 'int'),
            '_idvc'        => array(1, 'int'),
            'url'          => array('', 'string'),
            'urlref'       => array('', 'string'),
            'res'          => array(self::UNKNOWN_RESOLUTION, 'string'),
            'idgoal'       => array(-1, 'int'),

            // other
            'bots'         => array(0, 'int'),
            'dp'           => array(0, 'int'),
            'rec'          => array(false, 'int'),
            'new_visit'    => array(0, 'int'),

            // Ecommerce
            'ec_id'        => array(false, 'string'),
            'ec_st'        => array(false, 'float'),
            'ec_tx'        => array(false, 'float'),
            'ec_sh'        => array(false, 'float'),
            'ec_dt'        => array(false, 'float'),
            'ec_items'     => array('', 'string'),

            // Events
            'e_c'          => array(false, 'string'),
            'e_a'          => array(false, 'string'),
            'e_n'          => array(false, 'string'),
            'e_v'          => array(false, 'float'),

            // some visitor attributes can be overwritten
            'cip'          => array(false, 'string'),
            'cdt'          => array(false, 'string'),
            'cid'          => array(false, 'string'),

            // Actions / pages
            'cs'           => array(false, 'string'),
            'download'     => array('', 'string'),
            'link'         => array('', 'string'),
            'action_name'  => array('', 'string'),
            'search'       => array('', 'string'),
            'search_cat'   => array(false, 'string'),
            'search_count' => array(-1, 'int'),
            'gt_ms'        => array(-1, 'int'),
        );

        if (!isset($supportedParams[$name])) {
            throw new Exception("Requested parameter $name is not a known Tracking API Parameter.");
        }
        $paramDefaultValue = $supportedParams[$name][0];
        $paramType = $supportedParams[$name][1];

        $value = $this->CI->common->getRequestVar($name, $paramDefaultValue, $paramType, $this->params);

        return $value;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getCurrentTimestamp()
    {
        return $this->timestamp;
    }

    protected function isTimestampValid($time)
    {
        return $time <= $this->getCurrentTimestamp()
        && $time > $this->getCurrentTimestamp() - 10 * 365 * 86400;
    }

    public function getIdSite()
    {
        $idSite = $this->CI->common->getRequestVar('idsite', 0, 'string', $this->params);
        /**
         * Triggered when obtaining the ID of the site we are tracking a visit for.
         *
         * This event can be used to change the site ID so data is tracked for a different
         * website.
         *
         * @param int &$idSite Initialized to the value of the **idsite** query parameter. If a
         *                     subscriber sets this variable, the value it uses must be greater
         *                     than 0.
         * @param array $params The entire array of request parameters in the current tracking
         *                      request.
         */
        if ($idSite <= 0) {
            throw new Exception('Invalid idSite: \'' . $idSite . '\'');
        }
        return $idSite;
    }

    public function getUserAgent()
    {
/*        $default = @$_SERVER['HTTP_USER_AGENT'];
        return $this->CI->common->getRequestVar('ua', is_null($default) ? false : $default, 'string', $this->params);*/
        $this->CI->load->library('user_agent');
        $data['os'] = $this->CI->agent->platform();
        $data['browser_name'] = $this->CI->agent->browser();
        $data['browser_version'] = $this->CI->agent->version();
        $data['language'] = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if ($this->CI->agent->is_referral())
        {
            $data['referrer'] = $this->CI->agent->referrer();
        }else{
            $data['referrer'] = '';
        }
        return $data;
    }

    public function getCustomVariables($scope)
    {
        if ($scope == 'visit') {
            $parameter = '_cvar';
        } else {
            $parameter = 'cvar';
        }

        $customVar = $this->CI->common->unsanitizeInputValues($this->CI->common->getRequestVar($parameter, '', 'json', $this->params));
        if (!is_array($customVar)) {
            return array();
        }
        $customVariables = array();
        $maxCustomVars = 5;//CustomVariables::getMaxCustomVariables();
        foreach ($customVar as $id => $keyValue) {
            $id = (int)$id;
            if ($id < 1
                || $id > $maxCustomVars
                || count($keyValue) != 2
                || (!is_string($keyValue[0]) && !is_numeric($keyValue[0]))
            ) {
                $this->CI->common->printDebug("Invalid custom variables detected (id=$id)");
                continue;
            }
            if (strlen($keyValue[1]) == 0) {
                $keyValue[1] = "";
            }
            // We keep in the URL when Custom Variable have empty names
            // and values, as it means they can be deleted server side

            $key = $keyValue[0];//self::truncateCustomVariable($keyValue[0]);
            $value = $keyValue[1];//self::truncateCustomVariable($keyValue[1]);
            $customVariables['custom_var_k' . $id] = $key;
            $customVariables['custom_var_v' . $id] = $value;
        }

        return $customVariables;
    }

    public function getCustomData($scope)
    {
        if ($scope == 'visit') {
            $parameter = 'myCustomData';
        } else {
            $parameter = 'myCustomData';
        }

        $customVar = $this->CI->common->unsanitizeInputValues($this->CI->common->getRequestVar($parameter, '', 'json', $this->params));
        return $customVar;
    }


    public static function truncateCustomVariable($input)
    {
        return substr(trim($input), 0, 5/* CustomVariables::getMaxLengthCustomVariables()*/);
    }

    protected function shouldUseThirdPartyCookie()
    {
        return TRUE;//(bool)Config::getInstance()->Tracker['use_third_party_id_cookie'];
    }

    /**
     * Update the cookie information.
     */
    public function setThirdPartyCookie($idVisitor)
    {
        if (!$this->shouldUseThirdPartyCookie()) {
            return;
        }
        $this->CI->common->printDebug("We manage the cookie...");

        $cookie = $this->makeThirdPartyCookie();
        // idcookie has been generated in handleNewVisit or we simply propagate the old value
        $cookie->set(0, bin2hex($idVisitor));
        $cookie->save();
    }

    protected function makeThirdPartyCookie()
    {
        $cookie = new Cookie(
            $this->getCookieName(),
            $this->getCookieExpire(),
            $this->getCookiePath());
        $this->CI->common->printDebug($cookie);
        return $cookie;
    }

    protected function getCookieName()
    {
        return '_pk_uid';//Config::getInstance()->Tracker['cookie_name'];
    }

    protected function getCookieExpire()
    {
        return $this->getCurrentTimestamp() + 63072000;//Config::getInstance()->Tracker['cookie_expire'];
    }

    protected function getCookiePath()
    {
        return '';//Config::getInstance()->Tracker['cookie_path'];
    }

    /**
     * Is the request for a known VisitorId, based on 1st party, 3rd party (optional) cookies or Tracking API forced Visitor ID
     * @throws Exception
     */
    public function getVisitorId()
    {
        $found = true;

        // Was a Visitor ID "forced" (@see Tracking API setVisitorId()) for this request?
        $idVisitor = $this->getForcedVisitorId();
        if (!empty($idVisitor)) {
            $this->CI->common->printDebug("Request will be recorded for this idvisitor = " . $idVisitor);
            $found = true;
        }

        // - If set to use 3rd party cookies for Visit ID, read the cookie
        if (!$found) {
            // - By default, reads the first party cookie ID
            $useThirdPartyCookie = $this->shouldUseThirdPartyCookie();
            if ($useThirdPartyCookie) {
                $cookie = $this->makeThirdPartyCookie();
                $idVisitor = $cookie->get(0);
                $found = true;
            }
        }
        // If a third party cookie was not found, we default to the first party cookie
        if (!$found) {
            $idVisitor = $this->CI->common->getRequestVar('_id', '', 'string', $this->params);
            $found = $idVisitor;//strlen($idVisitor) >= Tracker::LENGTH_HEX_ID_STRING;
        }

        if ($found) {
            return $idVisitor;
        }
        return false;
    }

    public function getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }

    public function setForceIp($ip)
    {
        if (!empty($ip)) {
            $this->enforcedIp = $ip;
        }
    }

    public function setForceDateTime($dateTime)
    {
        if (!is_numeric($dateTime)) {
            $dateTime = strtotime($dateTime);
        }
        if (!empty($dateTime)) {
            $this->timestamp = $dateTime;
        }
    }

    public function setForcedVisitorId($visitorId)
    {
        if (!empty($visitorId)) {
            $this->forcedVisitorId = $visitorId;
        }
    }

    public function getForcedVisitorId()
    {
        return $this->forcedVisitorId;
    }

    public function getPlugins()
    {
        static $pluginsInOrder = array('fla', 'java', 'dir', 'qt', 'realp', 'pdf', 'wma', 'gears', 'ag', 'cookie');
        $plugins = array();
        foreach ($pluginsInOrder as $param) {
            $plugins[] = $this->CI->common->getRequestVar($param, 0, 'int', $this->params);
        }
        return $plugins;
    }

    public function getParamsCount()
    {
        return count($this->params);
    }

    const GENERATION_TIME_MS_MAXIMUM = 3600000; // 1 hour

    public function getPageGenerationTime()
    {
        $generationTime = $this->getParam('gt_ms');
        if ($generationTime > 0
            && $generationTime < self::GENERATION_TIME_MS_MAXIMUM
        ) {
            return (int)$generationTime;
        }
        return false;
    }

    public function getDisplayResolution(){
        return $this->CI->common->getRequestVar('res', 0, 'string', $this->params);
    }

    public function urlReferer(){
        return $this->CI->common->getRequestVar('urlref', '', 'string', $this->params);
    }

    public function getVisitURL(){
        return $this->CI->common->getRequestVar('url', 0, 'string', $this->params);
    }

    public function getActionData(){
        $data['action_name'] = $this->CI->common->getRequestVar('action_name', 0, 'string', $this->params);
        $data['e_a'] = $this->CI->common->getRequestVar('e_a', 0, 'string', $this->params);
        $data['e_c'] = $this->CI->common->getRequestVar('e_c', 0, 'string', $this->params);
        $data['e_n'] = $this->CI->common->getRequestVar('e_n', 0, 'string', $this->params);
        $data['e_v'] = $this->CI->common->getRequestVar('e_v', 0, 'string', $this->params);

        return $data;
    }

    public function getCampaignData(){
        $data['campaignName'] = $this->CI->common->getRequestVar('_rcn', '', 'string', $this->params);
        $data['campaignKeyWord'] = $this->CI->common->getRequestVar('_rck', '', 'string', $this->params);
        $data['campaignID'] = $this->CI->common->getRequestVar('_rcid', '', 'string', $this->params);
        return $data;
    }

    public function getSearchEnginesData(){
        $this->CI->config->load('analytics');
        $searchEngines = $this->CI->config->item('searchEngines');
        $referelURL = $this->urlReferer();
        $data = '';
        //print_r($referelURL);
        if($referelURL != ""){
            $parseURL = parse_url($referelURL);
            $host = $parseURL['host'];
            if(in_array($host, $searchEngines)){
                $data = $host;
            }
        }
        return $data;
    }

    public function getSocialNetworksData(){
        $this->CI->config->load('analytics');
        $socialNetworks = $this->CI->config->item('socialNetworks');
        $referelURL = $this->urlReferer();
        //print_r($referelURL);
        $data = '';
        if($referelURL != ""){
            $parseURL = parse_url($referelURL);
            $host = $parseURL['host'];
            if(in_array($host, $socialNetworks)){
                $data = $host;
            }
        }
        return $data;
    }

    public function getEcommerceData(){
        $data['_ects'] = $this->CI->common->getRequestVar('_ects', 0, 'int', $this->params);
        $data['ec_id'] = $this->CI->common->getRequestVar('ec_id', 0, 'string', $this->params);
        $data['ec_st'] = $this->CI->common->getRequestVar('ec_st', 0, 'float', $this->params);
        $data['ec_tx'] = $this->CI->common->getRequestVar('ec_tx', 0, 'float', $this->params);
        $data['ec_sh'] = $this->CI->common->getRequestVar('ec_sh', 0, 'float', $this->params);
        $data['ec_dt'] = $this->CI->common->getRequestVar('ec_dt', 0, 'float', $this->params);
        $data['ec_items'] = $this->CI->common->getRequestVar('ec_items', 0, 'string', $this->params);
        return $data;
    }
}
