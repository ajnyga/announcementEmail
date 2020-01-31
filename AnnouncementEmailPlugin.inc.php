<?php

/**
 * @file plugins/generic/announcementEmail/AnnouncementEmailPlugin.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class announcementEmail
 * @ingroup plugins_generic_announcementEmail
 *
 * @brief announcementEmail plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class AnnouncementEmailPlugin extends GenericPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Insert AnnouncementEmail div
			HookRegistry::register('NotificationManager::getNotificationMessage', array($this, 'addAnnouncementContent'));
		}
		return $success;
	}

	/**
	 * Get the plugin display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.announcementEmail.displayName');
	}

	/**
	 * Get the plugin description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.announcementEmail.description');
	}

	/**
	 * Add announcement content to the noticication email
	 * @param $hookName string
	 * @param $params array
	 */
	function addAnnouncementContent($hookName, $params) {
		$notification =& $params[0];
		if ($notification->getType() === NOTIFICATION_TYPE_NEW_ANNOUNCEMENT){
			$message =& $params[1];
			$announcementId = $notification->getAssocId();
			$announcementDao = DAORegistry::getDAO('AnnouncementDAO');
			$announcement = $announcementDao->getById($announcementId);
			if ($announcement) {
				$templateMgr = TemplateManager::getManager();
				$templateMgr->assign('announcement', $announcement);
				$message = $templateMgr->fetch($this->getTemplateResource('notification/announcementEmail.tpl'));
			}
		}
	}
}
?>
