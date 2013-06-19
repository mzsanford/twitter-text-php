<?php
/**
 * @author     Mike Cochrane <mikec@mikenz.geek.nz>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */

/**
 * Twitter Regex Abstract Class
 *
 * Used by subclasses that need to parse tweets.
 *
 * Originally written by {@link http://github.com/mikenz Mike Cochrane}, this
 * is based on code by {@link http://github.com/mzsanford Matt Sanford} and
 * heavily modified by {@link http://github.com/ngnpope Nick Pope}.
 *
 * @author     Mike Cochrane <mikec@mikenz.geek.nz>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 * @package    Twitter
 */
abstract class Twitter_Regex {

  /**
   * Contains all generated regular expressions.
   *
   * @var  string  The regex patterns.
   */
  protected static $patterns = array();

  /**
   * The tweet to be used in parsing.  This should be populated by the
   * constructor of all subclasses.
   *
   * @var  string
   */
  protected $tweet = '';

  /**
   * This constructor is used to populate some variables.
   *
   * @param  string  $tweet  The tweet to parse.
   */
  protected function __construct($tweet) {
    $this->tweet = $tweet;
  }

  /**
   * Emulate a static initialiser while PHP doesn't have one.
   */
  public static function __static() {
    # Check whether we have initialized the regular expressions:
    static $initialized = false;
    if ($initialized) return;
    # Get a shorter reference to the regular expression array:
    $re =& self::$patterns;
    # Initialise local storage arrays:
    $tmp = array();

    # Expression to match whitespace characters.
    #
    #   0x0009-0x000D  Cc # <control-0009>..<control-000D>
    #   0x0020         Zs # SPACE
    #   0x0085         Cc # <control-0085>
    #   0x00A0         Zs # NO-BREAK SPACE
    #   0x1680         Zs # OGHAM SPACE MARK
    #   0x180E         Zs # MONGOLIAN VOWEL SEPARATOR
    #   0x2000-0x200A  Zs # EN QUAD..HAIR SPACE
    #   0x2028         Zl # LINE SEPARATOR
    #   0x2029         Zp # PARAGRAPH SEPARATOR
    #   0x202F         Zs # NARROW NO-BREAK SPACE
    #   0x205F         Zs # MEDIUM MATHEMATICAL SPACE
    #   0x3000         Zs # IDEOGRAPHIC SPACE
    $tmp['spaces'] = '\x{0009}-\x{000D}\x{0020}\x{0085}\x{00a0}\x{1680}\x{180E}\x{2000}-\x{200a}\x{2028}\x{2029}\x{202f}\x{205f}\x{3000}';

    # Invalid Characters:
    #   0xFFFE,0xFEFF # BOM
    #   0xFFFF        # Special
    #   0x202A-0x202E # Directional change
    $tmp['invalid_characters'] = '\x{202a}-\x{202e}\x{feff}\x{fffe}\x{ffff}';

    # Expression to match at and hash sign characters:
    $tmp['at_signs'] = '@＠';
    $tmp['hash_signs'] = '#＃';

    # Expression to match latin accented characters.
    #
    #   0x00C0-0x00D6
    #   0x00D8-0x00F6
    #   0x00F8-0x00FF
    #   0x0100-0x024f
    #   0x0253-0x0254
    #   0x0256-0x0257
    #   0x0259
    #   0x025b
    #   0x0263
    #   0x0268
    #   0x026f
    #   0x0272
    #   0x0289
    #   0x028b
    #   0x02bb
    #   0x0300-0x036f
    #   0x1e00-0x1eff
    #
    # Excludes 0x00D7 - multiplication sign (confusable with 'x').
    # Excludes 0x00F7 - division sign.
    $tmp['latin_accents'] = '\x{00c0}-\x{00d6}\x{00d8}-\x{00f6}\x{00f8}-\x{00ff}';
    $tmp['latin_accents'] .= '\x{0100}-\x{024f}\x{0253}-\x{0254}\x{0256}-\x{0257}';
    $tmp['latin_accents'] .= '\x{0259}\x{025b}\x{0263}\x{0268}\x{026f}\x{0272}\x{0289}\x{028b}\x{02bb}\x{0300}-\x{036f}\x{1e00}-\x{1eff}';

    # Expression to match RTL characters.
    #
    #   0x0600-0x06FF Arabic
    #   0x0750-0x077F Arabic Supplement
    #   0x08A0-0x08FF Arabic Extended-A
    #   0x0590-0x05FF Hebrew
    #   0xFB50-0xFDFF Arabic Presentation Forms-A
    #   0xFE70-0xFEFF Arabic Presentation Forms-B
    $tmp['rtl_chars'] = '\x{0600}-\x{06ff}\x{0750}-\x{077f}\x{08a0}-\x{08ff}\x{0590}-\x{05ff}\x{fb50}-\x{fdff}\x{fe70}-\x{feff}';

    # Expression to match non-latin characters.
    #
    # Cyrillic (Russian, Ukranian, ...):
    #
    #   0x0400-0x04FF Cyrillic
    #   0x0500-0x0527 Cyrillic Supplement
    #   0x2DE0-0x2DFF Cyrillic Extended A
    #   0xA640-0xA69F Cyrillic Extended B
    $tmp['non_latin_hashtag_chars'] = '\x{0400}-\x{04ff}\x{0500}-\x{0527}\x{2de0}-\x{2dff}\x{a640}-\x{a69f}';
    # Hebrew:
    #
    #   0x0591-0x05bf Hebrew
    #   0x05c1-0x05c2
    #   0x05c4-0x05c5
    #   0x05c7
    #   0x05d0-0x05ea
    #   0x05f0-0x05f4
    #   0xfb12-0xfb28 Hebrew Presentation Forms
    #   0xfb2a-0xfb36
    #   0xfb38-0xfb3c
    #   0xfb3e
    #   0xfb40-0xfb41
    #   0xfb43-0xfb44
    #   0xfb46-0xfb4f
    $tmp['non_latin_hashtag_chars'] .= '\x{0591}-\x{05bf}\x{05c1}-\x{05c2}\x{05c4}-\x{05c5}\x{05c7}\x{05d0}-\x{05ea}\x{05f0}-\x{05f4}';
    $tmp['non_latin_hashtag_chars'] .= '\x{fb12}-\x{fb28}\x{fb2a}-\x{fb36}\x{fb38}-\x{fb3c}\x{fb3e}\x{fb40}-\x{fb41}\x{fb43}-\x{fb44}\x{fb46}-\x{fb4f}';
    # Arabic:
    #
    #   0x0610-0x061a Arabic
    #   0x0620-0x065f
    #   0x066e-0x06d3
    #   0x06d5-0x06dc
    #   0x06de-0x06e8
    #   0x06ea-0x06ef
    #   0x06fa-0x06fc
    #   0x06ff
    #   0x0750-0x077f Arabic Supplement
    #   0x08a0        Arabic Extended A
    #   0x08a2-0x08ac
    #   0x08e4-0x08fe
    #   0xfb50-0xfbb1 Arabic Pres. Forms A
    #   0xfbd3-0xfd3d
    #   0xfd50-0xfd8f
    #   0xfd92-0xfdc7
    #   0xfdf0-0xfdfb
    #   0xfe70-0xfe74 Arabic Pres. Forms B
    #   0xfe76-0xfefc
    $tmp['non_latin_hashtag_chars'] .= '\x{0610}-\x{061a}\x{0620}-\x{065f}\x{066e}-\x{06d3}\x{06d5}-\x{06dc}\x{06de}-\x{06e8}\x{06ea}-\x{06ef}\x{06fa}-\x{06fc}\x{06ff}';
    $tmp['non_latin_hashtag_chars'] .= '\x{0750}-\x{077f}\x{08a0}\x{08a2}-\x{08ac}\x{08e4}-\x{08fe}';
    $tmp['non_latin_hashtag_chars'] .= '\x{fb50}-\x{fbb1}\x{fbd3}-\x{fd3d}\x{fd50}-\x{fd8f}\x{fd92}-\x{fdc7}\x{fdf0}-\x{fdfb}\x{fe70}-\x{fe74}\x{fe76}-\x{fefc}';
    #
    #   0x200c-0x200c Zero-Width Non-Joiner
    #   0x0e01-0x0e3a Thai
    $tmp['non_latin_hashtag_chars'] .= '\x{200c}\x{0e01}-\x{0e3a}';
    # Hangul (Korean):
    #
    #   0x0e40-0x0e4e Hangul (Korean)
    #   0x1100-0x11FF Hangul Jamo
    #   0x3130-0x3185 Hangul Compatibility Jamo
    #   0xA960-0xA97F Hangul Jamo Extended A
    #   0xAC00-0xD7AF Hangul Syllables
    #   0xD7B0-0xD7FF Hangul Jamo Extended B
    #   0xFFA1-0xFFDC Half-Width Hangul
    $tmp['non_latin_hashtag_chars'] .= '\x{0e40}-\x{0e4e}\x{1100}-\x{11ff}\x{3130}-\x{3185}\x{a960}-\x{a97f}\x{ac00}-\x{d7af}\x{d7b0}-\x{d7ff}\x{ffa1}-\x{ffdc}';

    # Expression to match other characters.
    #
    #   0x30A1-0x30FA   Katakana (Full-Width)
    #   0x30FC-0x30FE   Katakana (Full-Width)
    #   0xFF66-0xFF9F   Katakana (Half-Width)
    #   0xFF10-0xFF19   Latin (Full-Width)
    #   0xFF21-0xFF3A   Latin (Full-Width)
    #   0xFF41-0xFF5A   Latin (Full-Width)
    #   0x3041-0x3096   Hiragana
    #   0x3099-0x309E   Hiragana
    #   0x3400-0x4DBF   Kanji (CJK Extension A)
    #   0x4E00-0x9FFF   Kanji (Unified)
    #   0x20000-0x2A6DF Kanji (CJK Extension B)
    #   0x2A700-0x2B73F Kanji (CJK Extension C)
    #   0x2B740-0x2B81F Kanji (CJK Extension D)
    #   0x2F800-0x2FA1F Kanji (CJK supplement)
    #   0x3003          Kanji (CJK supplement)
    #   0x3005          Kanji (CJK supplement)
    #   0x303B          Kanji (CJK supplement)
    $tmp['cj_hashtag_characters'] = '\x{30A1}-\x{30FA}\x{30FC}-\x{30FE}\x{FF66}-\x{FF9F}\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}\x{3041}-\x{3096}\x{3099}-\x{309E}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{3003}\x{3005}\x{303B}\x{020000}-\x{02a6df}\x{02a700}-\x{02b73f}\x{02b740}-\x{02b81f}\x{02f800}-\x{02fa1f}';

    $tmp['hashtag_alpha'] = '[a-z_'.$tmp['latin_accents'].$tmp['non_latin_hashtag_chars'].$tmp['cj_hashtag_characters'].']';
    $tmp['hashtag_alphanumeric'] = '[a-z0-9_'.$tmp['latin_accents'].$tmp['non_latin_hashtag_chars'].$tmp['cj_hashtag_characters'].']';
    $tmp['hashtag_boundary'] = '(?:\A|\z|[^&a-z0-9_'.$tmp['latin_accents'].$tmp['non_latin_hashtag_chars'].$tmp['cj_hashtag_characters'].'])';
    $tmp['hashtag'] = '('.$tmp['hashtag_boundary'].')(#|＃)('.$tmp['hashtag_alphanumeric'].'*'.$tmp['hashtag_alpha'].$tmp['hashtag_alphanumeric'].'*)';

    $re['valid_hashtag'] = '/'.$tmp['hashtag'].'(?=(.*|$))/iu';
    $re['end_hashtag_match'] = '/\A(?:['.$tmp['hash_signs'].']|:\/\/)/u';

    # XXX: PHP doesn't have Ruby's $' (dollar apostrophe) so we have to capture
    #      $after in the following regular expression.  Note that we only use a
    #      look-ahead capture here and don't append $after when we return.
    $tmp['valid_mention_preceding_chars'] = '([^a-zA-Z0-9_!#\$%&*@＠\/]|^|RT:?)';
    $re['valid_mentions_or_lists'] = '/'.$tmp['valid_mention_preceding_chars'].'(['.$tmp['at_signs'].'])([a-z0-9_]{1,20})(\/[a-z][a-z0-9_\-]{0,24})?(?=(.*|$))/iu';
    $re['valid_reply'] = '/^(?:['.$tmp['spaces'].'])*['.$tmp['at_signs'].']([a-z0-9_]{1,20})/iu';
    $re['end_mention_match'] = '/\A(?:['.$tmp['at_signs'].']|['.$tmp['latin_accents'].']|:\/\/)/iu';

    # URL related hash regex collection

    $tmp['valid_url_preceding_chars'] = '(?:[^A-Z0-9_'.$tmp['at_signs'].'\$'.$tmp['hash_signs'].'\.'.$tmp['invalid_characters'].']|^)';

    $tmp['domain_valid_chars'] = '[^[:punct:][:space:][:blank:][:cntrl:]'.$tmp['invalid_characters'].$tmp['spaces'].']';
    $tmp['valid_subdomain'] = '(?:(?:'.$tmp['domain_valid_chars'].'(?:[_-]|'.$tmp['domain_valid_chars'].')*)?'.$tmp['domain_valid_chars'].'\.)';
    $tmp['valid_domain_name'] = '(?:(?:'.$tmp['domain_valid_chars'].'(?:[-]|'.$tmp['domain_valid_chars'].')*)?'.$tmp['domain_valid_chars'].'\.)';

    $tmp['valid_gTLD'] = '(?:(?:aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|xxx)(?=[^0-9a-z]|$))';
    $tmp['valid_ccTLD'] = '(?:(?:ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cs|cu|cv|cx|cy|cz|dd|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|ss|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|za|zm|zw)(?=[^0-9a-z]|$))';
    $tmp['valid_punycode'] = '(?:xn--[0-9a-z]+)';

    $tmp['valid_domain'] = '(?:'.$tmp['valid_subdomain'].'*'.$tmp['valid_domain_name']
      .'(?:'.$tmp['valid_gTLD'].'|'.$tmp['valid_ccTLD'].'|'.$tmp['valid_punycode'].'))';

    # Used by the extractor:
    $re['valid_ascii_domain'] = '/(?:(?:[a-z0-9\-_]|'.$tmp['latin_accents'].')+\.)+(?:'.$tmp['valid_gTLD'].'|'.$tmp['valid_ccTLD'].'|'.$tmp['valid_punycode'].')/iu';

    # Used by the extractor for stricter t.co URL extraction:
    $re['valid_tco_url'] = '/^https?:\/\/t\.co\/[a-z0-9]+/i';

    # Used by the extractor to filter out unwanted URLs:
    $re['invalid_short_domain'] = '/^'.$tmp['valid_domain_name'].$tmp['valid_ccTLD'].'$/iu';

    $tmp['valid_port_number'] = '[0-9]+';

    $tmp['valid_general_url_path_chars'] = '[a-z0-9!\*;:=\+\,\.\$\/%#\[\]\-_~&|'.$tmp['latin_accents'].']';
    # Allow URL paths to contain balanced parentheses:
    # 1. Used in Wikipedia URLs, e.g. /Primer_(film)
    # 2. Used in IIS sessions, e.g. /S(dfd346)/
    $tmp['valid_url_balanced_parens'] = '(?:\('.$tmp['valid_general_url_path_chars'].'+\))';
    # Valid end-of-path characters (so /foo. does not gobble the period).
    # 1. Allow =&# for empty URL parameters and other URL-join artifacts.
    $tmp['valid_url_path_ending_chars'] = '(?:[a-z0-9=_#\/\+\-'.$tmp['latin_accents'].']|(?:'.$tmp['valid_url_balanced_parens'].'))';
    # Allow @ in a URL, but only in the middle.  Catch things like http://example.com/@user/
    $tmp['valid_url_path'] = '(?:(?:'
      . $tmp['valid_general_url_path_chars'].'*(?:'
      . $tmp['valid_url_balanced_parens'].' '
      . $tmp['valid_general_url_path_chars'].'*)*'
      . $tmp['valid_url_path_ending_chars'].')|(?:@'
      . $tmp['valid_general_url_path_chars'].'+\/))';

    $tmp['valid_url_query_chars'] = '[a-z0-9!?\*\'\(\);:&=\+\$\/%#\[\]\-_\.,~|]';
    $tmp['valid_url_query_ending_chars'] = '[a-z0-9_&=#\/]';

    $re['valid_url'] = '/(?:'                     # $1 Complete match (preg_match() already matches everything.)
      . '('.$tmp['valid_url_preceding_chars'].')' # $2 Preceding characters
      . '('                                       # $3 Complete URL
      . '(https?:\/\/)?'                          # $4 Protocol (optional)
      . '('.$tmp['valid_domain'].')'              # $5 Domain(s)
      . '(?::('.$tmp['valid_port_number'].'))?'   # $6 Port number (optional)
      . '(\/'.$tmp['valid_url_path'].'*)?'        # $7 URL Path
      . '(\?'.$tmp['valid_url_query_chars'].'*'.$tmp['valid_url_query_ending_chars'].')?' # $8 Query String
      . ')'
      . ')/iux';

    $tmp['cash_signs'] = '\$';
    $tmp['cashtag'] = '[a-z]{1,6}(?:[._][a-z]{1,2})?';
    $re['valid_cashtag'] = '/(^|['.$tmp['spaces'].'])(['.$tmp['cash_signs'].'])('.$tmp['cashtag'].')(?=($|\s|[[:punct:]]))/iu';
    $re['end_cashtag_match'] = '/\A(?:['.$tmp['cash_signs'].']|:\/\/)/u';

    # These URL validation pattern strings are based on the ABNF from RFC 3986
    $tmp['validate_url_unreserved'] = '[a-z0-9\-._~]';
    $tmp['validate_url_pct_encoded'] = '(?:%[0-9a-f]{2})';
    $tmp['validate_url_sub_delims'] = '[!$&\'()*+,;=]';
    $tmp['validate_url_pchar'] = '(?:'.$tmp['validate_url_unreserved'].'|'.$tmp['validate_url_pct_encoded'].'|'.$tmp['validate_url_sub_delims'].'|[:\|@])'; #/iox

    $tmp['validate_url_userinfo'] = '(?:'.$tmp['validate_url_unreserved'].'|'.$tmp['validate_url_pct_encoded'].'|'.$tmp['validate_url_sub_delims'].'|:)*'; #/iox

    $tmp['validate_url_dec_octet'] = '(?:[0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])'; #/i
    $tmp['validate_url_ipv4'] = '(?:'.$tmp['validate_url_dec_octet'].'(?:\.'.$tmp['validate_url_dec_octet'].'){3})'; #/iox

    # Punting on real IPv6 validation for now
    $tmp['validate_url_ipv6'] = '(?:\[[a-f0-9:\.]+\])'; #/i

    # Also punting on IPvFuture for now
    $tmp['validate_url_ip'] = '(?:'.$tmp['validate_url_ipv4'].'|'.$tmp['validate_url_ipv6'].')'; #/iox

    # This is more strict than the rfc specifies
    $tmp['validate_url_subdomain_segment'] = '(?:[a-z0-9](?:[a-z0-9_\-]*[a-z0-9])?)'; #/i
    $tmp['validate_url_domain_segment'] = '(?:[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?)'; #/i
    $tmp['validate_url_domain_tld'] = '(?:[a-z](?:[a-z0-9\-]*[a-z0-9])?)'; #/i
    $tmp['validate_url_domain'] = '(?:(?:'.$tmp['validate_url_subdomain_segment'].'\.)*(?:'.$tmp['validate_url_domain_segment'].'\.)'.$tmp['validate_url_domain_tld'].')'; #/iox

    $tmp['validate_url_host'] = '(?:'.$tmp['validate_url_ip'].'|'.$tmp['validate_url_domain'].')'; #/iox

    # Unencoded internationalized domains - this doesn't check for invalid UTF-8 sequences
    $tmp['validate_url_unicode_subdomain_segment'] = '(?:(?:[a-z0-9]|[^\x00-\x7f])(?:(?:[a-z0-9_\-]|[^\x00-\x7f])*(?:[a-z0-9]|[^\x00-\x7f]))?)'; #/ix
    $tmp['validate_url_unicode_domain_segment'] = '(?:(?:[a-z0-9]|[^\x00-\x7f])(?:(?:[a-z0-9\-]|[^\x00-\x7f])*(?:[a-z0-9]|[^\x00-\x7f]))?)'; #/ix
    $tmp['validate_url_unicode_domain_tld'] = '(?:(?:[a-z]|[^\x00-\x7f])(?:(?:[a-z0-9\-]|[^\x00-\x7f])*(?:[a-z0-9]|[^\x00-\x7f]))?)'; #/ix
    $tmp['validate_url_unicode_domain'] = '(?:(?:'.$tmp['validate_url_unicode_subdomain_segment'].'\.)*(?:'.$tmp['validate_url_unicode_domain_segment'].'\.)'.$tmp['validate_url_unicode_domain_tld'].')'; #/iox

    $tmp['validate_url_unicode_host'] = '(?:'.$tmp['validate_url_ip'].'|'.$tmp['validate_url_unicode_domain'].')'; #/iox

    $tmp['validate_url_port'] = '[0-9]{1,5}';

    $re['validate_url_unicode_authority'] = '/'
      .'(?:('.$tmp['validate_url_userinfo'].')@)?' #  $1 userinfo
      .'('.$tmp['validate_url_unicode_host'].')'   #  $2 host
      .'(?::('.$tmp['validate_url_port'].'))?'     #  $3 port
      .'/iux';

    $re['validate_url_authority'] = '/'
      .'(?:('.$tmp['validate_url_userinfo'].')@)?' #  $1 userinfo
      .'('.$tmp['validate_url_host'].')'           #  $2 host
      .'(?::('.$tmp['validate_url_port'].'))?'     #  $3 port
      .'/ix';

    $re['validate_url_scheme'] = '/(?:[a-z][a-z0-9+\-.]*)/i';
    $re['validate_url_path'] = '/(\/'.$tmp['validate_url_pchar'].'*)*/i';
    $re['validate_url_query'] = '/('.$tmp['validate_url_pchar'].'|\/|\?)*/i';
    $re['validate_url_fragment'] = '/('.$tmp['validate_url_pchar'].'|\/|\?)*/i';

    # Modified version of RFC 3986 Appendix B
    $re['validate_url_unencoded'] = '/^' #  Full URL
      .'(?:'
      .'([^:\/?#]+):\/\/' #  $1 Scheme
      .')?'
      .'([^\/?#]*)'       #  $2 Authority
      .'([^?#]*)'         #  $3 Path
      .'(?:'
      .'\?([^#]*)'        #  $4 Query
      .')?'
      .'(?:'
      .'\#(.*)'           #  $5 Fragment
      .')?$/iux';

    $re['invalid_characters'] = '/['.$tmp['invalid_characters'].']/u';

    $re['rtl_chars'] = '/['.$tmp['rtl_chars'].']/iu';

    # Flag that initialization is complete:
    $initialized = true;
  }

}

# Cause regular expressions to be initialized as soon as this file is loaded:
Twitter_Regex::__static();

################################################################################
# vim:et:ft=php:nowrap:sts=2:sw=2:ts=2
