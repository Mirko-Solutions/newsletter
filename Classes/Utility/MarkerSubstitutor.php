<?php

namespace Mirko\Newsletter\Utility;

use Mirko\Newsletter\Domain\Model\Email;
use Mirko\Newsletter\Service\Typo3GeneralService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Used to substitute markers in any kind of text
 */
class MarkerSubstitutor
{
    private $simpleMarkersFound;
    private $advancedMarkersFound;

    /**
     * Substitute multiple markers to an URL
     *
     * @param string $url
     * @param Email $email
     *
     * @return string url with marker replaced
     */
    public function substituteMarkersInUrl($url, Email $email)
    {
        $prefix = '<a href="';
        $suffix = '">';
        $link = $prefix . $url . $suffix;

        $result = $this->substituteMarkers($link, $email, 'link');

        return mb_substr($result, mb_strlen($prefix), mb_strlen($result) - mb_strlen($prefix) - mb_strlen($suffix));
    }

    /**
     * Apply multiple markers to mail contents
     *
     * @param string $src
     * @param Email $email
     * @param string $name optional name to be forwarded to hook
     *
     * @return string url with marker replaced
     */
    public function substituteMarkers($src, Email $email, $name = '')
    {
        $markers = $this->getMarkers($email);
        $result = $src;
        $extConfiguration = Typo3GeneralService::getExtensionConfiguration();
        if (array_key_exists(
                'substituteMarkersHook',
                $extConfiguration
            ) && is_array($extConfiguration['substituteMarkersHook'])) {
            foreach ($extConfiguration['substituteMarkersHook'] as $_classRef) {
                $_procObj = GeneralUtility::makeInstance($_classRef);
                $result = $_procObj->substituteMarkersHook($result, $name, $markers, $email);
            }
        }

        // For each marker, only substitute if the field is registered as a marker.
        // This approach has shown to speed up things quite a bit.
        $this->findExistingMarkers($src);
        foreach ($markers as $name => $value) {
            if (in_array($name, $this->advancedMarkersFound, true)) {
                $result = $this->substituteAdvancedMarker($result, $name, $value);
            }

            if (in_array($name, $this->simpleMarkersFound, true)) {
                $result = $this->substituteSimpleMarker($result, $name, $value);
            }
        }

        return $result;
    }

    /**
     * Find any markers that exists in the source
     *
     * @param string $src
     */
    private function findExistingMarkers($src)
    {
        // Detect what markers we need to substitute later on
        preg_match_all('/###(\w+)###/', $src, $fields);
        preg_match_all('|"https?://(\w+)"|', $src, $fieldsLinks);
        $this->simpleMarkersFound = array_merge($fields[1], $fieldsLinks[1]);

        // Any advanced IF fields we need to substitute later on
        $this->advancedMarkersFound = [];
        preg_match_all('/###:IF: (\w+) ###/U', $src, $fields);
        foreach ($fields[1] as $field) {
            $this->advancedMarkersFound[] = $field;
        }
    }

    /**
     * Return all markers and their values as associative array
     *
     * @param Email $email
     *
     * @return string[]
     */
    private function getMarkers(Email $email)
    {
        $markers = $email->getRecipientData();

        // Add predefined markers
        $markers['newsletter_view_url'] = $email->getViewUrl();
        $markers['newsletter_unsubscribe_url'] = $email->getUnsubscribeUrl();

        return $markers;
    }

    /**
     * Replace a named marker with a supplied value
     * A simple marker can have the form of: ###marker###, http://marker, or https://marker
     *
     * @param string $src Source to apply marker substitution to
     * @param string $name Name of the marker to replace
     * @param string $value Value to replace marker with
     *
     * @return string Source with applied marker
     */
    private function substituteSimpleMarker($src, $name, $value)
    {     // All variants of the marker to search
        $search = [
            "###$name###",
            "http://$name",
            "https://$name",
            urlencode(
                "###$name###"
            ), // If the marker is in a link and the "links spy" option is activated it will be urlencoded
            urlencode("http://$name"),
            urlencode("https://$name"),
        ];

        $replace = [
            $value,
            $value,
            preg_replace('-^http://-', 'https://', $value),
            urlencode($value), // We need to replace with urlencoded value
            urlencode($value),
            urlencode(preg_replace('-^http://-', 'https://', $value)),
        ];

        return str_ireplace($search, $replace, $src);
    }

    /**
     * Substitute an advanced marker
     * An advanced conditional marker ###:IF: marker ### ..content.. (###:ELSE:###)? ..content.. ###:ENDIF:###
     *
     * @param string $src Source to apply marker substitution to
     * @param string $name Name of the marker to replace
     * @param string $value Value to replace marker with
     *
     * @return string Source with applied marker
     */
    private function substituteAdvancedMarker($src, $name, $value)
    {
        $tokenBegin = "###:IF: $name ###";
        $tokenElse = '###:ELSE:###';
        $tokenEnd = '###:ENDIF:###';
        while (($beginning = mb_strpos($src, $tokenBegin)) !== false) {
            $end = mb_strpos($src, $tokenEnd, $beginning);

            // If marker is not correctly terminated, cancel everything
            if ($end === false) {
                break;
            }

            // Find ELSE token but only before the ENDIF token
            $else = mb_strpos($src, $tokenElse, $beginning);
            if ($else > $end) {
                $else = false;
            }

            // Find the text which will replace the marker
            if ($value) {
                $textBeginning = $beginning + mb_strlen($tokenBegin);
                if ($else === false) {
                    $text = mb_substr($src, $textBeginning, $end - $textBeginning);
                } else {
                    $text = mb_substr($src, $textBeginning, $else - $textBeginning);
                }
            } else {
                if ($else === false) {
                    $text = '';
                } else {
                    $textBeginning = $else + mb_strlen($tokenElse);
                    $text = mb_substr($src, $textBeginning, $end - $textBeginning);
                }
            }

            // Do the actual replacement in the entire src (possibly replacing the same marker several times)
            $entireMarker = mb_substr($src, $beginning, $end - $beginning + mb_strlen(($tokenEnd)));
            $src = str_replace($entireMarker, $text, $src);
        }

        return $src;
    }
}
