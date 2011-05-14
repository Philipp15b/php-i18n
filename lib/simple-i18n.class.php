<?php
class i18n {

	/*
	 * Editable settings via constructor
	 */

	/**
	 * Language file path
	 * This is the path for the language files. You must use the '{LANGUAGE}' placeholder for the language.
	 *
	 * @var string
	 */
	protected $filepath = './lang/lang_{LANGUAGE}.ini';

	/**
	 * Cache file path
	 * This is the path for all the cache files. Best is an empty directory with no other files in it.
	 *
	 * @var string
	 */
	protected $cachepath = './langcache/';

	/**
	 * Fallback language
	 * This is the language which is used when there is no language file for all other user languages. It has the lowest priority.
	 * Remember to create a language file for the fallback!!
	 *
	 * @type string
	 */
	protected $fallback_lang = 'en';

	/**
	 * Forced language
	 * If you want to force a specific language define it here.
	 *
	 * @type string
	 */
	protected $force_lang = NULL;

	/**
	 * This is the seperator used if you use sections in your ini-file.
	 * For example, if you have a string 'greeting' in a section 'welcomepage' you will can access it via 'L::welcomepage_greeting'.
	 * If you changed it to 'ABC' you could access your string via 'L::welcomepageABCgreeting'
	 *
	 * @var string
	 */
	protected $section_seperator = '_';

	/*
	 * Runtime needed variables
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
	protected $user_langs = array();

	/**
	 * The applied language
	 * This means the chosen language, which must have an existing ini-file.
	 *
	 * @var NULL, string
	 */
	protected $appliedLang = NULL;

	/**
	 * Language file path
	 * This is the path for the used language file. Computed automatically.
	 *
	 * @var string
	 */
	protected $langFilePath = NULL;

	/**
	 * Cache file path
	 * This is the path for the used cache file. Computed automatically.
	 *
	 * @var string
	 */
	protected $cacheFilePath = NULL;

	/**
	 * Constructor
	 * The constructor sets all important settings and loads all needed files directly after applying the settings you can set with it. This means that you CAN NOT change a setting after initializing the class.
	 *
	 * @param string [$filepath] This is the path for the language files. You must use the '{LANGUAGE}' placeholder for the language.
	 * @param string [$cachepath] This is the path for all the cache files. Best is an empty directory with no other files in it. No placeholders.
	 * @param string [$fallback_lang] This is the language which is used when there is no language file for all other user languages. It has the lowest priority.
	 * @param string [$force_lang] If you want to force a specific language define it here.
	 * @param string [$section_seperator] This is the seperator used for sections in your ini-file.
	 */
	public function __construct($filepath =NULL, $cachepath =NULL, $fallback_lang =NULL, $force_lang =NULL, $section_seperator =NULL) {

		// Apply settings
		if($filepath != NULL) {
			$this -> filepath = $filepath;
		}

		if($cachepath != NULL) {
			$this -> cachepath = $cachepath;
		}

		if($fallback_lang != NULL) {
			$this -> fallback_lang = $fallback_lang;
		}

		if($force_lang != NULL) {
			$this -> force_lang = $force_lang;
		}

		if($section_seperator != NULL) {
			$this -> section_seperator = $section_seperator;
		}

		// set user language
		$this -> user_langs = $this -> getUserLangs();

		// search for language file
		$this -> appliedLang = NULL;
		foreach($this->user_langs as $priority => $langcode) {
			$this -> langFilePath = str_replace('{LANGUAGE}', $langcode, $this -> filepath);
			if(file_exists($this -> langFilePath)) {
				$this -> appliedLang = $langcode;
				break;
			}
		}

		// abort if no language file was found
		if($this -> appliedLang == NULL) {
			die('No language file was found.');
			// TODO: Throw exception or something like that.
		}
		// search for cache file
		$this -> cacheFilePath = $this -> cachepath . '/php_i18n_' . md5_file(__FILE__) . '_' . $this -> appliedLang . '.cache.php';

		// if no cache file exists or if it is older than the language file create a new one
		if(!file_exists($this -> cacheFilePath) || filemtime($this -> cacheFilePath) < filemtime($this -> langFilePath)) {

			$ini = parse_ini_file($this -> langFilePath, true);
			$compiled = "<?php class L {\n";
			$compiled .= $this -> compile_ini($ini);
			$compiled .= '}';

			file_put_contents($this -> cacheFilePath, $compiled);
			chmod($this -> cacheFilePath, 0777);

		}

		// include the cache file
		require_once $this -> cacheFilePath;

	}

	/**
	 * getUserLangs()
	 * Returns the user languages
	 * Normally it returns an array like this:
	 * 1. Forced language
	 * 2. Language in $_GET['lang']
	 * 3. Language in $_SESSION['lang']
	 * 4. Fallback language
	 * Note: duplicate values are deleted.
	 *
	 * @return array with the user languages sorted by priority. Highest is best.
	 */
	public function getUserLangs() {

		// reset user_lang array
		$user_langs = array();

		// Highest priority: forced language
		if($this -> force_lang != NULL) {
			array_push($user_langs, $this -> force_lang);
		}

		// 2nd highest priority: GET parameter 'lang'
		if(isset($_GET['lang']) && is_string($_GET['lang'])) {
			array_push($user_langs, $_GET['lang']);
		}

		// 3rd highest priority: SESSION parameter 'lang'
		if(isset($_SESSION['lang']) && is_string($_SESSION['lang'])) {
			array_push($user_langs, $_SESSION['lang']);
		}

		// Lowest priority: fallback
		array_push($user_langs, $this -> fallback_lang);

		// remove duplicate elements
		$user_langs = array_unique($user_langs);

		return $user_langs;
	}

	/**
	 * Parse an ini file to PHP code.
	 * This method parses a an the array expression from an ini to PHP code.
	 * To be specific it only returns some lines with 'const ###### = '#######;'
	 *
	 * @return string the PHP code
	 */
	public function compile_ini($ini, $prefix ='') {
		$tmp = '';
		foreach($ini as $key => $value) {
			if(is_array($value)) {
				$tmp .= $this->compile_ini($value, $key.$this->section_seperator);
			} else {
				$tmp .= 'const ' . $prefix . $key . ' = \'' . str_replace('\'', '\\\'', $value) . "';\n";
			}
		}
		return $tmp;
	}

	public function getAppliedLang() {
		return $this->appliedLang;
	}
	
	public function getCachePath() {
		return $this->cachepath;
	}
	
	public function getFallbackLang() {
		return $this->fallback_lang;
	}
	
	

}
?>