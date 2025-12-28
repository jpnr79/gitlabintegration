<?php

function plugin_init_gitlabintegration() {

	global $PLUGIN_HOOKS, $CFG_GLPI;
	
	include_once (GLPI_ROOT . "/plugins/gitlabintegration/inc/itemform.class.php");
	include_once (GLPI_ROOT . "/plugins/gitlabintegration/inc/eventlog.class.php");
	include_once (GLPI_ROOT . "/plugins/gitlabintegration/inc/parameters.class.php");
	include_once (GLPI_ROOT . "/plugins/gitlabintegration/inc/gitlabintegration.class.php");
	include_once (GLPI_ROOT . "/plugins/gitlabintegration/inc/menu.class.php");
	include_once (GLPI_ROOT . "/plugins/gitlabintegration/inc/profiles.class.php");

	$PLUGIN_HOOKS['add_css']['gitlabintegration'][] = "css/styles.css";
	$PLUGIN_HOOKS['add_javascript']['gitlabintegration'][] = 'js/buttonsFunctions.js';
	
	// CSRF compliance : All actions must be done via POST and forms closed by Html::closeForm();
	$PLUGIN_HOOKS['csrf_compliant']['gitlabintegration'] = true;

	if (class_exists('PluginGitlabIntegrationItemForm')) {
		$PLUGIN_HOOKS['post_item_form']['gitlabintegration'] = ['PluginGitlabIntegrationItemForm', 'postItemForm'];
	}

	// add entry to configuration menu
	$PLUGIN_HOOKS['menu_toadd']['gitlabintegration']['admin'] = ['admin' => 'PluginGitlabIntegrationMenu'];
}


function plugin_version_gitlabintegration() {
	global $DB, $LANG;

	return array('name'			  => __('Gitlab Integration','gitlabintegration'),
			     'version' 		  => '0.0.1',
				 'author'		  => 'Fáiza Letícia Schoeninger',
				 'license'		  => 'GPLv3+',
				 'homepage'		  => 'https://github.com/faizaleticia',
				 'requirements'   => ['glpi' => ['min' => '11.0', 'max' => '12.0']]
	);
}



function plugin_gitlabintegration_check_prerequisites() {
	$min_version = '11.0';
	$max_version = '12.0';
	$glpi_version = null;
	$glpi_root = '/var/www/glpi';
	$version_dir = $glpi_root . '/version';
	if (is_dir($version_dir)) {
		$files = scandir($version_dir, SCANDIR_SORT_DESCENDING);
		foreach ($files as $file) {
			if ($file[0] !== '.' && preg_match('/^\d+\.\d+\.\d+$/', $file)) {
				$glpi_version = $file;
				break;
			}
		}
	}
	// Do not use GLPI_VERSION constant, only rely on version directory for GLPI 11+ compliance
	// Load Toolbox if not loaded
	if (!class_exists('Toolbox') && file_exists($glpi_root . '/src/Toolbox.php')) {
		require_once $glpi_root . '/src/Toolbox.php';
	}
	// Fallback error logger if Toolbox::logInFile is unavailable
	if (!function_exists('gitlabintegration_fallback_log')) {
		function gitlabintegration_fallback_log($msg) {
			$logfile = __DIR__ . '/gitlabintegration_error.log';
			$date = date('Y-m-d H:i:s');
			file_put_contents($logfile, "[$date] $msg\n", FILE_APPEND);
		}
	}
	if ($glpi_version === null) {
		$logmsg = '[setup.php:plugin_gitlabintegration_check_prerequisites] ERROR: GLPI version not detected.';
		if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
			try {
				Toolbox::logInFile('gitlabintegration', $logmsg);
			} catch (Throwable $e) {
				gitlabintegration_fallback_log($logmsg . ' (Toolbox::logInFile failed: ' . $e->getMessage() . ')');
			}
		} else {
			gitlabintegration_fallback_log($logmsg);
		}
		echo "GLPI version NOT compatible. Requires GLPI >= $min_version";
		return false;
	}
	if (version_compare($glpi_version, $min_version, '<')) {
		$logmsg = sprintf(
			'ERROR [setup.php:plugin_gitlabintegration_check_prerequisites] GLPI version %s is less than required minimum %s, user=%s',
			$glpi_version, $min_version, $_SESSION['glpiname'] ?? 'unknown'
		);
		if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
			Toolbox::logInFile('gitlabintegration', $logmsg);
		} else {
			gitlabintegration_fallback_log($logmsg);
		}
		echo "GLPI version NOT compatible. Requires GLPI >= $min_version";
		return false;
	}
	if (version_compare($glpi_version, $max_version, '>')) {
		$logmsg = sprintf(
			'ERROR [setup.php:plugin_gitlabintegration_check_prerequisites] GLPI version %s is greater than supported maximum %s, user=%s',
			$glpi_version, $max_version, $_SESSION['glpiname'] ?? 'unknown'
		);
		if (class_exists('Toolbox') && method_exists('Toolbox', 'logInFile')) {
			Toolbox::logInFile('gitlabintegration', $logmsg);
		} else {
			gitlabintegration_fallback_log($logmsg);
		}
		echo "GLPI version NOT compatible. Requires GLPI <= $max_version";
		return false;
	}
	return true;
}


function plugin_gitlabintegration_check_config($verbose=false) {
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
