<?php

namespace App\Utils;

define('AJAX_CHAT_URL', '/chat/');
define('AJAX_CHAT_PATH', dirname(__FILE__).'/../../vendor/frug/ajax-chat/chat/');
define('AJAX_CHAT_BANNED', 6);
define('AJAX_CHAT_CUSTOM', 5);
define('AJAX_CHAT_CHATBOT', 4);
define('AJAX_CHAT_ADMIN', 3);
define('AJAX_CHAT_MODERATOR', 2);
define('AJAX_CHAT_USER', 1);
define('AJAX_CHAT_GUEST', 0);

require AJAX_CHAT_PATH.'lib/class/AJAXChat.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatDataBase.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLDataBase.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLQuery.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLiDataBase.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatMySQLiQuery.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatEncoding.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatString.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatFileSystem.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatHTTPHeader.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatLanguage.php';
require AJAX_CHAT_PATH.'lib/class/AJAXChatTemplate.php';
require AJAX_CHAT_PATH.'lib/class/CustomAJAXChat.php';
require AJAX_CHAT_PATH.'lib/class/CustomAJAXChatShoutBox.php';
require AJAX_CHAT_PATH.'lib/class/CustomAJAXChatInterface.php';

if (!class_exists(\CustomAJAXChat::class)) {
    throw new \LogicException("Unable to load class: \CustomAJAXChat.");
}

class Chat extends \CustomAJAXChat
{
    private array $_userData;

    public function __construct($params = [], $userData = [])
    {
        $default = [
            'dbConnection' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'chat',
                'type' => null,
                'link' => null,
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
            'contentType' => null,
            'sessionName' => 'ajax_chat',
            'sessionKeyPrefix' => 'ajaxChat',
            'sessionCookieLifeTime' => 365,
            'sessionCookiePath' => '/',
            'sessionCookieDomain' => null,
            'sessionCookieSecure' => null,
            'defaultChannelName' => 'Public',
            'defaultChannelID' => 0,
            'limitChannelList' => null,
            'privateChannelDiff' => 500000000,
            'privateMessageDiff' => 1000000000,
            'allowPrivateChannels' => true,
            'allowPrivateMessages' => true,
            'privateChannelPrefix' => '[',
            'privateChannelSuffix' => ']',
            'forceAutoLogin' => false,
            'showChannelMessages' => true,
            'chatClosed' => false,
            'timeZoneOffset' => null,
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
            'requestMessagesPriorChannelEnterList' => null,
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
            'logsUserAccessChannelList' => null,
            'socketServerEnabled' => false,
            'socketServerHost' => null,
            'socketServerIP' => '127.0.0.1',
            'socketServerPort' => 1935,
            'socketServerChatID' => 0,
        ];

        $this->_config = array_merge($default, $params);

        $this->_userData = $userData;

        parent::__construct();
    }

    public function getValidLoginUserData()
    {
        if (!empty($this->_userData)) {
            if (425 == $this->_userData['userID']) {// SOXSOXSOX VEUT PAS ÊTRE MODO SUR LE CHAT
                $userRole = AJAX_CHAT_USER;
            } elseif (in_array('ROLE_ADMIN', $this->_userData['userRole']) || in_array('ROLE_SUPER_ADMIN', $this->_userData['userRole'])) {
                $userRole = AJAX_CHAT_ADMIN;
            } elseif (in_array('ROLE_MODERATOR', $this->_userData['userRole']) || 22405 == $this->_userData['userID']) {
                $userRole = AJAX_CHAT_MODERATOR;
            } else {
                $userRole = AJAX_CHAT_USER;
            }

            $userData = $this->_userData;
            $userData['userRole'] = $userRole;

            return $userData;
        }

        return $this->getGuestUser();
    }

    public function &getChannels(): array
    {
        if (null === $this->_channels) {
            $this->_channels = $this->getAllChannels();
        }

        return $this->_channels;
    }

    public function &getAllChannels(): array
    {
        if (null === $this->_allChannels) {
            // Get all existing channels:
            $customChannels = $this->getCustomChannels();

            $defaultChannelFound = false;

            foreach ($customChannels as $key => $value) {
                $forumName = $this->trimChannelName($value);

                $this->_allChannels[$forumName] = $key;

                if ($key == $this->getConfig('defaultChannelID')) {
                    $defaultChannelFound = true;
                }
            }

            if (!$defaultChannelFound) {
                // Add the default channel as first array element to the channel list:
                $this->_allChannels = array_merge(
                    [
                        $this->trimChannelName($this->getConfig('defaultChannelName')) => $this->getConfig('defaultChannelID'),
                    ],
                    $this->_allChannels
                );
            }
        }

        return $this->_allChannels;
    }

    public function &getCustomUsers(): array
    {
        $users = [
            [
                'userRole' => AJAX_CHAT_GUEST,
                'userName' => null,
                'password' => null,
                'channels' => [0],
            ],
        ];

        return $users;
    }

    public function &getCustomChannels(): array
    {
        $channels = ['NinjaTooken'];

        return $channels;
    }

    public function parseCustomCommands($text, $textParts): bool
    {
        switch ($textParts[0]) {
            // Away from keyboard message:
            case '/afk':
                $this->setUserName('/afk '.$this->getUserName());
                $this->updateOnlineList();
                $this->addInfoMessage($this->getUserName(), 'userName');
                $this->setSessionVar('AwayFromKeyboard', true);

                return true;
            default:
                return false;
        }
    }

    public function onNewMessage($text): bool
    {
        if ($this->getSessionVar('AwayFromKeyboard')) {
            $this->setUserName($this->subString($this->getUserName(), 5));
            $this->updateOnlineList();
            $this->addInfoMessage($this->getUserName(), 'userName');
            $this->setSessionVar('AwayFromKeyboard', false);
        }

        return true;
    }
}
