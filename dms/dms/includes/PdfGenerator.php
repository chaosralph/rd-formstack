<?php

require_once __DIR__ . '/../vendor/autoload.php';

class PdfGenerator
{
    /**
     * Erzeugt eine PDF aus einem oder mehreren Bildern.
     * Jedes Bild wird auf eine eigene Seite gelegt.
     *
     * @param array  $imagePaths  Liste der Bildpfade
     * @param string $outputPath  Zielpfad für die PDF
     * @param string $title       Dokumententitel
     * @return bool
     */
    public static function createFromImages(array $imagePaths, string $outputPath, string $title = ''): bool
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator(PDF_AUTHOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle($title ?: 'Dokument');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pageWidth = 210;  // A4 mm
        $pageHeight = 297;

        foreach ($imagePaths as $imagePath) {
            if (!file_exists($imagePath)) {
                continue;
            }

            $pdf->AddPage();

            $imgInfo = getimagesize($imagePath);
            if ($imgInfo === false) {
                continue;
            }

            [$imgW, $imgH] = $imgInfo;
            $imgRatio = $imgW / $imgH;
            $pageRatio = $pageWidth / $pageHeight;

            if ($imgRatio > $pageRatio) {
                $w = $pageWidth;
                $h = $pageWidth / $imgRatio;
                $x = 0;
                $y = ($pageHeight - $h) / 2;
            } else {
                $h = $pageHeight;
                $w = $pageHeight * $imgRatio;
                $x = ($pageWidth - $w) / 2;
                $y = 0;
            }

            $pdf->Image($imagePath, $x, $y, $w, $h, '', '', '', false, 300);
        }

        $pdf->Output($outputPath, 'F');

        return file_exists($outputPath);
    }
}
