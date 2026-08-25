<?php

/**
 * Lädt Plugin-Klassen (ohne Composer-Autoload für Plugins\).
 */
$pluginSrc = __DIR__;

require_once $pluginSrc.'/Models/Friendship.php';
require_once $pluginSrc.'/Models/ChatMessage.php';
require_once $pluginSrc.'/Models/E2ePublicKey.php';
require_once $pluginSrc.'/Http/Controllers/ProfileController.php';
