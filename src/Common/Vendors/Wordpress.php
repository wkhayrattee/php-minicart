<?php
/**
 * This is an extraction of some WordPress functions, to adapt into our system
 * Credit goes to WordPress for the original code.
 *
 * I have changed some/parts of the code to adapt to my system,
 * but still keeping the licensing as GPLv3
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common\Vendors;

/**
 * Class Wordpress
 * @package Wak\Common
 */
class Wordpress
{
    /**
     * Converts a number of special characters into their HTML entities.
     *
     * Specifically deals with: &, <, >, ", and '.
     *
     * $quote_style can be set to ENT_COMPAT to encode " to
     * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
     *
     * @since 1.2.2
     * @access private
     *
     * @param string $string The text which is to be encoded.
     * @param int $quote_style Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
     * @param string $charset Optional. The character encoding of the string. Default is false.
     * @param boolean $double_encode Optional. Whether to encode existing html entities. Default is false.
     * @return string The encoded text with HTML entities.
     */
    protected static function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = "UTF-8", $double_encode = false ) {
        $string = (string) $string;

        if ( 0 === strlen( $string ) )
            return '';

        // Don't bother if there are no specialchars - saves some processing
        if ( ! preg_match( '/[&<>"\']/', $string ) )
            return $string;

        // Account for the previous behaviour of the function when the $quote_style is not an accepted value
        if ( empty( $quote_style ) )
            $quote_style = ENT_NOQUOTES;
        elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) )
            $quote_style = ENT_QUOTES;

        if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ) ) )
            $charset = 'UTF-8';

        $_quote_style = $quote_style;

        if ( $quote_style === 'double' ) {
            $quote_style = ENT_COMPAT;
            $_quote_style = ENT_COMPAT;
        } elseif ( $quote_style === 'single' ) {
            $quote_style = ENT_NOQUOTES;
        }

        // Handle double encoding ourselves
        if ( $double_encode ) {
            $string = @htmlspecialchars( $string, $quote_style, $charset );
        } else {
            // Decode &amp; into &
            $string = self::wp_specialchars_decode( $string, $_quote_style );

            // Guarantee every &entity; is valid or re-encode the &
            $string = self::wp_kses_normalize_entities( $string );

            // Now re-encode everything except &entity;
//            $string = preg_split( '/(&#?x?[0-9a-z]+;)/i', $string, -1, PREG_SPLIT_DELIM_CAPTURE ); ////commented because this did not seem to work for "what you input is what you see"

            for ( $i = 0, $c = count( $string ); $i < $c; $i += 2 ) {
                $string[$i] = @htmlspecialchars( $string[$i], $quote_style, $charset );
            }
            $string = implode( '', $string );
        }

        // Backwards compatibility
        if ( 'single' === $_quote_style )
            $string = str_replace( "'", '&#039;', $string );

        return $string;
    }

    /**
     * Converts a number of HTML entities into their special characters.
     *
     * Specifically deals with: &, <, >, ", and '.
     *
     * $quote_style can be set to ENT_COMPAT to decode " entities,
     * or ENT_QUOTES to do both " and '. Default is ENT_NOQUOTES where no quotes are decoded.
     *
     * @since 2.8.0
     *
     * @param string $string The text which is to be decoded.
     * @param mixed $quote_style Optional. Converts double quotes if set to ENT_COMPAT, both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES. Also compatible with old _wp_specialchars() values; converting single quotes if set to 'single', double if set to 'double' or both if otherwise set. Default is ENT_NOQUOTES.
     * @return string The decoded text without HTML entities.
     */
    protected static function wp_specialchars_decode( $string, $quote_style = ENT_QUOTES ) {
        $string = (string) $string;

        if ( 0 === strlen( $string ) ) {
            return '';
        }

        // Don't bother if there are no entities - saves a lot of processing
        if ( strpos( $string, '&' ) === false ) {
            return $string;
        }

        // Match the previous behaviour of _wp_specialchars() when the $quote_style is not an accepted value
        if ( empty( $quote_style ) ) {
            $quote_style = ENT_NOQUOTES;
        } elseif ( !in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) ) {
            $quote_style = ENT_QUOTES;
        }

        // More complete than get_html_translation_table( HTML_SPECIALCHARS )
        $single = array( '&#039;'  => '\'', '&#x27;' => '\'' );
        $single_preg = array( '/&#0*39;/'  => '&#039;', '/&#x0*27;/i' => '&#x27;' );
        $double = array( '&quot;' => '"', '&#034;'  => '"', '&#x22;' => '"' );
        $double_preg = array( '/&#0*34;/'  => '&#034;', '/&#x0*22;/i' => '&#x22;' );
        $others = array( '&lt;'   => '<', '&#060;'  => '<', '&gt;'   => '>', '&#062;'  => '>', '&amp;'  => '&', '&#038;'  => '&', '&#x26;' => '&' );
        $others_preg = array( '/&#0*60;/'  => '&#060;', '/&#0*62;/'  => '&#062;', '/&#0*38;/'  => '&#038;', '/&#x0*26;/i' => '&#x26;' );

        if ( $quote_style === ENT_QUOTES ) {
            $translation = array_merge( $single, $double, $others );
            $translation_preg = array_merge( $single_preg, $double_preg, $others_preg );
        } elseif ( $quote_style === ENT_COMPAT || $quote_style === 'double' ) {
            $translation = array_merge( $double, $others );
            $translation_preg = array_merge( $double_preg, $others_preg );
        } elseif ( $quote_style === 'single' ) {
            $translation = array_merge( $single, $others );
            $translation_preg = array_merge( $single_preg, $others_preg );
        } elseif ( $quote_style === ENT_NOQUOTES ) {
            $translation = $others;
            $translation_preg = $others_preg;
        }

        // Remove zero padding on numeric entities
        $string = preg_replace( array_keys( $translation_preg ), array_values( $translation_preg ), $string );

        // Replace characters according to translation table
        return strtr( $string, $translation );
    }

    /**
     * Same approach as sanitize_user()
     * But it will only keep alphanumeric, _ and @
     * @param $username
     * @return mixed|string
     */
    protected static function sanitize_to_alphanumeric_plus($username) {
        $username = self::wp_strip_all_tags( $username );
        $username = self::remove_accents( $username );
        // Kill octets
        $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
        $username = preg_replace( '/&.+?;/', '', $username ); // Kill entities

        // If strict, reduce to ASCII for max portability.
        $username = preg_replace( '|[^a-z0-9_@]|i', '', $username );

        $username = trim( $username );
        // Consolidate contiguous whitespace
        $username = preg_replace( '|\s+|', ' ', $username );

        return $username;
    }

    /**
     * Sanitizes a username, stripping out unsafe characters.
     *
     * Removes tags, octets, entities, and if strict is enabled, will only keep
     * alphanumeric, _, space, ., -, @. After sanitizing, it passes the username,
     * raw username (the username in the parameter), and the value of $strict as
     * parameters for the 'sanitize_user' filter.
     *
     * @since 2.0.0
     *
     * @param string $username The username to be sanitized.
     * @param bool $strict If set limits $username to specific characters. Default false.
     * @return string The sanitized username, after passing through filters.
     */
    protected static function sanitize_user( $username, $strict = true ) {
        $username = self::wp_strip_all_tags( $username );
        $username = self::remove_accents( $username );
        // Kill octets
        $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
        $username = preg_replace( '/&.+?;/', '', $username ); // Kill entities

        // If strict, reduce to ASCII for max portability.
        if ( $strict )
            $username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );

        $username = trim( $username );
        // Consolidate contiguous whitespace
        $username = preg_replace( '|\s+|', ' ', $username );

        return $username;
    }

    /**
     * An extension by me from sanitize_user to sanitize for title
     * Removes tags, octets, entities,
     * will only keep:
     * alphanumeric, _, space, ., -, @, ', ", `, &, ?, !, :, *, $, +, (), {}, {}
     * SHOULD NOT
     *     - accept <> because this could create a tag or octet
     *
     * @param $title
     * @return mixed|string
     */
    protected static function sanitize_title($title) {
        $title = self::wp_strip_all_tags( $title );
        $title = self::remove_accents( $title );
        // Kill octets
        $title = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $title );
        $title = preg_replace( '/&.+?;/', '', $title ); // Kill entities

        $title = preg_replace( '|[^a-z0-9 _.\-+@\'\"&:*$(){}[]]|i', '', $title );

        $title = trim( $title );
        // Consolidate contiguous whitespace
        $title = preg_replace( '|\s+|', ' ', $title );

        return $title;
    }

    /**
     * Keep everything, EXCEPT tags, octets, entities
     *
     * @param $message
     * @return mixed|string
     */
    protected static function sanitize_message($message) {
        $message = self::wp_strip_all_tags( $message );
//        $message = self::remove_accents( $message ); //if UTF-8, should be OK - header('Content-Type: text/html; charset=UTF-8');
        // Kill octets
        $message = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $message );
        $message = preg_replace( '/&.+?;/', '', $message ); // Kill entities

        $message = trim( $message );
        // Consolidate contiguous whitespace
//        $message = preg_replace( '|\s+|', ' ', $message );

        return $message;
    }

    /**
     * Sanitizes a username, stripping out unsafe characters.
     *
     * Removes tags, octets, entities, and if strict is enabled, will only keep
     * alphanumeric ony. After sanitizing, it passes the username,
     * raw username (the username in the parameter), and the value of $strict as
     * parameters for the 'sanitize_user' filter.
     *
     * @since 2.0.0
     *
     * @param string $username The username to be sanitized.
     * @return string The sanitized username, after passing through filters.
     */
    protected static function sanitize_alphanumeric( $username) {
        $raw_username = $username;
        $username = self::wp_strip_all_tags( $username );
        $username = self::remove_accents( $username );
        // Kill octets
        $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
        $username = preg_replace( '/&.+?;/', '', $username ); // Kill entities

        $username = preg_replace( '|[^a-z0-9]|i', '', $username );

        $username = trim( $username );
        // Consolidate contiguous whitespace
        $username = preg_replace( '|\s+|', ' ', $username );

        return $username;
    }

    /**
     * Sanitizes a title, replacing whitespace and a few other characters with dashes.
     *
     * Limits the output to alphanumeric characters, underscore (_) and dash (-).
     * Whitespace becomes a dash.
     *
     * @since 1.2.0
     *
     * @param string $title The title to be sanitized.
     * @param string $context Optional. The operation for which the string is sanitized.
     * @return string The sanitized title.
     */
    protected static function sanitize_title_with_dashes( $title, $context = 'save' /* or 'display' */ ) {
        $title = strip_tags($title);
        // Preserve escaped octets.
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        // Remove percent signs that are not part of an octet.
        $title = str_replace('%', '', $title);
        // Restore octets.
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

        $title = strtolower($title);
        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        $title = str_replace('.', '-', $title);

        if ( 'save' == $context ) {
            // Convert nbsp, ndash and mdash to hyphens
            $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );

            // Strip these characters entirely
            $title = str_replace( array(
                // iexcl and iquest
                '%c2%a1', '%c2%bf',
                // angle quotes
                '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
                // curly quotes
                '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
                '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
                // copy, reg, deg, hellip and trade
                '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
                // acute accents
                '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
                // grave accent, macron, caron
                '%cc%80', '%cc%84', '%cc%8c',
            ), '', $title );

            // Convert times to x
            $title = str_replace( '%c3%97', 'x', $title );
        }

        $title = preg_replace('/[^%a-z0-9 -]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);
        $title = preg_replace('|-+|', '-', $title);
        $title = trim($title, '-');

        return $title;
    }

    /**
     * Sanitizes a filename, replacing whitespace with dashes.
     *
     * Removes special characters that are illegal in filenames on certain
     * operating systems and special characters requiring special escaping
     * to manipulate at the command line. Replaces spaces and consecutive
     * dashes with a single dash. Trims period, dash and underscore from beginning
     * and end of filename.
     *
     * @since 2.1.0
     *
     * @param string $filename The filename to be sanitized
     * @return string The sanitized filename
     */
    protected static function sanitize_file_name( $filename ) {
        $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
        /**
         * Filter the list of characters to remove from a filename.
         *
         * @since 2.8.0
         *
         * @param array  $special_chars Characters to remove.
         * @param string $filename_raw  Filename as it was passed into sanitize_file_name().
         */
        $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
        $filename = str_replace( $special_chars, '', $filename );
        $filename = str_replace( array( '%20', '+' ), '-', $filename );
        $filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
        $filename = trim( $filename, '.-_' );

        // Split the filename into a base and extension[s]
        $parts = explode('.', $filename);

        // Return if only one extension
        if ( count( $parts ) <= 2 ) {
            /**
             * Filter a sanitized filename string.
             *
             * @since 2.8.0
             *
             * @param string $filename     Sanitized filename.
             * @param string $filename_raw The filename prior to sanitization.
             */
            return $filename;
        }

        // Process multiple extensions
        $filename = array_shift($parts);
        $extension = array_pop($parts);
        $mimes = self::wp_get_mime_types();

        /*
         * Loop over any intermediate extensions. Postfix them with a trailing underscore
         * if they are a 2 - 5 character long alpha string not in the extension whitelist.
         */
        foreach ( (array) $parts as $part) {
            $filename .= '.' . $part;

            if ( preg_match("/^[a-zA-Z]{2,5}\d?$/", $part) ) {
                $allowed = false;
                foreach ( $mimes as $ext_preg => $mime_match ) {
                    $ext_preg = '!^(' . $ext_preg . ')$!i';
                    if ( preg_match( $ext_preg, $part ) ) {
                        $allowed = true;
                        break;
                    }
                }
                if ( !$allowed )
                    $filename .= '_';
            }
        }
        $filename .= '.' . $extension;
        /** This filter is documented in wp-includes/formatting.php */
        return $filename;
    }

    /**
     * Strips out all characters that are not allowable in an email.
     *
     * @since 1.5.0
     *
     * @param string $email Email address to filter.
     * @return string Filtered email address.
     */
    protected static function sanitize_email( $email ) {
        // Test for the minimum length the email can be
        if ( strlen( $email ) < 3 ) {
            /**
             * Filter a sanitized email address.
             *
             * This filter is evaluated under several contexts, including 'email_too_short',
             * 'email_no_at', 'local_invalid_chars', 'domain_period_sequence', 'domain_period_limits',
             * 'domain_no_periods', 'domain_no_valid_subs', or no context.
             *
             * @since 2.8.0
             *
             * @param string $email   The sanitized email address.
             * @param string $email   The email address, as provided to sanitize_email().
             * @param string $message A message to pass to the user.
             */
            return false; //email_too_short
        }

        // Test for an @ character after the first position
        if ( strpos( $email, '@', 1 ) === false ) {
            /** This filter is documented in wp-includes/formatting.php */
            return false; //email_no_at
        }

        // Split out the local and domain parts
        list( $local, $domain ) = explode( '@', $email, 2 );

        // LOCAL PART
        // Test for invalid characters
        $local = preg_replace( '/[^a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]/', '', $local );
        if ( '' === $local ) {
            /** This filter is documented in wp-includes/formatting.php */
            return false; //local_invalid_chars
        }

        // DOMAIN PART
        // Test for sequences of periods
        $domain = preg_replace( '/\.{2,}/', '', $domain );
        if ( '' === $domain ) {
            /** This filter is documented in wp-includes/formatting.php */
            return false; //domain_period_sequence
        }

        // Test for leading and trailing periods and whitespace
        $domain = trim( $domain, " \t\n\r\0\x0B." );
        if ( '' === $domain ) {
            /** This filter is documented in wp-includes/formatting.php */
            return false; //domain_period_limits
        }

        // Split the domain into subs
        $subs = explode( '.', $domain );

        // Assume the domain will have at least two subs
        if ( 2 > count( $subs ) ) {
            /** This filter is documented in wp-includes/formatting.php */
            return false; //domain_no_periods
        }

        // Create an array that will contain valid subs
        $new_subs = array();

        // Loop through each sub
        foreach ( $subs as $sub ) {
            // Test for leading and trailing hyphens
            $sub = trim( $sub, " \t\n\r\0\x0B-" );

            // Test for invalid characters
            $sub = preg_replace( '/[^a-z0-9-]+/i', '', $sub );

            // If there's anything left, add it to the valid subs
            if ( '' !== $sub ) {
                $new_subs[] = $sub;
            }
        }

        // If there aren't 2 or more valid subs
        if ( 2 > count( $new_subs ) ) {
            /** This filter is documented in wp-includes/formatting.php */
            return false; //domain_no_valid_subs
        }

        // Join valid subs into the new domain
        $domain = join( '.', $new_subs );

        // Put the email back together
        $email = $local . '@' . $domain;

        // Congratulations your email made it!
        /** This filter is documented in wp-includes/formatting.php */
        return $email;
    }

    /**
     * Sanitize a string from user input or from the db
     *
     * check for invalid UTF-8,
     * Convert single < characters to entity,
     * strip all tags,
     * remove line breaks, tabs and extra white space,
     * strip octets.
     *
     * @since 2.9.0
     *
     * @param string $str
     * @param bool $remove_breaks optional Whether to remove left over line breaks and white space chars
     * @return string
     */
    protected static function sanitize_text_field($str, $remove_breaks = false) {
        $filtered = self::wp_check_invalid_utf8( $str );

        if ( strpos($filtered, '<') !== false ) {
            $filtered = self::wp_pre_kses_less_than( $filtered );
            // This will strip extra whitespace for us.
            $filtered = self::wp_strip_all_tags( $filtered, $remove_breaks );
        } else {
            if ($remove_breaks === true) {
                $filtered = trim( preg_replace('/[\r\n\t ]+/', ' ', $filtered) );
            }
        }

        $found = false;
        while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
            $filtered = str_replace($match[0], '', $filtered);
            $found = true;
        }

        if ( $found ) {
            // Strip out the whitespace that may now exist after removing the octets.
            $filtered = trim( preg_replace('/ +/', ' ', $filtered) );
        }

        /**
         * Filter a sanitized text field string.
         *
         * @since 2.9.0
         *
         * @param string $filtered The sanitized string.
         * @param string $str      The string prior to being sanitized.
         */
        return $filtered;
    }

    /**
     * Trims text to a certain number of words.
     *
     * This function is localized. For languages that count 'words' by the individual
     * character (such as East Asian languages), the $num_words argument will apply
     * to the number of individual characters.
     *
     * @since 3.3.0
     *
     * @param string $text Text to trim.
     * @param int $num_words Number of words. Default 55.
     * @param string $more Optional. What to append if $text needs to be trimmed. Default '&hellip;'.
     * @return string Trimmed text.
     */
    protected static function wp_trim_words( $text, $num_words = 55, $more = null ) {
        if ( null === $more )
            $more = '&hellip;';
        $text = self::wp_strip_all_tags( $text );

        $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
        $sep = ' ';
        if ( count( $words_array ) > $num_words ) {
            array_pop( $words_array );
            $text = implode( $sep, $words_array );
            $text = $text . $more;
        } else {
            $text = implode( $sep, $words_array );
        }
        return $text;
    }

    /**
     * Encode the Unicode values to be used in the URI.
     *
     * @since 1.5.0
     *
     * @param string $utf8_string
     * @param int $length Max length of the string
     * @return string String with Unicode encoded for URI.
     */
    protected static function utf8_uri_encode( $utf8_string, $length = 0 ) {
        $unicode = '';
        $values = array();
        $num_octets = 1;
        $unicode_length = 0;

        self::mbstring_binary_safe_encoding();
        $string_length = strlen( $utf8_string );
        self::reset_mbstring_encoding();

        for ($i = 0; $i < $string_length; $i++ ) {

            $value = ord( $utf8_string[ $i ] );

            if ( $value < 128 ) {
                if ( $length && ( $unicode_length >= $length ) )
                    break;
                $unicode .= chr($value);
                $unicode_length++;
            } else {
                if ( count( $values ) == 0 ) {
                    if ( $value < 224 ) {
                        $num_octets = 2;
                    } elseif ( $value < 240 ) {
                        $num_octets = 3;
                    } else {
                        $num_octets = 4;
                    }
                }

                $values[] = $value;

                if ( $length && ( $unicode_length + ($num_octets * 3) ) > $length )
                    break;
                if ( count( $values ) == $num_octets ) {
                    for ( $j = 0; $j < $num_octets; $j++ ) {
                        $unicode .= '%' . dechex( $values[ $j ] );
                    }

                    $unicode_length += $num_octets * 3;

                    $values = array();
                    $num_octets = 1;
                }
            }
        }

        return $unicode;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @since 1.2.1
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    protected static function remove_accents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        if (self::seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
                chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
                chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
                chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
                chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
                chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
                chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
                chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
                chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
                chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
                chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
                chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
                chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
                chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
                chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
                chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
                chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
                chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
                chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
                chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
                chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
                chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
                chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
                chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
                chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
                chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
                chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
                chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
                chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
                chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
                chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
                chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
                // Decompositions for Latin Extended-A
                chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
                chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
                chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
                chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
                chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
                chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
                chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
                chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
                chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
                chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
                chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
                chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
                chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
                chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
                chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
                chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
                chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
                chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
                chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
                chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
                chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
                chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
                chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
                chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
                chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
                chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
                chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
                chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
                chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
                chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
                chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
                chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
                chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
                chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
                chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
                chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
                chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
                chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
                chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
                chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
                chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
                chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
                chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
                chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
                chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
                chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
                chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
                chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
                chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
                chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
                chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
                chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
                chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
                chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
                chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
                chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
                chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
                chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
                chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
                chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
                chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
                chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
                chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
                chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
                // Decompositions for Latin Extended-B
                chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
                chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
                // Euro Sign
                chr(226).chr(130).chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194).chr(163) => '',
                // Vowels with diacritic (Vietnamese)
                // unmarked
                chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
                chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
                // grave accent
                chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
                chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
                chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
                chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
                chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
                chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
                chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
                // hook
                chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
                chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
                chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
                chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
                chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
                chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
                chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
                chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
                chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
                chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
                chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
                chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
                // tilde
                chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
                chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
                chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
                chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
                chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
                chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
                chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
                chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
                // acute accent
                chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
                chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
                chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
                chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
                chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
                chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
                // dot below
                chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
                chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
                chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
                chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
                chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
                chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
                chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
                chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
                chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
                chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
                chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
                chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
                // Vowels with diacritic (Chinese, Hanyu Pinyin)
                chr(201).chr(145) => 'a',
                // macron
                chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
                // acute accent
                chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
                // caron
                chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
                chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
                chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
                chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
                chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
                // grave accent
                chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
            );

            $string = strtr($string, $chars);
        } else {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                .chr(252).chr(253).chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    /**
     * Checks to see if a string is utf8 encoded.
     *
     * NOTE: This function checks for 5-Byte sequences, UTF8
     *       has Bytes Sequences with a maximum length of 4.
     *
     * @author bmorel at ssi dot fr (modified)
     * @since 1.2.1
     *
     * @param string $str The string to be checked
     * @return bool True if $str fits a UTF-8 model, false otherwise.
     */
    protected static function seems_utf8($str) {
        self::mbstring_binary_safe_encoding();
        $length = strlen($str);
        self::reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
            else return false; // Does not match any model
            for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Set the mbstring internal encoding to a binary safe encoding when func_overload
     * is enabled.
     *
     * When mbstring.func_overload is in use for multi-byte encodings, the results from
     * strlen() and similar functions respect the utf8 characters, causing binary data
     * to return incorrect lengths.
     *
     * This function overrides the mbstring encoding to a binary-safe encoding, and
     * resets it to the users expected encoding afterwards through the
     * `reset_mbstring_encoding` function.
     *
     * It is safe to recursively call this function, however each
     * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
     * of `reset_mbstring_encoding()` calls.
     *
     * @since 3.7.0
     *
     * @see reset_mbstring_encoding()
     *
     * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
     *                    Default false.
     */
    protected static function mbstring_binary_safe_encoding( $reset = false ) {
        static $encodings = array();
        static $overloaded = null;

        if ( is_null( $overloaded ) )
            $overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );

        if ( false === $overloaded )
            return;

        if ( ! $reset ) {
            $encoding = mb_internal_encoding();
            array_push( $encodings, $encoding );
            mb_internal_encoding( 'ISO-8859-1' );
        }

        if ( $reset && $encodings ) {
            $encoding = array_pop( $encodings );
            mb_internal_encoding( $encoding );
        }
    }

    /**
     * Reset the mbstring internal encoding to a users previously set encoding.
     *
     * @see mbstring_binary_safe_encoding()
     *
     * @since 3.7.0
     */
    protected static function reset_mbstring_encoding() {
        self::mbstring_binary_safe_encoding( true );
    }

    /**
     * Converts and fixes HTML entities.
     *
     * This function normalizes HTML entities. It will convert `AT&T` to the correct
     * `AT&amp;T`, `&#00058;` to `&#58;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
     *
     * @since 1.0.0
     *
     * @param string $string Content to normalize entities
     * @return string Content with normalized entities
     */
    protected static function wp_kses_normalize_entities($string) {
        // Disarm all entities by converting & to &amp;

        $string = str_replace('&', '&amp;', $string);

        // Change back the allowed entities in our entity whitelist

        $string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'self::wp_kses_named_entities', $string);
        $string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/', 'self::wp_kses_normalize_entities2', $string);
        $string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'self::wp_kses_normalize_entities3', $string);

        return $string;
    }

    /**
     * Callback for wp_kses_normalize_entities() regular expression.
     *
     * This function only accepts valid named entity references, which are finite,
     * case-sensitive, and highly scrutinized by HTML and XML validators.
     *
     * @since 3.0.0
     *
     * @param array $matches preg_replace_callback() matches array
     * @return string Correctly encoded entity
     */
    protected static function wp_kses_named_entities($matches) {
        $allowedentitynames = array(
            'nbsp',    'iexcl',  'cent',    'pound',  'curren', 'yen',
            'brvbar',  'sect',   'uml',     'copy',   'ordf',   'laquo',
            'not',     'shy',    'reg',     'macr',   'deg',    'plusmn',
            'acute',   'micro',  'para',    'middot', 'cedil',  'ordm',
            'raquo',   'iquest', 'Agrave',  'Aacute', 'Acirc',  'Atilde',
            'Auml',    'Aring',  'AElig',   'Ccedil', 'Egrave', 'Eacute',
            'Ecirc',   'Euml',   'Igrave',  'Iacute', 'Icirc',  'Iuml',
            'ETH',     'Ntilde', 'Ograve',  'Oacute', 'Ocirc',  'Otilde',
            'Ouml',    'times',  'Oslash',  'Ugrave', 'Uacute', 'Ucirc',
            'Uuml',    'Yacute', 'THORN',   'szlig',  'agrave', 'aacute',
            'acirc',   'atilde', 'auml',    'aring',  'aelig',  'ccedil',
            'egrave',  'eacute', 'ecirc',   'euml',   'igrave', 'iacute',
            'icirc',   'iuml',   'eth',     'ntilde', 'ograve', 'oacute',
            'ocirc',   'otilde', 'ouml',    'divide', 'oslash', 'ugrave',
            'uacute',  'ucirc',  'uuml',    'yacute', 'thorn',  'yuml',
            'quot',    'amp',    'lt',      'gt',     'apos',   'OElig',
            'oelig',   'Scaron', 'scaron',  'Yuml',   'circ',   'tilde',
            'ensp',    'emsp',   'thinsp',  'zwnj',   'zwj',    'lrm',
            'rlm',     'ndash',  'mdash',   'lsquo',  'rsquo',  'sbquo',
            'ldquo',   'rdquo',  'bdquo',   'dagger', 'Dagger', 'permil',
            'lsaquo',  'rsaquo', 'euro',    'fnof',   'Alpha',  'Beta',
            'Gamma',   'Delta',  'Epsilon', 'Zeta',   'Eta',    'Theta',
            'Iota',    'Kappa',  'Lambda',  'Mu',     'Nu',     'Xi',
            'Omicron', 'Pi',     'Rho',     'Sigma',  'Tau',    'Upsilon',
            'Phi',     'Chi',    'Psi',     'Omega',  'alpha',  'beta',
            'gamma',   'delta',  'epsilon', 'zeta',   'eta',    'theta',
            'iota',    'kappa',  'lambda',  'mu',     'nu',     'xi',
            'omicron', 'pi',     'rho',     'sigmaf', 'sigma',  'tau',
            'upsilon', 'phi',    'chi',     'psi',    'omega',  'thetasym',
            'upsih',   'piv',    'bull',    'hellip', 'prime',  'Prime',
            'oline',   'frasl',  'weierp',  'image',  'real',   'trade',
            'alefsym', 'larr',   'uarr',    'rarr',   'darr',   'harr',
            'crarr',   'lArr',   'uArr',    'rArr',   'dArr',   'hArr',
            'forall',  'part',   'exist',   'empty',  'nabla',  'isin',
            'notin',   'ni',     'prod',    'sum',    'minus',  'lowast',
            'radic',   'prop',   'infin',   'ang',    'and',    'or',
            'cap',     'cup',    'int',     'sim',    'cong',   'asymp',
            'ne',      'equiv',  'le',      'ge',     'sub',    'sup',
            'nsub',    'sube',   'supe',    'oplus',  'otimes', 'perp',
            'sdot',    'lceil',  'rceil',   'lfloor', 'rfloor', 'lang',
            'rang',    'loz',    'spades',  'clubs',  'hearts', 'diams',
            'sup1',    'sup2',   'sup3',    'frac14', 'frac12', 'frac34',
            'there4',
            );

        if ( empty($matches[1]) )
            return '';

        $i = $matches[1];
        return ( ( ! in_array($i, $allowedentitynames) ) ? "&amp;$i;" : "&$i;" );
    }

    /**
     * Callback for wp_kses_normalize_entities() regular expression.
     *
     * This function helps {@see wp_kses_normalize_entities()} to only accept 16-bit
     * values and nothing more for `&#number;` entities.
     *
     * @access private
     * @since 1.0.0
     *
     * @param array $matches preg_replace_callback() matches array
     * @return string Correctly encoded entity
     */
    protected static function wp_kses_normalize_entities2($matches) {
        if ( empty($matches[1]) )
            return '';

        $i = $matches[1];
        if (self::valid_unicode($i)) {
            $i = str_pad(ltrim($i,'0'), 3, '0', STR_PAD_LEFT);
            $i = "&#$i;";
        } else {
            $i = "&amp;#$i;";
        }

        return $i;
    }

    /**
     * Callback for wp_kses_normalize_entities() for regular expression.
     *
     * This function helps wp_kses_normalize_entities() to only accept valid Unicode
     * numeric entities in hex form.
     *
     * @access private
     *
     * @param array $matches preg_replace_callback() matches array
     * @return string Correctly encoded entity
     */
    protected static function wp_kses_normalize_entities3($matches) {
        if ( empty($matches[1]) )
            return '';

        $hexchars = $matches[1];
        return ( ( ! self::valid_unicode(hexdec($hexchars)) ) ? "&amp;#x$hexchars;" : '&#x'.ltrim($hexchars,'0').';' );
    }

    /**
     * Helper function to determine if a Unicode value is valid.
     *
     * @param int $i Unicode value
     * @return bool True if the value was a valid Unicode number
     */
    protected static function valid_unicode($i) {
        return ( $i == 0x9 || $i == 0xa || $i == 0xd ||
            ($i >= 0x20 && $i <= 0xd7ff) ||
            ($i >= 0xe000 && $i <= 0xfffd) ||
            ($i >= 0x10000 && $i <= 0x10ffff) );
    }

    /**
     * Filter the list of mime types and file extensions.
     *
     * This filter should be used to add, not remove, mime types. To remove
     * mime types, use the 'upload_mimes' filter.
     *
     * @since 3.5.0
     *
     * @param array $wp_get_mime_types Mime types keyed by the file extension regex
     *                                 corresponding to those types.
     * @return array
     */
    protected static function wp_get_mime_types() {
        return array(
            // Image formats.
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tiff|tif' => 'image/tiff',
            'ico' => 'image/x-icon',
            // Video formats.
            'asf|asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp|3gpp' => 'video/3gpp', // Can also be audio
            '3g2|3gp2' => 'video/3gpp2', // Can also be audio
            // Text formats.
            'txt|asc|c|cc|h|srt' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'ics' => 'text/calendar',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm|html' => 'text/html',
            'vtt' => 'text/vtt',
            'dfxp' => 'application/ttaf+xml',
            // Audio formats.
            'mp3|m4a|m4b' => 'audio/mpeg',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/x-ms-wma',
            'wax' => 'audio/x-ms-wax',
            'mka' => 'audio/x-matroska',
            // Misc application formats.
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz|gzip' => 'application/x-gzip',
            'rar' => 'application/rar',
            '7z' => 'application/x-7z-compressed',
            'exe' => 'application/x-msdownload',
            'psd' => 'application/octet-stream',
            'xcf' => 'application/octet-stream',
            // MS Office formats.
            'doc' => 'application/msword',
            'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
            'oxps' => 'application/oxps',
            'xps' => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp|wpd' => 'application/wordperfect',
            // iWork formats.
            'key' => 'application/vnd.apple.keynote',
            'numbers' => 'application/vnd.apple.numbers',
            'pages' => 'application/vnd.apple.pages',
        );
    }

    /**
     * Convert lone less than signs.
     *
     * KSES already converts lone greater than signs.
     *
     * @since 2.3.0
     *
     * @param string $text Text to be converted.
     * @return string Converted text.
     */
    protected static function wp_pre_kses_less_than( $text ) {
        return preg_replace_callback('%<[^>]*?((?=<)|>|$)%', 'self::wp_pre_kses_less_than_callback', $text);
    }

    /**
     * Callback function used by preg_replace.
     *
     * @since 2.3.0
     *
     * @param array $matches Populated by matches to preg_replace.
     * @return string The text returned after esc_html if needed.
     */
    protected static function wp_pre_kses_less_than_callback( $matches ) {
        if ( false === strpos($matches[0], '>') )
            return esc_html($matches[0]);
        return $matches[0];
    }

    /**
     * Checks for invalid UTF8 in a string.
     *
     * @since 2.8.0
     *
     * @param string $string The text which is to be checked.
     * @param boolean $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
     * @return string The checked text.
     */
    protected static function wp_check_invalid_utf8( $string, $strip = false ) {
        $string = (string) $string;

        if ( 0 === strlen( $string ) ) {
            return '';
        }

        // Store the site charset as a static to avoid multiple calls to get_option()
        static $is_utf8 = "UTF-8";
        if ( !$is_utf8 ) {
            return $string;
        }

        // Check for support for utf8 in the installed PCRE library once and store the result in a static
        static $utf8_pcre;
        if ( !isset( $utf8_pcre ) ) {
            $utf8_pcre = @preg_match( '/^./u', 'a' );
        }
        // We can't demand utf8 in the PCRE installation, so just return the string in those cases
        if ( !$utf8_pcre ) {
            return $string;
        }

        // preg_match fails when it encounters invalid UTF8 in $string
        if ( 1 === @preg_match( '/^./us', $string ) ) {
            return $string;
        }

        // Attempt to strip the bad chars if requested (not recommended)
        if ( $strip && function_exists( 'iconv' ) ) {
            return iconv( 'utf-8', 'utf-8', $string );
        }

        return '';
    }

    /**
     * Properly strip all HTML tags including script and style
     *
     * This differs from strip_tags() because it removes the contents of
     * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
     * will return 'something'. wp_strip_all_tags will return ''
     *
     * @since 2.9.0
     *
     * @param string $string String containing HTML tags
     * @param bool $remove_breaks optional Whether to remove left over line breaks and white space chars
     * @return string The processed string.
     */
    protected static function wp_strip_all_tags($string, $remove_breaks = false) {
        $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
        $string = strip_tags($string);
        if ( $remove_breaks ) {
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        }
        return trim( $string );
    }

    /**
     * Retrieve a list of protocols to allow in HTML attributes.
     *
     * @since 3.3.0
     * @since 4.3.0 Added 'webcal' to the protocols array.
     *
     * @see wp_kses()
     * @see esc_url()
     *
     * @staticvar array $protocols
     *
     * @return array Array of allowed protocols. Defaults to an array containing 'http', 'https',
     *               'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet',
     *               'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', and 'webcal'.
     */
    protected static function wp_allowed_protocols() {
        $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal' );
        return $protocols;
    }

    /**
     * A wrapper for PHP's parse_url() function that handles edgecases in < PHP 5.4.7
     *
     * PHP 5.4.7 expanded parse_url()'s ability to handle non-absolute url's, including
     * schemeless and relative url's with :// in the path, this works around those
     * limitations providing a standard output on PHP 5.2~5.4+.
     *
     * Error suppression is used as prior to PHP 5.3.3, an E_WARNING would be generated
     * when URL parsing failed.
     *
     * @since 4.4.0
     *
     * @param string $url The URL to parse.
     * @return bool|array False on failure; Array of URL components on success;
     *                    See parse_url()'s return values.
     */
    protected static function wp_parse_url( $url ) {
        $parts = @parse_url( $url );
        if ( ! $parts ) {
            // < PHP 5.4.7 compat, trouble with relative paths including a scheme break in the path
            if ( '/' == $url[0] && false !== strpos( $url, '://' ) ) {
                // Since we know it's a relative path, prefix with a scheme/host placeholder and try again
                if ( ! $parts = @parse_url( 'placeholder://placeholder' . $url ) ) {
                    return $parts;
                }
                // Remove the placeholder values
                unset( $parts['scheme'], $parts['host'] );
            } else {
                return $parts;
            }
        }

        // < PHP 5.4.7 compat, doesn't detect schemeless URL's host field
        if ( '//' == substr( $url, 0, 2 ) && ! isset( $parts['host'] ) ) {
            $path_parts = explode( '/', substr( $parts['path'], 2 ), 2 );
            $parts['host'] = $path_parts[0];
            if ( isset( $path_parts[1] ) ) {
                $parts['path'] = '/' . $path_parts[1];
            } else {
                unset( $parts['path'] );
            }
        }

        return $parts;
    }

    /**
     * Perform a deep string replace operation to ensure the values in $search are no longer present
     *
     * Repeats the replacement operation until it no longer replaces anything so as to remove "nested" values
     * e.g. $subject = '%0%0%0DDD', $search ='%0D', $result ='' rather than the '%0%0DD' that
     * str_replace would return
     *
     * @since 2.8.1
     * @access private
     *
     * @param string|array $search  The value being searched for, otherwise known as the needle.
     *                              An array may be used to designate multiple needles.
     * @param string       $subject The string being searched and replaced on, otherwise known as the haystack.
     * @return string The string with the replaced svalues.
     */
    protected static function _deep_replace( $search, $subject ) {
        $subject = (string) $subject;

        $count = 1;
        while ( $count ) {
            $subject = str_replace( $search, '', $subject, $count );
        }

        return $subject;
    }

    /**
     * Sanitize string from bad protocols.
     *
     * This function removes all non-allowed protocols from the beginning of
     * $string. It ignores whitespace and the case of the letters, and it does
     * understand HTML entities. It does its work in a while loop, so it won't be
     * fooled by a string like "javascript:javascript:alert(57)".
     *
     * @since 1.0.0
     *
     * @param string $string            Content to filter bad protocols from
     * @param array  $allowed_protocols Allowed protocols to keep
     * @return string Filtered content
     */
    protected static function wp_kses_bad_protocol($string, $allowed_protocols) {
        $string = self::wp_kses_no_null($string);
        $iterations = 0;

        do {
            $original_string = $string;
            $string = self::wp_kses_bad_protocol_once($string, $allowed_protocols);
        } while ( $original_string != $string && ++$iterations < 6 );

        if ( $original_string != $string )
            return '';

        return $string;
    }

    /**
     * Removes any invalid control characters in $string.
     *
     * Also removes any instance of the '\0' string.
     *
     * @since 1.0.0
     *
     * @param string $string
     * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
     * @return string
     */
    protected static function wp_kses_no_null( $string, $options = null ) {
        if ( ! isset( $options['slash_zero'] ) ) {
            $options = array( 'slash_zero' => 'remove' );
        }

        $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
        if ( 'remove' == $options['slash_zero'] ) {
            $string = preg_replace( '/\\\\+0+/', '', $string );
        }

        return $string;
    }

    /**
     * Sanitizes content from bad protocols and other characters.
     *
     * This function searches for URL protocols at the beginning of $string, while
     * handling whitespace and HTML entities.
     *
     * @since 1.0.0
     *
     * @param string $string            Content to check for bad protocols
     * @param string $allowed_protocols Allowed protocols
     * @return string Sanitized content
     */
    protected static function wp_kses_bad_protocol_once($string, $allowed_protocols, $count = 1 ) {
        $string2 = preg_split( '/:|&#0*58;|&#x0*3a;/i', $string, 2 );
        if ( isset($string2[1]) && ! preg_match('%/\?%', $string2[0]) ) {
            $string = trim( $string2[1] );
            $protocol = self::wp_kses_bad_protocol_once2( $string2[0], $allowed_protocols );
            if ( 'feed:' == $protocol ) {
                if ( $count > 2 )
                    return '';
                $string = self::wp_kses_bad_protocol_once( $string, $allowed_protocols, ++$count );
                if ( empty( $string ) )
                    return $string;
            }
            $string = $protocol . $string;
        }

        return $string;
    }

    /**
     * Callback for wp_kses_bad_protocol_once() regular expression.
     *
     * This function processes URL protocols, checks to see if they're in the
     * whitelist or not, and returns different data depending on the answer.
     *
     * @access private
     * @since 1.0.0
     *
     * @param string $string            URI scheme to check against the whitelist
     * @param string $allowed_protocols Allowed protocols
     * @return string Sanitized content
     */
    protected static function wp_kses_bad_protocol_once2( $string, $allowed_protocols ) {
        $string2 = self::wp_kses_decode_entities($string);
        $string2 = preg_replace('/\s/', '', $string2);
        $string2 = self::wp_kses_no_null($string2);
        $string2 = strtolower($string2);

        $allowed = false;
        foreach ( (array) $allowed_protocols as $one_protocol )
            if ( strtolower($one_protocol) == $string2 ) {
                $allowed = true;
                break;
            }

        if ($allowed)
            return "$string2:";
        else
            return '';
    }

    /**
     * Convert all entities to their character counterparts.
     *
     * This function decodes numeric HTML entities (`&#65;` and `&#x41;`).
     * It doesn't do anything with other entities like &auml;, but we don't
     * need them in the URL protocol whitelisting system anyway.
     *
     * @since 1.0.0
     *
     * @param string $string Content to change entities
     * @return string Content after decoded entities
     */
    protected static function wp_kses_decode_entities($string) {
        $string = preg_replace_callback('/&#([0-9]+);/', [new Wordpress(), '_wp_kses_decode_entities_chr'], $string);
        $string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', [new Wordpress(), '_wp_kses_decode_entities_chr_hexdec'], $string);

        return $string;
    }

    /**
     * Regex callback for wp_kses_decode_entities()
     *
     * @param array $match preg match
     * @return string
     */
    protected static function _wp_kses_decode_entities_chr( $match ) {
        return chr( $match[1] );
    }

    /**
     * Regex callback for wp_kses_decode_entities()
     *
     * @param array $match preg match
     * @return string
     */
    protected static function _wp_kses_decode_entities_chr_hexdec( $match ) {
        return chr( hexdec( $match[1] ) );
    }

    /**
     * Checks and cleans a URL.
     *
     * A number of characters are removed from the URL. If the URL is for displaying
     * (the default behaviour) ampersands are also replaced. The {@see 'clean_url'} filter
     * is applied to the returned cleaned URL.
     *
     * @since 2.8.0
     *
     * @param string $url       The URL to be cleaned.
     * @param array  $protocols Optional. An array of acceptable protocols.
     *		                    Defaults to return value of wp_allowed_protocols()
     * @param string $_context  Private. Use esc_url_raw() for database usage.
     * @return string The cleaned $url after the {@see 'clean_url'} filter is applied.
     */
    protected static function esc_url( $url, $protocols = null, $_context = 'display' ) {
        $original_url = $url;

        if ( '' == $url )
            return $url;

        $url = str_replace( ' ', '%20', $url );
        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);

        if ( '' === $url ) {
            return $url;
        }

        if ( 0 !== stripos( $url, 'mailto:' ) ) {
            $strip = array('%0d', '%0a', '%0D', '%0A');
            $url = self::_deep_replace($strip, $url);
        }

        $url = str_replace(';//', '://', $url);
        /* If the URL doesn't appear to contain a scheme, we
         * presume it needs http:// prepended (unless a relative
         * link starting with /, # or ? or a php file).
         */
        if ( strpos($url, ':') === false && ! in_array( $url[0], array( '/', '#', '?' ) ) &&
            ! preg_match('/^[a-z0-9-]+?\.php/i', $url) )
            $url = 'http://' . $url;

        // Replace ampersands and single quotes only when displaying.
        if ( 'display' == $_context ) {
            $url = self::wp_kses_normalize_entities( $url );
            $url = str_replace( '&amp;', '&#038;', $url );
            $url = str_replace( "'", '&#039;', $url );
        }

        if ( ( false !== strpos( $url, '[' ) ) || ( false !== strpos( $url, ']' ) ) ) {

            $parsed = self::wp_parse_url( $url );
            $front  = '';

            if ( isset( $parsed['scheme'] ) ) {
                $front .= $parsed['scheme'] . '://';
            } elseif ( '/' === $url[0] ) {
                $front .= '//';
            }

            if ( isset( $parsed['user'] ) ) {
                $front .= $parsed['user'];
            }

            if ( isset( $parsed['pass'] ) ) {
                $front .= ':' . $parsed['pass'];
            }

            if ( isset( $parsed['user'] ) || isset( $parsed['pass'] ) ) {
                $front .= '@';
            }

            if ( isset( $parsed['host'] ) ) {
                $front .= $parsed['host'];
            }

            if ( isset( $parsed['port'] ) ) {
                $front .= ':' . $parsed['port'];
            }

            $end_dirty = str_replace( $front, '', $url );
            $end_clean = str_replace( array( '[', ']' ), array( '%5B', '%5D' ), $end_dirty );
            $url       = str_replace( $end_dirty, $end_clean, $url );

        }

        if ( '/' === $url[0] ) {
            $good_protocol_url = $url;
        } else {
            if ( ! is_array( $protocols ) )
                $protocols = self::wp_allowed_protocols();
            $good_protocol_url = self::wp_kses_bad_protocol( $url, $protocols );
            if ( strtolower( $good_protocol_url ) != strtolower( $url ) )
                return '';
        }
        return $good_protocol_url;
    }

}
