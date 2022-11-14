<?php

namespace Mirko\Newsletter\Utility;

/**
 * Uri helper
 */
class Uri
{
    /**
     * Returns the list of all official IANA registered schemes
     * http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml
     *
     * @return array
     */
    public static function getSchemes()
    {
        return [
            'aaa',
            'aaas',
            'about',
            'acap',
            'acct',
            'acr',
            'adiumxtra',
            'afp',
            'afs',
            'aim',
            'apt',
            'attachment',
            'aw',
            'barion',
            'beshare',
            'bitcoin',
            'bolo',
            'callto',
            'cap',
            'chrome',
            'chrome-extension',
            'cid',
            'coap',
            'coaps',
            'com-eventbrite-attendee',
            'content',
            'crid',
            'cvs',
            'data',
            'dav',
            'dict',
            'dlna-playcontainer',
            'dlna-playsingle',
            'dns',
            'dtn',
            'dvb',
            'ed2k',
            'example',
            'facetime',
            'fax',
            'feed',
            'feedready',
            'file',
            'finger',
            'fish',
            'ftp',
            'geo',
            'gg',
            'git',
            'gizmoproject',
            'go',
            'gopher',
            'gtalk',
            'h323',
            'ham',
            'hcp',
            'http',
            'https',
            'iax',
            'icap',
            'icon',
            'im',
            'imap',
            'info',
            'ipn',
            'ipp',
            'ipps',
            'irc',
            'irc6',
            'ircs',
            'iris',
            'iris.beep',
            'iris.lwz',
            'iris.xpc',
            'iris.xpcs',
            'itms',
            'jabber',
            'jar',
            'jms',
            'keyparc',
            'lastfm',
            'ldap',
            'ldaps',
            'magnet',
            'mailserver',
            'mailto',
            'maps',
            'market',
            'message',
            'mid',
            'mms',
            'modem',
            'ms-help',
            'ms-settings-power',
            'msnim',
            'msrp',
            'msrps',
            'mtqp',
            'mumble',
            'mupdate',
            'mvn',
            'news',
            'nfs',
            'ni',
            'nih',
            'nntp',
            'notes',
            'oid',
            'opaquelocktoken',
            'pack',
            'palm',
            'paparazzi',
            'pkcs11',
            'platform',
            'pop',
            'pres',
            'prospero',
            'proxy',
            'psyc',
            'query',
            'reload',
            'res',
            'resource',
            'rmi',
            'rsync',
            'rtmfp',
            'rtmp',
            'rtsp',
            'rtsps',
            'rtspu',
            'secondlife',
            'service',
            'session',
            'sftp',
            'sgn',
            'shttp',
            'sieve',
            'sip',
            'sips',
            'skype',
            'smb',
            'sms',
            'smtp',
            'snews',
            'snmp',
            'soap.beep',
            'soap.beeps',
            'soldat',
            'spotify',
            'ssh',
            'steam',
            'stun',
            'stuns',
            'submit',
            'svn',
            'tag',
            'teamspeak',
            'tel',
            'teliaeid',
            'telnet',
            'tftp',
            'things',
            'thismessage',
            'tip',
            'tn3270',
            'turn',
            'turns',
            'tv',
            'udp',
            'unreal',
            'urn',
            'ut2004',
            'vemmi',
            'ventrilo',
            'videotex',
            'view-source',
            'wais',
            'webcal',
            'ws',
            'wss',
            'wtai',
            'wyciwyg',
            'xcon',
            'xcon-userid',
            'xfire',
            'xmlrpc.beep',
            'xmlrpc.beeps',
            'xmpp',
            'xri',
            'ymsgr',
            'z39.50',
            'z39.50r',
            'z39.50s',
        ];
    }

    /**
     * Return the regex pattern to detect absolute URI, including special URI fragment
     *
     * @return string
     */
    private static function getPattern()
    {
        $escapedSchemes = [];
        foreach (self::getSchemes() as $scheme) {
            $escapedSchemes[] = preg_quote($scheme);
        }

        $pattern = '/^((' . implode('|', $escapedSchemes) . '):|#)/i';

        return $pattern;
    }

    public static function isAbsolute($uri)
    {
        return (bool)preg_match(self::getPattern(), $uri);
    }
}
