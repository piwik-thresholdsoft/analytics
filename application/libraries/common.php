<?php

/**
 * Created by Threshold soft solutions.
 * Author: rameshpaul.ch@thresholdsoft.com
 */


/**
 * Contains helper methods used by both Piwik Core and the Piwik Tracking engine.
 *
 * This is the only non-Tracker class loaded by the **\/piwik.php** file.
 */
class Common
{
    // constants used to map the referrer type to an integer in the log_visit table
    const REFERRER_TYPE_DIRECT_ENTRY = 1;
    const REFERRER_TYPE_SEARCH_ENGINE = 2;
    const REFERRER_TYPE_WEBSITE = 3;
    const REFERRER_TYPE_CAMPAIGN = 6;

    // Flag used with htmlspecialchar. See php.net/htmlspecialchars.
    const HTML_ENCODING_QUOTE_STYLE = ENT_QUOTES;

    public  $isCliMode = null;

    /*
     * Database
     */

    /**
     * Hashes a string into an integer which should be very low collision risks
     * @param string $string String to hash
     * @return int  Resulting int hash
     */
    public  function hashStringToInt($string)
    {
        $stringHash = substr(md5($string), 0, 8);
        return base_convert($stringHash, 16, 10);
    }

    /**
     * Returns a prefixed table name.
     *
     * The table prefix is determined by the `[database] tables_prefix` INI config
     * option.
     *
     * @param string $table The table name to prefix, ie "log_visit"
     * @return string  The prefixed name, ie "piwik-production_log_visit".
     * @api
     */
    public  function prefixTable($table)
    {
        $prefix = '';//Config::getInstance()->database['tables_prefix'];
        return $prefix . $table;
    }

    /**
     * Returns an array containing the prefixed table names of every passed argument.
     *
     * @param string ... The table names to prefix, ie "log_visit"
     * @return array The prefixed names in an array.
     */
    public  function prefixTables()
    {
        $result = array();
        foreach (func_get_args() as $table) {
            $result[] = $this->prefixTable($table);
        }
        return $result;
    }

    /**
     * Removes the prefix from a table name and returns the result.
     *
     * The table prefix is determined by the `[database] tables_prefix` INI config
     * option.
     *
     * @param string $table The prefixed table name, eg "piwik-production_log_visit".
     * @return string The unprefixed table name, eg "log_visit".
     * @api
     */
    public  function unprefixTable($table)
    {
         $prefixTable = null;
        if (is_null($prefixTable)) {
            $prefixTable = '';//Config::getInstance()->database['tables_prefix'];
        }
        if (empty($prefixTable)
            || strpos($table, $prefixTable) !== 0
        ) {
            return $table;
        }
        $count = 1;
        return str_replace($prefixTable, '', $table, $count);
    }

    /**
     * Returns true if PHP was invoked from command-line interface (shell)
     *
     * @since added in 0.4.4
     * @return bool true if PHP invoked as a CGI or from CLI
     */
    public  function isPhpCliMode()
    {
        if (is_bool($this->$isCliMode)) {
            return $this->$isCliMode;
        }

        $remoteAddr = @$_SERVER['REMOTE_ADDR'];
        return PHP_SAPI == 'cli' ||
        ($this->isPhpCgiType() && empty($remoteAddr));
    }

    /**
     * Returns true if PHP is executed as CGI type.
     *
     * @since added in 0.4.4
     * @return bool true if PHP invoked as a CGI
     */
    public  function isPhpCgiType()
    {
        $sapiType = php_sapi_name();

        return substr($sapiType, 0, 3) === 'cgi';
    }

    /**
     * Returns true if the current request is a console command, eg.
     * ./console xx:yy
     * or
     * php console xx:yy
     *
     * @return bool
     */
    public  function isRunningConsoleCommand()
    {
        $searched = 'console';
        $consolePos = strpos($_SERVER['SCRIPT_NAME'], $searched);
        $expectedConsolePos = strlen($_SERVER['SCRIPT_NAME']) - strlen($searched);
        $isScriptIsConsole = ($consolePos === $expectedConsolePos);
        return $this->isPhpCliMode() && $isScriptIsConsole;
    }

    /*
     * String operations
     */

    /**
     * Multi-byte substr() - works with UTF-8.
     *
     * Calls `mb_substr` if available and falls back to `substr` if it's not.
     *
     * @param string $string
     * @param int $start
     * @param int ...      optional length
     * @return string
     * @api
     */
    public  function mb_substr($string, $start)
    {
        $length = func_num_args() > 2
            ? func_get_arg(2)
            : $this->mb_strlen($string);

        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length, 'UTF-8');
        }

        return substr($string, $start, $length);
    }

    /**
     * Multi-byte strlen() - works with UTF-8
     *
     * Calls `mb_substr` if available and falls back to `substr` if not.
     *
     * @param string $string
     * @return int
     * @api
     */
    public  function mb_strlen($string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, 'UTF-8');
        }

        return strlen($string);
    }

    /**
     * Multi-byte strtolower() - works with UTF-8.
     *
     * Calls `mb_strtolower` if available and falls back to `strtolower` if not.
     *
     * @param string $string
     * @return string
     * @api
     */
    public  function mb_strtolower($string)
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($string, 'UTF-8');
        }

        return strtolower($string);
    }

    /*
     * Escaping input
     */

    /**
     * Sanitizes a string to help avoid XSS vulnerabilities.
     *
     * This function is automatically called when {@link getRequestVar()} is called,
     * so you should not normally have to use it.
     *
     * This function should be used when outputting data that isn't escaped and was
     * obtained from the user (for example when using the `|raw` twig filter on goal names).
     *
     * _NOTE: Sanitized input should not be used directly in an SQL query; SQL placeholders
     * should still be used._
     *
     * **Implementation Details**
     *
     * - [htmlspecialchars](http://php.net/manual/en/function.htmlspecialchars.php) is used to escape text.
     * - Single quotes are not escaped so **Piwik's amazing community** will still be
     *   **Piwik's amazing community**.
     * - Use of the `magic_quotes` setting will not break this method.
     * - Boolean, numeric and null values are not modified.
     *
     * @param mixed $value The variable to be sanitized. If an array is supplied, the contents
     *                     of the array will be sanitized recursively. The keys of the array
     *                     will also be sanitized.
     * @param bool $alreadyStripslashed Implementation detail, ignore.
     * @throws Exception If `$value` is of an incorrect type.
     * @return mixed  The sanitized value.
     * @api
     */
    public  function sanitizeInputValues($value, $alreadyStripslashed = false)
    {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_string($value)) {
            $value = $this->sanitizeInputValue($value);

            if (!$alreadyStripslashed) // a JSON array was already stripslashed, don't do it again for each value
            {
                $value = $this->undoMagicQuotes($value);
            }
        } elseif (is_array($value)) {
            foreach (array_keys($value) as $key) {
                $newKey = $key;
                $newKey = $this->sanitizeInputValues($newKey, $alreadyStripslashed);
                if ($key != $newKey) {
                    $value[$newKey] = $value[$key];
                    unset($value[$key]);
                }

                $value[$newKey] = $this->sanitizeInputValues($value[$newKey], $alreadyStripslashed);
            }
        } elseif (!is_null($value)
            && !is_bool($value)
        ) {
            throw new Exception("The value to escape has not a supported type. Value = " . var_export($value, true));
        }
        //print_r($value);
        return $value;
    }

    /**
     * Sanitize a single input value
     *
     * @param string $value
     * @return string  sanitized input
     */
    public  function sanitizeInputValue($value)
    {
        // $_GET and $_REQUEST already urldecode()'d
        // decode
        // note: before php 5.2.7, htmlspecialchars() double encodes &#x hex items
        $value = html_entity_decode($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        // filter
        $value = $this->sanitizeLineBreaks($value);

        // escape
        $tmp = @htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');

        // note: php 5.2.5 and above, htmlspecialchars is destructive if input is not UTF-8
        if ($value != '' && $tmp == '') {
            // convert and escape
            $value = utf8_encode($value);
            $tmp = htmlspecialchars($value, self::HTML_ENCODING_QUOTE_STYLE, 'UTF-8');
        }
        return $tmp;
    }

    /**
     * Unsanitizes a single input value and returns the result.
     *
     * @param string $value
     * @return string  unsanitized input
     */
    public  function unsanitizeInputValue($value)
    {
        return htmlspecialchars_decode($value, self::HTML_ENCODING_QUOTE_STYLE);
    }

    /**
     * Unsanitizes one or more values and returns the result.
     *
     * This method should be used when you need to unescape data that was obtained from
     * the user.
     *
     * Some data in Piwik is stored sanitized (such as site name). In this case you may
     * have to use this method to unsanitize it in order to, for example, output it in JSON.
     *
     * @param string|array $value The data to unsanitize. If an array is passed, the
     *                            array is sanitized recursively. Key values are not unsanitized.
     * @return string|array The unsanitized data.
     * @api
     */
    public  function unsanitizeInputValues($value)
    {
        if (is_array($value)) {
            $result = array();
            foreach ($value as $key => $arrayValue) {
                $result[$key] = $this->unsanitizeInputValues($arrayValue);
            }
            return $result;
        } else {
            return $this->unsanitizeInputValue($value);
        }
    }

    /**
     * Undo the damage caused by magic_quotes; deprecated in php 5.3 but not removed until php 5.4
     *
     * @param string
     * @return string  modified or not
     */
    private  function undoMagicQuotes($value)
    {
        return version_compare(PHP_VERSION, '5.4', '<')
        && get_magic_quotes_gpc()
            ? stripslashes($value)
            : $value;
    }

    /**
     *
     * @param string
     * @return string Line breaks and line carriage removed
     */
    public  function sanitizeLineBreaks($value)
    {
        $value = str_replace(array("\n", "\r", "\0"), '', $value);
        return $value;
    }

    /**
     * Gets a sanitized request parameter by name from the `$_GET` and `$_POST` superglobals.
     *
     * Use this function to get request parameter values. **_NEVER use `$_GET` and `$_POST` directly._**
     *
     * If the variable cannot be found, and a default value was not provided, an exception is raised.
     *
     * _See {@link sanitizeInputValues()} to learn more about sanitization._
     *
     * @param string $varName Name of the request parameter to get. By default, we look in `$_GET[$varName]`
     *                        and `$_POST[$varName]` for the value.
     * @param string|null $varDefault The value to return if the request parameter cannot be found or has an empty value.
     * @param string|null $varType Expected type of the request variable. This parameters value must be one of the following:
     *                             `'array'`, `'int'`, `'integer'`, `'string'`, `'json'`.
     *
     *                             If `'json'`, the string value will be `json_decode`-d and then sanitized.
     * @param array|null $requestArrayToUse The array to use instead of `$_GET` and `$_POST`.
     * @throws Exception If the request parameter doesn't exist and there is no default value, or if the request parameter
     *                   exists but has an incorrect type.
     * @return mixed The sanitized request parameter.
     * @api
     */
    public function getRequestVar($varName, $varDefault = null, $varType = null, $requestArrayToUse = null)
    {
        if (is_null($requestArrayToUse)) {
            $requestArrayToUse = $_GET + $_POST;
        }
        //print_r($requestArrayToUse);
        $varDefault = $this->sanitizeInputValues($varDefault);
        if ($varType === 'int') {
            // settype accepts only integer
            // 'int' is simply a shortcut for 'integer'
            $varType = 'integer';
        }

        // there is no value $varName in the REQUEST so we try to use the default value
        if (empty($varName)
            || !isset($requestArrayToUse[$varName])
            || (!is_array($requestArrayToUse[$varName])
                && strlen($requestArrayToUse[$varName]) === 0
            )
        ) {
            if (is_null($varDefault)) {
                throw new Exception("The parameter '$varName' isn't set in the Request, and a default value wasn't provided.");
            } else {
                if (!is_null($varType)
                    && in_array($varType, array('string', 'integer', 'array'))
                ) {
                    settype($varDefault, $varType);
                }
                return $varDefault;
            }
        }

        // Normal case, there is a value available in REQUEST for the requested varName:

        // we deal w/ json differently
        if ($varType == 'json') {
            $value = $this->undoMagicQuotes($requestArrayToUse[$varName]);
            $value = $this->json_decode($value, $assoc = true);
            return $this->sanitizeInputValues($value, $alreadyStripslashed = true);
        }

        $value = $this->sanitizeInputValues($requestArrayToUse[$varName]);
        if (!is_null($varType)) {
            $ok = false;

            if ($varType === 'string') {
                if (is_string($value)) $ok = true;
            } elseif ($varType === 'integer') {
                if ($value == (string)(int)$value) $ok = true;
            } elseif ($varType === 'float') {
                if ($value == (string)(float)$value) $ok = true;
            } elseif ($varType === 'array') {
                if (is_array($value)) $ok = true;
            } else {
                throw new Exception("\$varType specified is not known. It should be one of the following: array, int, integer, float, string");
            }

            // The type is not correct
            if ($ok === false) {
                if ($varDefault === null) {
                    throw new Exception("The parameter '$varName' doesn't have a correct type, and a default value wasn't provided.");
                } // we return the default value with the good type set
                else {
                    settype($varDefault, $varType);
                    return $varDefault;
                }
            }
            settype($value, $varType);
        }
        return $value;
    }

    /*
     * Generating unique strings
     */

    /**
     * Returns a 32 characters long uniq ID
     *
     * @return string 32 chars
     */
    public  function generateUniqId()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Configureable hash() algorithm (defaults to md5)
     *
     * @param string $str String to be hashed
     * @param bool $raw_output
     * @return string Hash string
     */
    public  function hash($str, $raw_output = false)
    {
         $hashAlgorithm = null;
        if (is_null($hashAlgorithm)) {
            $hashAlgorithm = 'md5';//@Config::getInstance()->General['hash_algorithm'];
        }

        if ($hashAlgorithm) {
            $hash = @hash($hashAlgorithm, $str, $raw_output);
            if ($hash !== false)
                return $hash;
        }

        return md5($str, $raw_output);
    }

    /**
     * Generate random string.
     * Do not use for security related purposes (the string is not truly random).
     *
     * @param int $length string length
     * @param string $alphabet characters allowed in random string
     * @return string  random string with given length
     */
    public  function getRandomString($length = 16, $alphabet = "abcdefghijklmnoprstuvwxyz0123456789")
    {
        $chars = $alphabet;
        $str = '';

        list($usec, $sec) = explode(" ", microtime());
        $seed = ((float)$sec + (float)$usec) * 100000;
        mt_srand($seed);

        for ($i = 0; $i < $length; $i++) {
            $rand_key = mt_rand(0, strlen($chars) - 1);
            $str .= substr($chars, $rand_key, 1);
        }
        return str_shuffle($str);
    }

    /*
     * Conversions
     */

    /**
     * Convert hexadecimal representation into binary data.
     * !! Will emit warning if input string is not hex!!
     *
     * @see http://php.net/bin2hex
     *
     * @param string $str Hexadecimal representation
     * @return string
     */
    public  function hex2bin($str)
    {
        return pack("H*", $str);
    }

    /**
     * This function will convert the input string to the binary representation of the ID
     * but it will throw an Exception if the specified input ID is not correct
     *
     * This is used when building segments containing visitorId which could be an invalid string
     * therefore throwing Unexpected PHP error [pack(): Type H: illegal hex digit i] severity [E_WARNING]
     *
     * It would be simply to silent fail the pack() call above but in all other cases, we don't expect an error,
     * so better be safe and get the php error when something unexpected is happening
     * @param string $id
     * @throws Exception
     * @return string  binary string
     */
    public  function convertVisitorIdToBin($id)
    {
        if (strlen($id) /*!== Tracker::LENGTH_HEX_ID_STRING*/
            || @bin2hex($this->hex2bin($id)) != $id
        ) {
            throw new Exception("visitorId is expected to be a  hex char string");
        }
        return $this->hex2bin($id);
    }

    /**
     * Convert IP address (in network address format) to presentation format.
     * This is a backward compatibility function for code that only expects
     * IPv4 addresses (i.e., doesn't support IPv6).
     *
     * @see IP::N2P()
     *
     * This function does not support the long (or its string representation)
     * returned by the built-in ip2long() function, from Piwik 1.3 and earlier.
     *
     * @deprecated 1.4
     *
     * @param string $ip IP address in network address format
     * @return string
     */
    public  function long2ip($ip)
    {
        // IPv4
        if (strlen($ip) == 4) {
            return $this->N2P($ip);
        }

        // IPv6 - transitional address?
        if (strlen($ip) == 16) {
            if (substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff", 0, 12) === 0
                || substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 0, 12) === 0
            ) {
                // remap 128-bit IPv4-mapped and IPv4-compat addresses
                return $this->N2P(substr($ip, 12));
            }
        }

        return '0.0.0.0';
    }

    public  function N2P($ip)
    {
        // use @inet_ntop() because it throws an exception and E_WARNING on invalid input
        $ipStr = @inet_ntop($ip);
        return $ipStr === false ? '0.0.0.0' : $ipStr;
    }

    /**
     * JSON encode wrapper
     * - missing or broken in some php 5.x versions
     *
     * @param mixed $value
     * @return string
     * @deprecated
     */
    public  function json_encode($value)
    {
        return @json_encode($value);
    }

    /**
     * JSON decode wrapper
     * - missing or broken in some php 5.x versions
     *
     * @param string $json
     * @param bool $assoc
     * @return mixed
     * @deprecated
     */
    public  function json_decode($json, $assoc = false)
    {
        return json_decode($json, $assoc);
    }

    /**
     * Detects whether an error occurred during the last json encode/decode.
     * @return bool
     */
    public  function hasJsonErrorOccurred()
    {
        return json_last_error() != JSON_ERROR_NONE;
    }

    /**
     * Returns a human readable error message in case an error occcurred during the last json encode/decode.
     * Returns an empty string in case there was no error.
     *
     * @return string
     */
    public  function getLastJsonError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return '';
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }

        return 'Unknown error';
    }

    public  function stringEndsWith($haystack, $needle)
    {
        if ('' === $needle) {
            return true;
        }

        $lastCharacters = substr($haystack, -strlen($needle));

        return $lastCharacters === $needle;
    }

    /**
     * Returns the list of parent classes for the given class.
     *
     * @param  string    $klass   A class name.
     * @return string[]  The list of parent classes in order from highest ancestor to the descended class.
     */
    public  function getClassLineage($klass)
    {
        $klasses = array_merge(array($klass), array_values(class_parents($klass, $autoload = false)));

        return array_reverse($klasses);
    }

    /*
     * DataFiles
     */

    /**
     * Returns list of continent codes
     *
     * @see core/DataFiles/Countries.php
     *
     * @return array  Array of 3 letter continent codes
     */
    public  function getContinentsList()
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';

                $continentsList = $GLOBALS['Piwik_ContinentList'];*/
        $continentsList = array();
        return $continentsList;
    }

    /**
     * Returns list of valid country codes
     *
     * @see core/DataFiles/Countries.php
     *
     * @param bool $includeInternalCodes
     * @return array  Array of (2 letter ISO codes => 3 letter continent code)
     */
    public  function getCountriesList($includeInternalCodes = false)
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';

                $countriesList = $GLOBALS['Piwik_CountryList'];
                $extras = $GLOBALS['Piwik_CountryList_Extras'];

                if ($includeInternalCodes) {
                    return array_merge($countriesList, $extras);
                }*/
        $countriesList = array();
        return $countriesList;
    }

    /**
     * Returns the list of valid language codes.
     *
     * See [core/DataFiles/Languages.php](https://github.com/piwik/piwik/blob/master/core/DataFiles/Languages.php).
     *
     * @return array Array of two letter ISO codes mapped with their associated language names (in English). E.g.
     *               `array('en' => 'English', 'ja' => 'Japanese')`.
     * @api
     */
    public  function getLanguagesList()
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';

                $languagesList = $GLOBALS['Piwik_LanguageList'];*/
        $languagesList = array();
        return $languagesList;
    }

    /**
     * Returns a list of language to country mappings.
     *
     * See [core/DataFiles/LanguageToCountry.php](https://github.com/piwik/piwik/blob/master/core/DataFiles/LanguageToCountry.php).
     *
     * @return array Array of two letter ISO language codes mapped with two letter ISO country codes:
     *               `array('fr' => 'fr') // French => France`
     * @api
     */
    public  function getLanguageToCountryList()
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/LanguageToCountry.php';

                $languagesList = $GLOBALS['Piwik_LanguageToCountry'];*/
        $languagesList = array();
        return $languagesList;
    }

    /**
     * Returns list of search engines by URL
     *
     * @see core/DataFiles/SearchEngines.php
     *
     * @return array  Array of ( URL => array( searchEngineName, keywordParameter, path, charset ) )
     */
    public  function getSearchEngineUrls()
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/SearchEngines.php';

                $searchEngines = $GLOBALS['Piwik_SearchEngines'];

                Piwik::postEvent('Referrer.addSearchEngineUrls', array(&$searchEngines));*/
        $searchEngines = array();
        return $searchEngines;
    }

    /**
     * Returns list of search engines by name
     *
     * @see core/DataFiles/SearchEngines.php
     *
     * @return array  Array of ( searchEngineName => URL )
     */
    public  function getSearchEngineNames()
    {
        $searchEngines = $this->getSearchEngineUrls();

        $nameToUrl = array();
        foreach ($searchEngines as $url => $info) {
            if (!isset($nameToUrl[$info[0]])) {
                $nameToUrl[$info[0]] = $url;
            }
        }

        return $nameToUrl;
    }

    /**
     * Returns list of social networks by URL
     *
     * @see core/DataFiles/Socials.php
     *
     * @return array  Array of ( URL => Social Network Name )
     */
    public  function getSocialUrls()
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Socials.php';

                $socialUrls = $GLOBALS['Piwik_socialUrl'];

                Piwik::postEvent('Referrer.addSocialUrls', array(&$socialUrls));*/

        $socialUrls = array();
        return $socialUrls;
    }

    /**
     * Returns list of provider names
     *
     * @see core/DataFiles/Providers.php
     *
     * @return array  Array of ( dnsName => providerName )
     */
    public  function getProviderNames()
    {
        /*        require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Providers.php';

                $providers = $GLOBALS['Piwik_ProviderNames'];*/
        $providers = array();
        return $providers;
    }

    /*
     * Language, country, continent
     */

    /**
     * Returns the browser language code, eg. "en-gb,en;q=0.5"
     *
     * @param string|null $browserLang Optional browser language, otherwise taken from the request header
     * @return string
     */
    public  function getBrowserLanguage($browserLang = null)
    {
         $replacementPatterns = array(
            // extraneous bits of RFC 3282 that we ignore
            '/(\\\\.)/', // quoted-pairs
            '/(\s+)/', // CFWcS white space
            '/(\([^)]*\))/', // CFWS comments
            '/(;q=[0-9.]+)/', // quality

            // found in the LANG environment variable
            '/\.(.*)/', // charset (e.g., en_CA.UTF-8)
            '/^C$/', // POSIX 'C' locale
        );

        if (is_null($browserLang)) {
            $browserLang = $this->sanitizeInputValues(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (empty($browserLang) && $this->isPhpCliMode()) {
                $browserLang = @getenv('LANG');
            }
        }

        if (is_null($browserLang)) {
            // a fallback might be to infer the language in HTTP_USER_AGENT (i.e., localized build)
            $browserLang = "";
        } else {
            // language tags are case-insensitive per HTTP/1.1 s3.10 but the region may be capitalized per ISO3166-1;
            // underscores are not permitted per RFC 4646 or 4647 (which obsolete RFC 1766 and 3066),
            // but we guard against a bad user agent which naively uses its locale
            $browserLang = strtolower(str_replace('_', '-', $browserLang));

            // filters
            $browserLang = preg_replace($replacementPatterns, '', $browserLang);

            $browserLang = preg_replace('/((^|,)chrome:.*)/', '', $browserLang, 1); // Firefox bug
            $browserLang = preg_replace('/(,)(?:en-securid,)|(?:(^|,)en-securid(,|$))/', '$1', $browserLang, 1); // unregistered language tag

            $browserLang = str_replace('sr-sp', 'sr-rs', $browserLang); // unofficial (proposed) code in the wild
        }

        return $browserLang;
    }

    /**
     * Returns the visitor country based on the Browser 'accepted language'
     * information, but provides a hook for geolocation via IP address.
     *
     * @param string $lang browser lang
     * @param bool $enableLanguageToCountryGuess If set to true, some assumption will be made and detection guessed more often, but accuracy could be affected
     * @param string $ip
     * @return string  2 letter ISO code
     */
    public  function getCountry($lang, $enableLanguageToCountryGuess, $ip)
    {
        if (empty($lang) || strlen($lang) < 2 || $lang == 'xx') {
            return 'xx';
        }

        $validCountries = $this->getCountriesList();
        return $this->extractCountryCodeFromBrowserLanguage($lang, $validCountries, $enableLanguageToCountryGuess);
    }

    /**
     * Returns list of valid country codes
     *
     * @param string $browserLanguage
     * @param array $validCountries Array of valid countries
     * @param bool $enableLanguageToCountryGuess (if true, will guess country based on language that lacks region information)
     * @return array Array of 2 letter ISO codes
     */
    public  function extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess)
    {
        $langToCountry = $this->getLanguageToCountryList();

        if ($enableLanguageToCountryGuess) {
            if (preg_match('/^([a-z]{2,3})(?:,|;|$)/', $browserLanguage, $matches)) {
                // match language (without region) to infer the country of origin
                if (array_key_exists($matches[1], $langToCountry)) {
                    return $langToCountry[$matches[1]];
                }
            }
        }

        if (!empty($validCountries) && preg_match_all('/[-]([a-z]{2})/', $browserLanguage, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $parts) {
                // match location; we don't make any inferences from the language
                if (array_key_exists($parts[1], $validCountries)) {
                    return $parts[1];
                }
            }
        }
        return 'xx';
    }

    /**
     * Returns the visitor language based only on the Browser 'accepted language' information
     *
     * @param string $browserLanguage Browser's accepted langauge header
     * @param array $validLanguages array of valid language codes
     * @return string  2 letter ISO 639 code
     */
    public  function extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages)
    {
        // assumes language preference is sorted;
        // does not handle language-script-region tags or language range (*)
        if (!empty($validLanguages) && preg_match_all('/(?:^|,)([a-z]{2,3})([-][a-z]{2})?/', $browserLanguage, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $parts) {
                if (count($parts) == 3) {
                    // match locale (language and location)
                    if (in_array($parts[1] . $parts[2], $validLanguages)) {
                        return $parts[1] . $parts[2];
                    }
                }
                // match language only (where no region provided)
                if (in_array($parts[1], $validLanguages)) {
                    return $parts[1];
                }
            }
        }
        return 'xx';
    }

    /**
     * Returns the continent of a given country
     *
     * @param string $country 2 letters isocode
     *
     * @return string  Continent (3 letters code : afr, asi, eur, amn, ams, oce)
     */
    public  function getContinent($country)
    {
        $countryList = $this->getCountriesList();
        if (isset($countryList[$country])) {
            return $countryList[$country];
        }
        return 'unk';
    }

    /*
     * Campaign
     */

    /**
     * Returns the list of Campaign parameter names that will be read to classify
     * a visit as coming from a Campaign
     *
     * @return array array(
     *            0 => array( ... ) // campaign names parameters
     *            1 => array( ... ) // campaign keyword parameters
     * );
     */
    public  function getCampaignParameters()
    {
        $return = array(
            'pk_campaign',//Config::getInstance()->Tracker['campaign_var_name'],
            'pk_kwd'//Config::getInstance()->Tracker['campaign_keyword_var_name'],
        );

        foreach ($return as &$list) {
            if (strpos($list, ',') !== false) {
                $list = explode(',', $list);
            } else {
                $list = array($list);
            }
            $list = array_map('trim', $list);
        }

        return $return;
    }

    /*
     * Referrer
     */

    /**
     * Returns a string with a comma separated list of placeholders for use in an SQL query. Used mainly
     * to fill the `IN (...)` part of a query.
     *
     * @param array|string $fields The names of the mysql table fields to bind, e.g.
     *                             `array(fieldName1, fieldName2, fieldName3)`.
     *
     *                             _Note: The content of the array isn't important, just its length._
     * @return string The placeholder string, e.g. `"?, ?, ?"`.
     * @api
     */
    public  function getSqlStringFieldsArray($fields)
    {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        $count = count($fields);
        if ($count == 0) {
            return "''";
        }
        return '?' . str_repeat(',?', $count - 1);
    }

    /**
     * Sets outgoing header.
     *
     * @param string $header The header.
     * @param bool $replace Whether to replace existing or not.
     */
    public  function sendHeader($header, $replace = true)
    {
        // don't send header in CLI mode
        if($this->isPhpCliMode()) {
            return;
        }
        if (isset($GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING']) && $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING']) {
            @header($header, $replace);
        } else {
            header($header, $replace);
        }
    }

    /**
     * Returns the ID of the current LocationProvider (see UserCountry plugin code) from
     * the Tracker cache.
     */
    public  function getCurrentLocationProviderId()
    {
        /*        $cache = Cache::getCacheGeneral();
                return empty($cache['currentLocationProviderId'])
                    ? DefaultProvider::ID
                    : $cache['currentLocationProviderId'];*/
    }

    public  function printDebug($info = '')
    {

    }

}
