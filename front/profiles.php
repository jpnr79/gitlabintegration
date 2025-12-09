<?php
if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
}
include (GLPI_ROOT . "/inc/includes.php");

// $criteria = $_GET['criteria'];
$start = $_GET['start'] ?? 0;

Session::checkLoginUser();

Html::header(PluginGitlabIntegrationProfiles::getTypeName(), $_SERVER['PHP_SELF'],
             "admin", "plugingitlabintegrationmenu", "profiles");
PluginGitlabIntegrationProfiles::title();
// Search::show('PluginGitlabIntegrationProfiles');
PluginGitlabIntegrationProfiles::configPage($start);
PluginGitlabIntegrationProfiles::massiveActions($start);
PluginGitlabIntegrationProfiles::configPage($start);

Html::footer();

PluginGitlabIntegrationProfiles::dialogActions();