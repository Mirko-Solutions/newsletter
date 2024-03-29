<?php

namespace Mirko\Newsletter\Domain\Model\PlainConverter;

use Mirko\Newsletter\Domain\Model\IPlainConverter;
use Mirko\Newsletter\Tools;

/**
 * Convert HTML to plain text using external lynx program
 */
class Lynx implements IPlainConverter
{
    private function injectBaseUrl($content, $baseUrl)
    {
        if (!str_contains($content, '<base ')) {
            $content = str_ireplace('<body', '<base href="' . $baseUrl . '"><body', $content);
        }

        return $content;
    }

    public function getPlainText($content, $baseUrl): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'newsletter_');
        $contentWithBase = $this->injectBaseUrl($content, $baseUrl);

        file_put_contents($tmpFile, $contentWithBase);

        $cmd = escapeshellcmd(Tools::confParam('path_to_lynx')) . ' -force_html -dump ' . escapeshellarg($tmpFile);
        exec($cmd, $output);
        unlink($tmpFile);
        return implode("\n", $output);
    }
}
