<?php
	// include i18n class and initialize it
	require_once 'i18n.class.php';
	$i18n = new i18n('lang/lang_{LANGUAGE}.ini', 'langcache/', 'en');
	// Parameters: language file path, cache dir, default language (all optional)

	// init object: load language files, parse them if not cached, and so on.
	$i18n->init();
?>

<!-- get applied language -->
<p>Applied Language: <?php echo $i18n->getAppliedLang(); ?> </p>

<!-- get the cache path -->
<p>Cache path: <?php echo $i18n->getCachePath(); ?></p>

<!-- Get some greetings -->
<p>A greeting: <?php echo L::greeting; ?></p>

<!-- Get last modified date -->
<p>Last modified: <?php echo L::last_modified("today"); ?></p>

<!-- A string with 2 variables -->
<p>A string with 2 variables: <?php echo L::stringwithvars("world", "today"); ?></p>

<!-- normally sections in the ini are seperated with an underscore like here. -->
<p>Something other: <?php echo L::category_somethingother; ?></p>