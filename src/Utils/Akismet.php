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

use Exception;

class Akismet implements AkismetInterface
{
    /**
     * URL to query.
     */
    private string $apiUrl;

    /**
     * User Agent string sent in query.
     */
    private string $userAgent;

    /**
     * The front page or home URL of the instance making the request.
     */
    private string $url;

    /**
     * Last error message. It's null if there is no error.
     */
    private ?string $error;

    /**
     * Constructor checks if cURL extension exists and sets API url.
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (!function_exists('curl_init')) {
            throw new Exception('Akismet cURL connector requires cURL extension');
        }

        $this->apiUrl = sprintf('http://%s/%s/', self::AKISMET_URL, self::AKISMET_API_VERSION);
    }

    /**
     * Checks if Akismet API key is valid.
     *
     * @param string $apiKey Akismet API key
     * @param string $url    The front page or home URL of the instance making the request
     */
    public function keyCheck(string $apiKey, string $url): bool
    {
        $check = $this->query([
            'key' => $apiKey,
            'blog' => $url,
        ], self::PATH_KEY, self::RETURN_VALID);

        if (true === $check) {
            $this->url = $url;
            $this->apiUrl = sprintf('http://%s.%s/%s/', $apiKey, self::AKISMET_URL, self::AKISMET_API_VERSION);

            return true;
        } else {
            return false;
        }
    }

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
    public function sendHam(array $comment): bool
    {
        return $this->query($comment, self::PATH_HAM, self::RETURN_THANKS);
    }

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
    public function sendSpam(array $comment): bool
    {
        return $this->query($comment, self::PATH_SPAM, self::RETURN_THANKS);
    }

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
    public function check(array $comment): bool
    {
        return $this->query($comment, self::PATH_CHECK, self::RETURN_TRUE);
    }

    /**
     * Makes query to Akismet API and checks the response.
     *
     * @param array  $comment Message data. Required keys:<br />
     *                        permalink - the permanent location of the entry the comment was submitted to<br />
     *                        comment_type - may be blank, comment, trackback, pingback, or a made up value like "registration"<br />
     *                        comment_author - name submitted with the comment<br />
     *                        comment_author_email - email address submitted with the comment<br />
     *                        comment_author_url - URL submitted with comment<br />
     *                        comment_content - the content that was submitted
     * @param string $path    API method to use self::PATH_*
     * @param string $expect  Expected response self::RETURN_*
     *
     * @return bool True is response is same as expected
     */
    private function query(array $comment, string $path = self::PATH_CHECK, string $expect = self::RETURN_TRUE): bool
    {
        $this->error = null;

        $conn = curl_init();

        if (self::PATH_KEY !== $path) {
            $comment['blog'] = $this->url;
            if (!array_key_exists('user_ip', $comment)) { // set the user ip if not sent
                $comment['user_ip'] = $_SERVER['REMOTE_ADDR'];
            }

            if (!array_key_exists('user_agent', $comment)) { // set the ua string if not sent
                $comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            }

            if (!array_key_exists('referrer', $comment)) { // set the referer if not set
                $comment['referrer'] = $_SERVER['HTTP_REFERER'];
            }
        }

        $settings = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => sprintf('%s%s', $this->apiUrl, $path),
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_POST => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_POSTFIELDS => http_build_query($comment),
            CURLOPT_HEADER => true,
        ];

        curl_setopt_array($conn, $settings);
        $response = explode("\n", curl_exec($conn));

        if (trim(end($response)) == $expect) {
            return true;
        } else {
            foreach ($response as $header) {
                if (0 === stripos($header, 'X-akismet-debug-help')) {
                    $this->error = trim($header);
                }
            }

            return false;
        }
    }

    /**
     * Sets User Agent string for connection
     * Akismet asks to set UA string like: AppName/Version | PluginName/Version.
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Gets last error occurred.
     *
     * @return string Returns null if there's no error
     */
    public function getError(): string
    {
        return $this->error;
    }
}
