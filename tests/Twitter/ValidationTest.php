<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */

/**
 * Twitter Validation Class Unit Tests
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */
class Twitter_ValidationTest extends PHPUnit_Framework_TestCase {

  /**
   * A helper function for providers.
   *
   * @param  string  $test  The test to fetch data for.
   *
   * @return  array  The test data to provide.
   */
  protected function providerHelper($test) {
    $data = Spyc::YAMLLoad(DATA.'/validate.yml');
    return isset($data['tests'][$test]) ? $data['tests'][$test] : array();
  }

  public function testConfiglationFromArray() {
    $validator = Twitter_Validation::create('', array(
        'short_url_length' => 22,
        'short_url_length_https' => 23,
    ));
    $this->assertSame(22, $validator->getShortUrlLength());
    $this->assertSame(23, $validator->getShortUrlLengthHttps());
  }

  public function testConfiglationFromObject() {
    $conf = new stdClass();
    $conf->short_url_length = 22;
    $conf->short_url_length_https = 23;
    $validator = Twitter_Validation::create('', $conf);
    $this->assertSame(22, $validator->getShortUrlLength());
    $this->assertSame(23, $validator->getShortUrlLengthHttps());
  }

  /**
   * @dataProvider  validateTweetProvider
   */
  public function testValidateTweet($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->validateTweet();
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function validateTweetProvider() {
    return $this->providerHelper('tweets');
  }

  /**
   * @dataProvider  validateUsernameProvider
   */
  public function testValidateUsername($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->validateUsername();
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function validateUsernameProvider() {
    return $this->providerHelper('usernames');
  }

  /**
   * @dataProvider  validateListProvider
   */
  public function testValidateList($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->validateList();
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function validateListProvider() {
    return $this->providerHelper('lists');
  }

  /**
   * @dataProvider  validateHashtagProvider
   */
  public function testValidateHashtag($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->validateHashtag();
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function validateHashtagProvider() {
    return $this->providerHelper('hashtags');
  }

  /**
   * @dataProvider  validateURLProvider
   */
  public function testValidateURL($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->validateURL();
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function validateURLProvider() {
    return $this->providerHelper('urls');
  }

  /**
   * @dataProvider  validateURLWithoutProtocolProvider
   */
  public function testValidateURLWithoutProtocol($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->validateURL(true, false);
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function validateURLWithoutProtocolProvider() {
    return $this->providerHelper('urls_without_protocol');
  }

  /**
   * @dataProvider  getLengthProvider
   */
  public function testGetLength($description, $text, $expected) {
    $validated = Twitter_Validation::create($text)->getLength();
    $this->assertSame($expected, $validated, $description);
  }

  /**
   *
   */
  public function getLengthProvider() {
    return $this->providerHelper('lengths');
  }

}

################################################################################
# vim:et:ft=php:nowrap:sts=2:sw=2:ts=2
