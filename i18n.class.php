<?php

namespace Philipp15b;

use Spyc;

/*
 * Fork this project on GitHub!
 * https://github.com/Philipp15b/php-i18n
 *
 * License: Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 * License URL: http://creativecommons.org/licenses/by-sa/3.0/deed.en
 */

class i18n {

    /**
     * Language file path
     * This is the path for the language files. You must use the '{LANGUAGE}' placeholder for the language or the script wont find any language files.
     *
     * @var string
     */
    protected $filePath = './lang/lang_{LANGUAGE}.ini';

    /**
     * Cache file path
     * This is the path for all the cache files. Best is an empty directory with no other files in it.
     *
     * @var string
     */
    protected $cachePath = './langcache/';

    /**
     * Fallback language
     * This is the language which is used when there is no language file for all other user languages. It has the lowest priority.
     * Remember to create a language file for the fallback!!
     *
     * @var string
     */
    protected $fallbackLang = 'en';

    /**
     * The class name of the compiled class that contains the translated texts.
     * @var string
     */
    protected $prefix = 'L';

    /**
     * Forced language
     * If you want to force a specific language define it here.
     *
     * @var string
     */
    protected $forcedLang = NULL;

    /**
     * This is the seperator used if you use sections in your ini-file.
     * For example, if you have a string 'greeting' in a section 'welcomepage' you will can access it via 'L::welcomepage_greeting'.
     * If you changed it to 'ABC' you could access your string via 'L::welcomepageABCgreeting'
     *
     * @var string
     */
    protected $sectionSeperator = '_';


    /*
     * The following properties are only available after calling init().
     */

    /**
     * User languages
     * These are the languages the user uses.
     * Normally, if you use the getUserLangs-method this array will be filled in like this:
     * 1. Forced language
     * 2. Language in $_GET['lang']
     * 3. Language in $_SESSION['lang']
     * 4. Fallback language
     *
     * @var array
     */
    protected $userLangs = [];

    protected $appliedLangs = [];
    protected $langFilePath = NULL;
    protected $cacheFilePath = NULL;
    protected $isInitialized = false;


    /**
     * Constructor
     * The constructor sets all important settings. All params are optional, you can set the options via extra functions too.
     *
     * @param string [$filePath] This is the path for the language files. You must use the '{LANGUAGE}' placeholder for the language.
     * @param string [$cachePath] This is the path for all the cache files. Best is an empty directory with no other files in it. No placeholders.
     * @param string [$fallbackLang] This is the language which is used when there is no language file for all other user languages. It has the lowest priority.
     * @param string [$prefix] The class name of the compiled class that contains the translated texts. Defaults to 'L'.
     */
    public function __construct($filePath = NULL, $cachePath = NULL, $fallbackLang = NULL, $prefix = NULL) {
        // Apply settings
        if ($filePath != NULL) {
            $this->filePath = $filePath;
        }

        if ($cachePath != NULL) {
            $this->cachePath = $cachePath;
        }

        if ($fallbackLang != NULL) {
            $this->fallbackLang = $fallbackLang;
        }

        if ($prefix != NULL) {
            $this->prefix = $prefix;
        }
    }

    public function init() {
        if ($this->isInitialized()) {
            throw new \BadMethodCallException('This object from class ' . __CLASS__ . ' is already initialized. It is not possible to init one object twice!');
        }

        $this->isInitialized = true;

        $appliedLang = $this->loadValidLangs();

        // search for cache file
        $this->cacheFilePath = $this->cachePath . '/php_i18n_' . md5_file(__FILE__) . '_' . $appliedLang . '.cache.php';

        // if no cache file exists or if it is older than the language file create a new one
        if (!file_exists($this->cacheFilePath) || filemtime($this->cacheFilePath) < filemtime($this->langFilePath)) {

            $reversed = array_reverse($this->appliedLangs);
            $config = [];
            foreach ($reversed as $langCode) {
                $new_config = self::parseLangFile(self::getLangFilePath($langCode, $this->filePath));
                $config = array_merge($config, $new_config);
            }

            $compiled = "<?php class " . $this->prefix . " {\n";
            $compiled .= $this->compile($config);
            $compiled .= 'public static function __callStatic($string, $args) {' . "\n";
            $compiled .= '    return vsprintf(constant("self::" . $string), $args);' . "\n";
            $compiled .= "}\n}";

            if (file_put_contents($this->cacheFilePath, $compiled) === FALSE) {
                throw new \Exception("Could not write cache file to path '" . $this->cacheFilePath . "'. Is it writable?");
            }
            chmod($this->cacheFilePath, 0777);

        }

        require_once $this->cacheFilePath;
    }
    
    private static function parseLangFile($langFilePath)
    {
        $extension = self::parseFilePathExtension($langFilePath);
        switch ($extension) {
            case 'ini':
                $config = parse_ini_file($langFilePath, true);
                break;
            case 'yml':
                $config = Spyc::YAMLLoad($langFilePath);
                break;
            case 'json':
                $config = json_decode(file_get_contents($langFilePath), true);
                break;
            default:
                throw new \InvalidArgumentException($extension . " is not a valid extension!");
        }

        return $config;

    }
    
    public static function getLangFilePath($langCode, $filePath)
    {
        return str_replace('{LANGUAGE}', $langCode, $filePath);
    }

    public static function parseFilePathExtension($langFilePath) {
        return pathinfo($langFilePath, PATHINFO_EXTENSION);
    }
    
    public function isInitialized() {
        return $this->isInitialized;
    }

    public function getAppliedLang() {
        return $this->appliedLang;
    }

    public function getCachePath() {
        return $this->cachePath;
    }

    public function getFallbackLang() {
        return $this->fallbackLang;
    }

    public function setFilePath($filePath) {
        $this->fail_after_init();
        $this->filePath = $filePath;
    }

    public function setCachePath($cachePath) {
        $this->fail_after_init();
        $this->cachePath = $cachePath;
    }

    public function setFallbackLang($fallbackLang) {
        $this->fail_after_init();
        $this->fallbackLang = $fallbackLang;
    }

    public function setPrefix($prefix) {
        $this->fail_after_init();
        $this->prefix = $prefix;
    }

    public function setForcedLang($forcedLang) {
        $this->fail_after_init();
        $this->forcedLang = $forcedLang;
    }

    public function setSectionSeperator($sectionSeperator) {
        $this->fail_after_init();
        $this->sectionSeperator = $sectionSeperator;
    }

    /**
     * getUserLangs()
     * Returns the user languages
     * Normally it returns an array like this:
     * 1. Forced language
     * 2. Language in $_GET['lang']
     * 3. Language in $_SESSION['lang']
     * 4. HTTP_ACCEPT_LANGUAGE
     * 5. Fallback language
     * 6. Parent Language
     * Note: duplicate values are deleted.
     *
     * @return array with the user languages sorted by priority.
     */
   public function getUserLangs() {
        $userLangs = array();

        // Highest priority: forced language
        if ($this->forcedLang != NULL) {
            $userLangs[] = $this->forcedLang;
        }

        // 2nd highest priority: GET parameter 'lang'
        if (isset($_GET['lang']) && is_string($_GET['lang'])) {
            $userLangs[] = $_GET['lang'];
        }

        // 3rd highest priority: SESSION parameter 'lang'
        if (isset($_SESSION['lang']) && is_string($_SESSION['lang'])) {
            $userLangs[] = $_SESSION['lang'];
        }

        // 4th highest priority: HTTP_ACCEPT_LANGUAGE
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
                $userLangs[] = self::parseLangCode($part, $parent);
                $userLangs[] = $parent;
            }
        }

        // Lowest priority: fallback
        $userLangs[] = $this->fallbackLang;

        // remove duplicate elements
        $userLangs = array_unique($userLangs);

        foreach ($userLangs as $key => $value) {
            $userLangs[$key] = preg_replace('/[^a-zA-Z0-9_-]/', '', $value); // only allow a-z, A-Z and 0-9
        }

        return $userLangs;
    }

    public static function parseLangCode($langCode, &$parentLangCode = null)
    {
        $langCode = strtolower($langCode);
        if(preg_match("/([\w]{2}(\-[\w]{2})*)/i", $langCode, $matches)){
            $langCode = $matches[0];
            $parentLangCode = $matches[2];
        }else{
            $langCode = substr($langCode, 0, 2);
        }

        return $langCode;
    }


    /**
     * Recursively compile an associative array to PHP code.
     */
    protected function compile($config, $prefix = '') {
        $code = '';
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $code .= $this->compile($value, $prefix . $key . $this->sectionSeperator);
            } else {
                $code .= 'const ' . $prefix . $key . ' = \'' . str_replace('\'', '\\\'', $value) . "';\n";
            }
        }
        return $code;
    }

    protected function get_file_extension() {
        return substr(strrchr($this->langFilePath, '.'), 1);
    }

    protected function fail_after_init() {
        if ($this->isInitialized()) {
            throw new \BadMethodCallException('This ' . __CLASS__ . ' object is already initalized, so you can not change any settings.');
        }
    }

    private function loadValidLangs()
    {
        $this->userLangs = $this->getUserLangs();

        // search for language file
        $this->appliedLangs = [];

        foreach ($this->userLangs as $priority => $langcode) {
            $langFilePath = self::getLangFilePath($langcode, $this->filePath);
            if (file_exists($langFilePath)) {
                $this->appliedLangs[] = $langcode;
            }
        }
        if (empty($this->appliedLangs)) {
            throw new \RuntimeException('No language file was found.');
        }

        // return the first lang in the set for the cache file name
        return $this->appliedLangs[0];
    }

}
