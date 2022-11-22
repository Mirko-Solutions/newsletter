<?php

namespace Mirko\Newsletter\Tests\Unit\Utility;

use Mirko\Newsletter\Utility\Uri;

/**
 * Test case for class Mirko\Newsletter\Utility\Uri.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Utility\Uri
 */
class UriTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    public function dataProviderTestUri()
    {
        $result = [];
        foreach (Uri::getSchemes() as $scheme) {
            $result[] = [$scheme];
        }

        return $result;
    }

    /**
     * @dataProvider dataProviderTestUri
     *
     * @param string $scheme
     */
    public function testKnownSchemes($scheme)
    {
        $this->assertTrue(Uri::isAbsolute($scheme . ':foo/bar'));
        $this->assertTrue(Uri::isAbsolute($scheme . '://foo/bar'));
        $this->assertFalse(Uri::isAbsolute('foo/bar/' . $scheme));
    }

    public function testFragment()
    {
        $this->assertTrue(Uri::isAbsolute('http:foo/bar#abc'));
        $this->assertTrue(Uri::isAbsolute('#abc'), 'fragment URL should be considered absolute URI because they msut not be modified at all');
    }
}
