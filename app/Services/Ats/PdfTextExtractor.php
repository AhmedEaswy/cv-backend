<?php

namespace App\Services\Ats;

use Smalot\PdfParser\Parser;
use Throwable;

class PdfTextExtractor
{
    public function __construct(
        private ?Parser $parser = null
    ) {
        $this->parser ??= new Parser;
    }

    /**
     * Extract plain text and light metadata from a PDF file path.
     *
     * @return array{text: string, page_count: int, file_size: int, char_count: int}
     */
    public function extract(string $absolutePath): array
    {
        $fileSize = is_file($absolutePath) ? (int) filesize($absolutePath) : 0;

        try {
            $pdf = $this->parser->parseFile($absolutePath);
            $text = trim(preg_replace('/[ \t]+/', ' ', $pdf->getText() ?? '') ?? '');
            $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
            $pages = $pdf->getPages();
            $pageCount = is_array($pages) ? count($pages) : 0;
        } catch (Throwable) {
            return [
                'text' => '',
                'page_count' => 0,
                'file_size' => $fileSize,
                'char_count' => 0,
            ];
        }

        return [
            'text' => $text,
            'page_count' => $pageCount,
            'file_size' => $fileSize,
            'char_count' => mb_strlen($text),
        ];
    }
}
