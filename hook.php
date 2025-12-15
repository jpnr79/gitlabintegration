<?php
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }
function plugin_gitlabintegration_install() {
	
	global $DB;

	$config = new Config();
   	$config->setConfigurationValues('plugin:Gitlab Integration', ['configuration' => false]);

   	// Add profile rights only if they don't already exist
   	$right_name = 'gitlabintegration:read';
   	$existing = $DB->request([
      'FROM' => 'glpi_profilerights',
      'WHERE' => ['name' => $right_name]
   ])->count();
   
   	if ($existing == 0) {
      	ProfileRight::addProfileRights([$right_name]);
   	}
	
	//instanciate migration with version
	$migration = new Migration(100);

	// //Create table glpi_plugin_gitlab_integration only if it does not exists yet!
	plugin_gitlabintegration_create_integration($DB);

	//Create table glpi_plugin_gitlab_integration only if it does not exists yet!
	plugin_gitlabintegration_create_profiles($DB);

	//Create table glpi_plugin_gitlab_parameters only if it does not exists yet!
	plugin_gitlabintegration_create_parameters($DB);

	//Insert parameters at table glpi_plugin_gitlab_parameters only if it exist!
	plugin_gitlabintegration_insert_parameters($DB);
 
	return true;
}


function plugin_gitlabintegration_uninstall() {

	global $DB;
	
	$config = new Config();
	$config->deleteConfigurationValues('plugin:Gitlab Integration', ['configuration' => false]);

	ProfileRight::deleteProfileRights(['gitlabintegration:read']);

	$notif = new Notification();
	$options = ['itemtype' => 'Ticket',
				'event'    => 'plugin_gitlabintegration',
				'FIELDS'   => 'id'];
	foreach ($DB->request('glpi_notifications', $options) as $data) {
		$notif->delete($data);
	}

	//Drop table glpi_plugin_gitlab_integration only if it exists!
	plugin_gitlabintegration_delete_integration($DB);
	
	//Drop table glpi_plugin_gitlab_profiles_users only if it exists!
	plugin_gitlabintegration_delete_profiles($DB);

	//Drop table glpi_plugin_gitlab_parameters only if it exists!
	plugin_gitlabintegration_delete_parameters($DB);
	
	return true;
}

function plugin_gitlabintegration_create_integration($DB) {
	if (!$DB->tableExists('glpi_plugin_gitlab_integration')) {
	    $query = "CREATE TABLE `glpi_plugin_gitlab_integration` (
				   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				   `ticket_id` BIGINT UNSIGNED NOT NULL,
				   `gitlab_project_id` BIGINT UNSIGNED NOT NULL,
				   PRIMARY KEY  (`id`),
				   KEY `ticket_id` (`ticket_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	    $DB->doQueryOrDie($query, $DB->error());
	}
}

function plugin_gitlabintegration_create_profiles($DB) {
	if (!$DB->tableExists('glpi_plugin_gitlab_profiles_users')) {
	    $query = "CREATE TABLE `glpi_plugin_gitlab_profiles_users` (
				   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				   `profile_id` INT UNSIGNED NOT NULL,
				   `user_id` BIGINT UNSIGNED NOT NULL,
				   `created_at` TIMESTAMP NULL DEFAULT NULL,
				   PRIMARY KEY (`id`),
				   KEY `profile_id` (`profile_id`),
				   KEY `user_id` (`user_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	    $DB->doQueryOrDie($query, $DB->error());
	}
}

function plugin_gitlabintegration_create_parameters($DB) {
	if (!$DB->tableExists('glpi_plugin_gitlab_parameters')) {
	    $query = "CREATE TABLE `glpi_plugin_gitlab_parameters` (
				   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				   `name` VARCHAR(50) NOT NULL UNIQUE,
				   `value` VARCHAR(125),
				   PRIMARY KEY  (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	    $DB->doQueryOrDie($query, $DB->error());
	}
}

function plugin_gitlabintegration_delete_integration($DB) {
	if ($DB->tableExists('glpi_plugin_gitlab_integration')) {
		$drop_count = "DROP TABLE glpi_plugin_gitlab_integration";
		$DB->doQuery($drop_count); 
	}
}

function plugin_gitlabintegration_delete_profiles($DB) {
	if ($DB->tableExists('glpi_plugin_gitlab_profiles_users')) {
		$drop_count = "DROP TABLE glpi_plugin_gitlab_profiles_users";
		$DB->doQuery($drop_count);
	} 
}

function plugin_gitlabintegration_delete_parameters($DB) {
	if ($DB->tableExists('glpi_plugin_gitlab_parameters')) {
		$drop_count = "DROP TABLE glpi_plugin_gitlab_parameters";
		$DB->doQuery($drop_count);
	} 
}

function plugin_gitlabintegration_insert_parameters($DB) {
	if ($DB->tableExists('glpi_plugin_gitlab_parameters')) {

		$ini_file = GLPI_ROOT . "/plugins/gitlabintegration/gitlabintegration.ini";
		
		if (!file_exists($ini_file)) {
			return;
		}
		
		$ini_array = parse_ini_file($ini_file);
		
		if (!is_array($ini_array)) {
			return;
		}

		$parameters = [[
			'name'  => 'gitlab_url',
			'value' => isset($ini_array['GITLAB_URL']) && $ini_array['GITLAB_URL'] != "" ? $ini_array['GITLAB_URL'] : NULL
		],
		[
			'name'  => 'gitlab_token',
			'value' => isset($ini_array['GITLAB_TOKEN']) && $ini_array['GITLAB_TOKEN'] != "" ? $ini_array['GITLAB_TOKEN'] : NULL
		]];
		
		foreach ($parameters as $parameter) {
			$DB->insert(
				'glpi_plugin_gitlab_parameters', [
					'name'  => $parameter['name'],
					'value' => $parameter['value']
				]
			);
		}
	}
}

?>
