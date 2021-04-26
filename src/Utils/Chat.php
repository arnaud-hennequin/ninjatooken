<?php

namespace App\Utils;

define('AJAX_CHAT_URL',        '/chat/');
define('AJAX_CHAT_PATH',        dirname(__FILE__) . '/../../vendor/frug/ajax-chat/chat/');
define('AJAX_CHAT_BANNED',      6);
define('AJAX_CHAT_CUSTOM',      5);
define('AJAX_CHAT_CHATBOT',     4);
define('AJAX_CHAT_ADMIN',       3);
define('AJAX_CHAT_MODERATOR',   2);
define('AJAX_CHAT_USER',        1);
define('AJAX_CHAT_GUEST',       0);

require(AJAX_CHAT_PATH.'lib/class/AJAXChat.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatDataBase.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLDataBase.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLQuery.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLiDataBase.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLiQuery.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatEncoding.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatString.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatFileSystem.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatHTTPHeader.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatLanguage.php');
require(AJAX_CHAT_PATH.'lib/class/AJAXChatTemplate.php');
require(AJAX_CHAT_PATH.'lib/class/CustomAJAXChat.php');
require(AJAX_CHAT_PATH.'lib/class/CustomAJAXChatShoutBox.php');
require(AJAX_CHAT_PATH.'lib/class/CustomAJAXChatInterface.php');

class Chat extends \CustomAJAXChat
{
    private $_userData = [];

    public function __construct($params = [], $userData = [])
    {
        $default = [
            'dbConnection' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'chat',
                'type' => NULL,
                'link' => NULL,
            ],
            'dbTableNames' => [
                'online' => 'ajax_chat_online',
                'messages' => 'ajax_chat_messages',
                'bans' => 'ajax_chat_bans',
                'invitations' => 'ajax_chat_invitations',
            ],
            'langAvailable' => [
                0 => 'ar',
                1 => 'bg',
                2 => 'ca',
                3 => 'cy',
                4 => 'cz',
                5 => 'da',
                6 => 'de',
                7 => 'el',
                8 => 'en',
                9 => 'es',
                10 => 'et',
                11 => 'fa',
                12 => 'fi',
                13 => 'fr',
                14 => 'gl',
                15 => 'he',
                16 => 'hr',
                17 => 'hu',
                18 => 'in',
                19 => 'it',
                20 => 'ja',
                21 => 'ka',
                22 => 'kr',
                23 => 'mk',
                24 => 'nl',
                25 => 'nl-be',
                26 => 'no',
                27 => 'pl',
                28 => 'pt-br',
                29 => 'pt-pt',
                30 => 'ro',
                31 => 'ru',
                32 => 'sk',
                33 => 'sl',
                34 => 'sr',
                35 => 'sv',
                36 => 'th',
                37 => 'tr',
                38 => 'uk',
                39 => 'zh',
                40 => 'zh-tw',
            ],
            'langDefault' => 'en',
            'langNames' => [
                'ar' => 'عربي',
                'bg' => 'Български',
                'ca' => 'Català',
                'cy' => 'Cymraeg',
                'cz' => 'Česky',
                'da' => 'Dansk',
                'de' => 'Deutsch',
                'el' => 'Ελληνικα',
                'en' => 'English',
                'es' => 'Español',
                'et' => 'Eesti',
                'fa' => 'فارسی',
                'fi' => 'Suomi',
                'fr' => 'Français',
                'gl' => 'Galego',
                'he' => 'עברית',
                'hr' => 'Hrvatski',
                'hu' => 'Magyar',
                'in' => 'Bahasa Indonesia',
                'it' => 'Italiano',
                'ja' => '日本語',
                'ka' => 'ქართული',
                'kr' => '한 글',
                'mk' => 'Македонски',
                'nl' => 'Nederlands',
                'nl-be' => 'Nederlands (België)',
                'no' => 'Norsk',
                'pl' => 'Polski',
                'pt-br' => 'Português (Brasil)',
                'pt-pt' => 'Português (Portugal)',
                'ro' => 'România',
                'ru' => 'Русский',
                'sk' => 'Slovenčina',
                'sl' => 'Slovensko',
                'sr' => 'Srpski',
                'sv' => 'Svenska',
                'th' => '&#x0e20;&#x0e32;&#x0e29;&#x0e32;&#x0e44;&#x0e17;&#x0e22;',
                'tr' => 'Türkçe',
                'uk' => 'Українська',
                'zh' => '中文 (简体)',
                'zh-tw' => '中文 (繁體)',
            ],
            'styleAvailable' => [
                0 => 'beige',
                1 => 'black',
                2 => 'grey',
                3 => 'Oxygen',
                4 => 'Lithium',
                5 => 'Sulfur',
                6 => 'Cobalt',
                7 => 'Mercury',
                8 => 'Uranium',
                9 => 'Pine',
                10 => 'Plum',
                11 => 'prosilver',
                12 => 'Core',
                13 => 'MyBB',
                14 => 'vBulletin',
                15 => 'XenForo',
            ],
            'styleDefault' => 'prosilver',
            'contentEncoding' => 'UTF-8',
            'sourceEncoding' => 'UTF-8',
            'contentType' => NULL,
            'sessionName' => 'ajax_chat',
            'sessionKeyPrefix' => 'ajaxChat',
            'sessionCookieLifeTime' => 365,
            'sessionCookiePath' => '/',
            'sessionCookieDomain' => NULL,
            'sessionCookieSecure' => NULL,
            'defaultChannelName' => 'Public',
            'defaultChannelID' => 0,
            'limitChannelList' => NULL,
            'privateChannelDiff' => 500000000,
            'privateMessageDiff' => 1000000000,
            'allowPrivateChannels' => true,
            'allowPrivateMessages' => true,
            'privateChannelPrefix' => '[',
            'privateChannelSuffix' => ']',
            'forceAutoLogin' => false,
            'showChannelMessages' => true,
            'chatClosed' => false,
            'timeZoneOffset' => NULL,
            'openingHour' => 0,
            'closingHour' => 24,
            'openingWeekDays' => [
                0 => 0,
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
            ],
            'allowGuestLogins' => true,
            'allowGuestWrite' => true,
            'allowGuestUserName' => true,
            'guestUserPrefix' => '(',
            'guestUserSuffix' => ')',
            'minGuestUserID' => 400000000,
            'allowNickChange' => true,
            'changedNickPrefix' => '(',
            'changedNickSuffix' => ')',
            'allowUserMessageDelete' => true,
            'chatBotID' => 2147483647,
            'chatBotName' => 'ChatBot',
            'inactiveTimeout' => 2,
            'inactiveCheckInterval' => 5,
            'requestMessagesPriorChannelEnter' => true,
            'requestMessagesPriorChannelEnterList' => NULL,
            'requestMessagesTimeDiff' => 24,
            'requestMessagesLimit' => 10,
            'maxUsersLoggedIn' => 100,
            'userNameMaxLength' => 16,
            'messageTextMaxLength' => 1040,
            'maxMessageRate' => 20,
            'defaultBanTime' => 5,
            'logoutData' => './?logout=true',
            'ipCheck' => true,
            'logsRequestMessagesTimeDiff' => 1,
            'logsRequestMessagesLimit' => 10,
            'logsFirstYear' => 2007,
            'logsPurgeLogs' => false,
            'logsPurgeTimeDiff' => 365,
            'logsUserAccess' => false,
            'logsUserAccessChannelList' => NULL,
            'socketServerEnabled' => false,
            'socketServerHost' => NULL,
            'socketServerIP' => '127.0.0.1',
            'socketServerPort' => 1935,
            'socketServerChatID' => 0
        ];

        $this->_config = array_merge($default, $params);

        $this->_userData = $userData;

        parent::__construct();
    }

    public function initConfig()
    {
        return;
    }

    public function getValidLoginUserData()
    {
        if (!empty($this->_userData)) {
            // SOXSOXSOX VEUT PAS ÊTRE MODO SUR LE CHAT
            if ($userData['userID'] == 425) {
                $userRole   = AJAX_CHAT_USER;
            } else if (in_array('ROLE_ADMIN', $this->userData['userRole']) || in_array('ROLE_SUPER_ADMIN', $this->userData['userRole'])) {
                $userRole   = AJAX_CHAT_ADMIN;
            } else if (in_array('ROLE_MODERATOR', $this->userData['userRole']) || $userData['userID']==22405) {
                $userRole   = AJAX_CHAT_MODERATOR;
            } else {
                $userRole   = AJAX_CHAT_USER;
            }

            $userData['userRole']   = $userRole;

            return $this->_userData;
        }
        return $this->getGuestUser();
    }
}
