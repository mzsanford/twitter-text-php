<?php
/**
 * Examples for the Twitter Text (PHP Edition) Library.
 *
 * Can be run on command line or in the browser.
 *
 * @author     Mike Cochrane <mikec@mikenz.geek.nz>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */

require_once dirname(__FILE__).'/bootstrap.php';

$browser = (PHP_SAPI != 'cli');

/**
 *
 */
function pretty_format($a) {
  if (is_bool($a)) {
    return $a ? 'true' : 'false';
  }
  if (is_string($a)) {
    return "'${a}'";
  }
  return preg_replace(array(
    "/\n/", '/ +\[/', '/ +\)/', '/Array +\(/', '/(?<!\() \[/', '/\[([^]]+)\]/',
    '/"(\d+)"/', '/(?<=^| )\((?= )/', '/(?<= )\)(?=$| )/',
  ), array(
    ' ', ' [', ' )', '(', ', [', '"$1"', '$1', '[', ']',
  ), print_r($a, true));
}

/**
 *
 */
function output_preamble() {
  global $browser;
  if (!$browser) return;
  echo <<<EOHTML
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>Twitter Text (PHP Edition) Library » Conformance</title>
<style>
body { font-family: sans-serif; font-size: 12px; }
.pass { color: #090; }
.fail { color: #f00; }
</style>
</head>
<body>
EOHTML;
}

/**
 *
 */
function output_closing() {
  global $browser;
  if (!$browser) return;
  echo <<<EOHTML
</body>
</html>
EOHTML;
}

/**
 *
 */
function output_h1($text) {
  global $browser;
  if ($browser) {
    echo '<h1>' . $text . '</h1>';
  } else {
    echo "\033[1m" . $text . "\033[0m" . PHP_EOL;
    echo str_repeat('=', mb_strlen($text)) . PHP_EOL;
  }
  echo PHP_EOL;
}

/**
 *
 */
function output_h2($text) {
  global $browser;
  if ($browser) {
    echo '<h2>' . $text . '</h2>';
  } else {
    echo "\033[1m" . $text . "\033[0m" . PHP_EOL;
    echo str_repeat('-', mb_strlen($text)) . PHP_EOL;
  }
  echo PHP_EOL;
}

/**
 *
 */
function output_h3($text) {
  global $browser;
  if ($browser) {
    echo '<h3>' . $text . '</h3>';
  } else {
    echo "\033[1m" . $text . "\033[0m" . PHP_EOL;
  }
  echo PHP_EOL;
}

/**
 *
 */
function output_skip_test() {
  global $browser;
  $text = 'Skipping Test...';
  if ($browser) {
    echo '<p>' . $text. '</p>';
  } else {
    echo "   \033[1;35m". $text . "\033[0m" . PHP_EOL;
  }
  echo PHP_EOL;
}

$pass_total = 0;
$fail_total = 0;
$pass_group = 0;
$fail_group = 0;

output_preamble();

output_h1('Twitter Text (PHP Edition) Library » Conformance');

output_h2('Extraction Conformance');

# Load the test data.
$data = Spyc::YAMLLoad($DATA.'/extract.yml');

# Define the functions to be tested.
$functions = array(
  'hashtags' => 'extractHashtags',
  'cashtags' => 'extractCashtags',
  'urls'     => 'extractURLs',
  'mentions' => 'extractMentionedUsernames',
  'replies'  => 'extractRepliedUsernames',
  'hashtags_with_indices' => 'extractHashtagsWithIndices',
  'cashtags_with_indices' => 'extractCashtagsWithIndices',
  'urls_with_indices'     => 'extractURLsWithIndices',
  'mentions_with_indices' => 'extractMentionedUsernamesWithIndices',
  'mentions_or_lists_with_indices' => 'extractMentionedUsernamesOrListsWithIndices',
);

# Perform testing.
foreach ($data['tests'] as $group => $tests) {

  output_h3('Test Group - '.ucfirst(str_replace('_', ' ', $group)));

  if (!array_key_exists($group, $functions)) {
    output_skip_test();
    continue;
  }
  $function = $functions[$group];
  $pass_group = 0;
  $fail_group = 0;
  if ($browser) echo '<ul>', PHP_EOL;
  foreach ($tests as $test) {
    echo ($browser ? '<li>' : ' - ');
    echo (isset($test['description']) ? $test['description'] : '???'), ' ... ';
    $extracted = Twitter_Extractor::create($test['text'])->$function();
    if ($test['expected'] == $extracted) {
      $pass_group++;
      echo ($browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m");
    } else {
      $fail_group++;
      echo ($browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m");
      if ($browser) {
        echo '<pre>';
        echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
        echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo 'Actual:   '.pretty_format($extracted);
        echo '</pre>';
      } else {
        echo PHP_EOL, PHP_EOL;
        echo '   Original: '.$test['text'], PHP_EOL;
        echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo '   Actual:   '.pretty_format($extracted), PHP_EOL;
      }
    }
    if ($browser) echo '</li>';
    echo PHP_EOL;
  }
  if ($browser) echo '</ul>';
  echo PHP_EOL;
  $pass_total += $pass_group;
  $fail_total += $fail_group;
  echo ($browser ? '<p class="group">' : "   \033[1;33m");
  printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
  echo ($browser ? '</p>' : "\033[0m".PHP_EOL);
  echo PHP_EOL;
}

output_h2('Autolink Conformance');

# Load the test data.
$data = Spyc::YAMLLoad($DATA.'/autolink.yml');

# Define the functions to be tested.
$functions = array(
  'usernames' => 'addLinksToUsernamesAndLists',
  'lists'     => 'addLinksToUsernamesAndLists',
  'hashtags'  => 'addLinksToHashtags',
  'cashtags'  => 'addLinksToCashtags',
  'urls'      => 'addLinksToURLs',
  'all'       => 'addLinks',
);

# Perform testing.
foreach ($data['tests'] as $group => $tests) {

  output_h3('Test Group - '.ucfirst(str_replace('_', ' ', $group)));

  if (!array_key_exists($group, $functions)) {
    output_skip_test();
    continue;
  }
  $function = $functions[$group];
  $pass_group = 0;
  $fail_group = 0;
  if ($browser) echo '<ul>', PHP_EOL;
  foreach ($tests as $test) {
    echo ($browser ? '<li>' : ' - ');
    echo (isset($test['description']) ? $test['description'] : '???'), ' ... ';
    $linked = Twitter_Autolink::create($test['text'], false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setCashtagClass('tweet-url cashtag')
      ->setURLClass('')
      ->$function();
    # XXX: Need to re-order for hashtag as it is written out differently...
    #      We use the same wrapping function for adding links for all methods.
    if ($group == 'hashtags') {
      $linked = preg_replace(array(
        '!<a class="([^"]*)" href="([^"]*)">([^<]*)</a>!',
        '!title="＃([^"]+)"!'
      ), array(
        '<a href="$2" title="$3" class="$1">$3</a>',
        'title="#$1"'
      ), $linked);
    }
    if ($test['expected'] == $linked) {
      $pass_group++;
      echo ($browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m");
    } else {
      $fail_group++;
      echo ($browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m");
      if ($browser) {
        echo '<pre>';
        echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
        echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo 'Actual:   '.pretty_format($linked);
        echo '</pre>';
      } else {
        echo PHP_EOL, PHP_EOL;
        echo '   Original: '.$test['text'], PHP_EOL;
        echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo '   Actual:   '.pretty_format($linked), PHP_EOL;
      }
    }
    if ($browser) echo '</li>';
    echo PHP_EOL;
  }
  if ($browser) echo '</ul>';
  echo PHP_EOL;
  $pass_total += $pass_group;
  $fail_total += $fail_group;
  echo ($browser ? '<p class="group">' : "   \033[1;33m");
  printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
  echo ($browser ? '</p>' : "\033[0m".PHP_EOL);
  echo PHP_EOL;
}

output_h2('Hit Highlighter Conformance');

# Load the test data.
$data = Spyc::YAMLLoad($DATA.'/hit_highlighting.yml');

# Define the functions to be tested.
$functions = array(
  'plain_text' => 'addHitHighlighting',
  'with_links' => 'addHitHighlighting',
);

# Perform testing.
foreach ($data['tests'] as $group => $tests) {

  output_h3('Test Group - '.ucfirst(str_replace('_', ' ', $group)));

  if (!array_key_exists($group, $functions)) {
    output_skip_test();
    continue;
  }
  $function = $functions[$group];
  $pass_group = 0;
  $fail_group = 0;
  if ($browser) echo '<ul>', PHP_EOL;
  foreach ($tests as $test) {
    echo ($browser ? '<li>' : ' - ');
    echo (isset($test['description']) ? $test['description'] : '???'), ' ... ';
    $highlighted = Twitter_HitHighlighter::create($test['text'])->$function($test['hits']);
    if ($test['expected'] == $highlighted) {
      $pass_group++;
      echo ($browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m");
    } else {
      $fail_group++;
      echo ($browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m");
      if ($browser) {
        echo '<pre>';
        echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
        echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo 'Actual:   '.pretty_format($highlighted);
        echo '</pre>';
      } else {
        echo PHP_EOL, PHP_EOL;
        echo '   Original: '.$test['text'], PHP_EOL;
        echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo '   Actual:   '.pretty_format($highlighted), PHP_EOL;
      }
    }
    if ($browser) echo '</li>';
    echo PHP_EOL;
  }
  if ($browser) echo '</ul>';
  echo PHP_EOL;
  $pass_total += $pass_group;
  $fail_total += $fail_group;
  echo ($browser ? '<p class="group">' : "   \033[1;33m");
  printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
  echo ($browser ? '</p>' : "\033[0m".PHP_EOL);
  echo PHP_EOL;
}

output_h2('Validation Conformance');

# Load the test data.
$data = Spyc::YAMLLoad($DATA.'/validate.yml');

# Define the functions to be tested.
$functions = array(
  'tweets' => 'validateTweet',
  'usernames' => 'validateUsername',
  'lists' => 'validateList',
  'hashtags' => 'validateHashtag',
  'urls' => 'validateURL',
  'urls_without_protocol' => 'validateURL',
  'lengths' => 'getLength',
);

# Perform testing.
foreach ($data['tests'] as $group => $tests) {

  output_h3('Test Group - '.ucfirst(str_replace('_', ' ', $group)));

  if (!array_key_exists($group, $functions)) {
    output_skip_test();
    continue;
  }
  $function = $functions[$group];
  $pass_group = 0;
  $fail_group = 0;
  if ($browser) echo '<ul>', PHP_EOL;
  foreach ($tests as $test) {
    echo ($browser ? '<li>' : ' - ');
    echo (isset($test['description']) ? $test['description'] : '???'), ' ... ';
    $validator = Twitter_Validation::create($test['text']);
    if ($group === 'urls_without_protocol') {
      $validated = $validator->$function(true, false);
    } else {
      $validated = $validator->$function();
    }
    if ($test['expected'] == $validated) {
      $pass_group++;
      echo ($browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m");
    } else {
      $fail_group++;
      echo ($browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m");
      if ($browser) {
        echo '<pre>';
        echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
        echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo 'Actual:   '.pretty_format($validated). '';
        echo '</pre>';
      } else {
        echo PHP_EOL, PHP_EOL;
        echo '   Original: '.$test['text'], PHP_EOL;
        echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
        echo '   Actual:   '.pretty_format($validated), PHP_EOL;
      }
    }
    if ($browser) echo '</li>';
    echo PHP_EOL;
  }
  if ($browser) echo '</ul>';
  echo PHP_EOL;
  $pass_total += $pass_group;
  $fail_total += $fail_group;
  echo ($browser ? '<p class="group">' : "   \033[1;33m");
  printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
  echo ($browser ? '</p>' : "\033[0m".PHP_EOL);
  echo PHP_EOL;
}

echo ($browser ? '<p class="total">' : "   \033[1;36m");
printf('Total Results: %d passes, %d failures', $pass_total, $fail_total);
echo ($browser ? '</p>' : "\033[0m".PHP_EOL);
echo PHP_EOL;

output_closing();

################################################################################
# vim:et:ft=php:nowrap:sts=2:sw=2:ts=2
