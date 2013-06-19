<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */

require_once 'Regex.php';

/**
 * Twitter Validator Class
 *
 * Performs "validation" on tweets.
 *
 * Originally written by {@link http://github.com/mikenz Mike Cochrane}, this
 * is based on code by {@link http://github.com/mzsanford Matt Sanford} and
 * heavily modified by {@link http://github.com/ngnpope Nick Pope}.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */
class Twitter_Validation extends Twitter_Regex {

  /**
   * The maximum length of a tweet.
   *
   * @var  int
   */
  const MAX_LENGTH = 140;

  /**
   * The length of a short URL beginning with http:
   *
   * @var  int
   */
  protected $short_url_length = 22;

  /**
   * The length of a short URL beginning with http:
   *
   * @var  int
   */
  protected $short_url_length_https = 23;

  /**
   * Provides fluent method chaining.
   *
   * @param  string  $tweet  The tweet to be validated.
   * @param  mixed   $config Setup short URL length from Twitter API /help/configuration response.
   *
   * @see  __construct()
   *
   * @return  Twitter_Validation
   */
  public static function create($tweet, $config = null) {
    return new self($tweet, $config);
  }

  /**
   * Reads in a tweet to be parsed and validates it.
   *
   * @param  string  $tweet  The tweet to validate.
   */
  public function __construct($tweet, $config = null) {
      parent::__construct($tweet);
      if (!empty($config)) {
        $this->setConfiguration($config);
      }
  }

/**
 * Setup short URL length from Twitter API /help/configuration response
 *
 * @param mixed $config
 * @return \Twitter_Validation
 * @link https://dev.twitter.com/docs/api/1/get/help/configuration
 */
  public function setConfiguration($config) {
    if (is_array($config)) {
      // setup from array
      if (isset($config['short_url_length'])) {
        $this->setShortUrlLength($config['short_url_length']);
      }
      if (isset($config['short_url_length_https'])) {
        $this->setShortUrlLengthHttps($config['short_url_length_https']);
      }
    } elseif (is_object($config)) {
      // setup from object
      if (isset($config->short_url_length)) {
        $this->setShortUrlLength($config->short_url_length);
      }
      if (isset($config->short_url_length_https)) {
        $this->setShortUrlLengthHttps($config->short_url_length_https);
      }
    }

    return $this;
  }

  /**
   * Set the length of a short URL beginning with http:
   *
   * @param mixed $length
   * @return \Twitter_Validation
   */
  public function setShortUrlLength($length) {
    $this->short_url_length = intval($length);
    return $this;
  }

/**
 * Get the length of a short URL beginning with http:
 *
 * @return int
 */
  public function getShortUrlLength() {
    return $this->short_url_length;
  }

  /**
   * Set the length of a short URL beginning with https:
   *
   * @param mixed $length
   * @return \Twitter_Validation
   */
  public function setShortUrlLengthHttps($length) {
    $this->short_url_length_https = intval($length);
    return $this;
  }

/**
 * Get the length of a short URL beginning with https:
 *
 * @return int
 */
  public function getShortUrlLengthHttps() {
    return $this->short_url_length_https;
  }

  /**
   * Check whether a tweet is valid.
   *
   * @return  boolean  Whether the tweet is valid.
   */
  public function validateTweet() {
    $length = mb_strlen($this->tweet);
    if (!$this->tweet || !$length) return false;
    if ($length > self::MAX_LENGTH) return false;
    if (preg_match(self::$patterns['invalid_characters'], $this->tweet)) return false;
    return true;
  }

  /**
   * Check whether a username is valid.
   *
   * @return  boolean  Whether the username is valid.
   */
  public function validateUsername() {
    $length = mb_strlen($this->tweet);
    if (!$this->tweet || !$length) return false;
    $extracted = Twitter_Extractor::create($this->tweet)->extractMentionedUsernames();
    return count($extracted) === 1 && $extracted[0] === substr($this->tweet, 1);
  }

  /**
   * Check whether a list is valid.
   *
   * @return  boolean  Whether the list is valid.
   */
  public function validateList() {
    $length = mb_strlen($this->tweet);
    if (!$this->tweet || !$length) return false;
    preg_match(self::$patterns['valid_mentions_or_lists'], $this->tweet, $matches);
    $matches = array_pad($matches, 5, '');
    return isset($matches) && $matches[1] === '' && $matches[4] && !empty($matches[4]) && $matches[5] === '';
  }

  /**
   * Check whether a hashtag is valid.
   *
   * @return  boolean  Whether the hashtag is valid.
   */
  public function validateHashtag() {
    $length = mb_strlen($this->tweet);
    if (!$this->tweet || !$length) return false;
    $extracted = Twitter_Extractor::create($this->tweet)->extractHashtags();
    return count($extracted) === 1 && $extracted[0] === substr($this->tweet, 1);
  }

  /**
   * Check whether a URL is valid.
   *
   * @param  boolean  $unicode_domains   Consider the domain to be unicode.
   * @param  boolean  $require_protocol  Require a protocol for valid domain?
   *
   * @return  boolean  Whether the URL is valid.
   */
  public function validateURL($unicode_domains = true, $require_protocol = true) {
    $length = mb_strlen($this->tweet);
    if (!$this->tweet || !$length) return false;
    preg_match(self::$patterns['validate_url_unencoded'], $this->tweet, $matches);
    $match = array_shift($matches);
    if (!$matches || $match !== $this->tweet) return false;
    list($scheme, $authority, $path, $query, $fragment) = array_pad($matches, 5, '');
    # Check scheme, path, query, fragment:
    if (($require_protocol && !(
        self::isValidMatch($scheme, self::$patterns['validate_url_scheme'])
        && preg_match('/^https?$/i', $scheme))
      )
      || !self::isValidMatch($path, self::$patterns['validate_url_path'])
      || !self::isValidMatch($query, self::$patterns['validate_url_query'], true)
      || !self::isValidMatch($fragment, self::$patterns['validate_url_fragment'], true)) {
      return false;
    }
    # Check authority:
    $authority_pattern = $unicode_domains ? 'validate_url_unicode_authority' : 'validate_url_authority';
    return self::isValidMatch($authority, self::$patterns[$authority_pattern]);
  }

  /**
   * Determines the length of a tweet.  Takes shortening of URLs into account.
   *
   * @return  int  the length of a tweet.
   */
  public function getLength() {
    $length = mb_strlen($this->tweet);
    $urls_with_indices = Twitter_Extractor::create($this->tweet)->extractURLsWithIndices();
    foreach ($urls_with_indices as $x) {
      $length += $x['indices'][0] - $x['indices'][1];
      $length += stripos($x['url'], 'https://') === 0 ? $this->short_url_length_https : $this->short_url_length;
    }
    return $length;
  }

  /**
   * A helper function to check for a valid match.  Used in URL validation.
   *
   * @param  string   $string    The subject string to test.
   * @param  string   $pattern   The pattern to match against.
   * @param  boolean  $optional  Whether a match is compulsory or not.
   *
   * @return  boolean  Whether an exact match was found.
   */
  protected static function isValidMatch($string, $pattern, $optional = false) {
    $found = preg_match($pattern, $string, $matches);
    if (!$optional) {
      return (($string || $string === '') && $found && $matches[0] === $string);
    } else {
      return !(($string || $string === '') && (!$found || $matches[0] !== $string));
    }
  }

}

################################################################################
# vim:et:ft=php:nowrap:sts=2:sw=2:ts=2
