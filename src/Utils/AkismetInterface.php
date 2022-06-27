<?php
/**
 * rzeka.net framework (http://framework.rzeka.net/).
 *
 * @see http://framework.rzeka.net/
 *
 * @copyright (c) 2013, rzeka.net
 * @license http://framework.rzeka.net/license New BSD License
 */

namespace App\Utils;

interface AkismetInterface
{
    /**
     * Base URL of Akismet API.
     */
    public const AKISMET_URL = 'rest.akismet.com';

    /**
     * Akismet API version to use.
     */
    public const AKISMET_API_VERSION = '1.1';

    /*
     * ==========
     * Possible API return values
     * ==========
     */
    public const RETURN_TRUE = 'true';
    public const RETURN_FALSE = 'false';
    public const RETURN_INVALID = 'invalid';
    public const RETURN_VALID = 'valid';
    public const RETURN_THANKS = 'Thanks for making the web a better place.';

    /*
     * ==========
     * API methods
     * ==========
     */

    /**
     * API method to verify key.
     */
    public const PATH_KEY = 'verify-key';

    /**
     * API method to check if message is spam.
     */
    public const PATH_CHECK = 'comment-check';

    /**
     * API method to mark message as spam.
     */
    public const PATH_SPAM = 'submit-spam';

    /**
     * API method to mark message as ham (not-spam).
     */
    public const PATH_HAM = 'submit-ham';

    /**
     * Checks if Akismet API key is valid.
     *
     * @param string $apiKey Akismet API key
     * @param string $url    The front page or home URL of the instance making the request
     */
    public function keyCheck(string $apiKey, string $url): bool;

    /**
     * Marks message as spam.
     *
     * @param array $comment Message data. Required keys:<br />
     *                       permalink - the permanent location of the entry the comment was submitted to<br />
     *                       comment_type - may be blank, comment, trackback, pingback, or a made up value like "registration"<br />
     *                       comment_author - name submitted with the comment<br />
     *                       comment_author_email - email address submitted with the comment<br />
     *                       comment_author_url - URL submitted with comment<br />
     *                       comment_content - the content that was submitted
     *
     * @return bool True if message has been marked as spam
     */
    public function sendSpam(array $comment): bool;

    /**
     * Marks message as ham (not-spam).
     *
     * @param array $comment Message data. Required keys:<br />
     *                       permalink - the permanent location of the entry the comment was submitted to<br />
     *                       comment_type - may be blank, comment, trackback, pingback, or a made up value like "registration"<br />
     *                       comment_author - name submitted with the comment<br />
     *                       comment_author_email - email address submitted with the comment<br />
     *                       comment_author_url - URL submitted with comment<br />
     *                       comment_content - the content that was submitted
     *
     * @return bool True if messahe has been marked as ham
     */
    public function sendHam(array $comment): bool;

    /**
     * Check if message is spam or not.
     *
     * @param array $comment Message data. Required keys:<br />
     *                       permalink - the permanent location of the entry the comment was submitted to<br />
     *                       comment_type - may be blank, comment, trackback, pingback, or a made up value like "registration"<br />
     *                       comment_author - name submitted with the comment<br />
     *                       comment_author_email - email address submitted with the comment<br />
     *                       comment_author_url - URL submitted with comment<br />
     *                       comment_content - the content that was submitted
     *
     * @return bool True if message is spam, false otherwise
     */
    public function check(array $comment): bool;

    /**
     * Sets User Agent string for connection
     * Akismet asks to set UA string like: AppName/Version | PluginName/Version.
     */
    public function setUserAgent(string $userAgent);

    /**
     * Gets last error occurred.
     *
     * @return string Returns null if there's no error
     */
    public function getError(): string;
}
