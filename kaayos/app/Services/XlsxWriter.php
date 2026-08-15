<?php

namespace App\Services;

use Carbon\Carbon;
use RuntimeException;
use ZipArchive;

class XlsxWriter
{
    private const NS_PACKAGE = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private const NS_DOC = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const NS_SPREADSHEET = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_DRAWING = 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing';

    private const NS_DRAWING_MAIN = 'http://schemas.openxmlformats.org/drawingml/2006/main';

    private const NS_CONTENT_TYPES = 'http://schemas.openxmlformats.org/package/2006/content-types';

    private const DATA_START_INDEX = 1;

    private const HEADER_ROW = 10;

    private const RESERVED_WIDTH = 11;

    private const LOGO_EMU = 777240;

    private const TUY_LOGO_PATH = 'images/tuy-logo.jpg';

    private const PESO_LOGO_PATH = 'images/peso-logo.jpg';

    private const LETTERHEAD = [
        ['Republic of the Philippines', 2],
        ['PROVINCE OF BATANGAS', 3],
        ['PUBLIC EMPLOYMENT SERVICE OFFICE', 3],
        ['PESO, Municipal Hall Complex', 4],
        ['Gomez St. corner Rizal St., Town Proper, Tuy, Batangas 4214', 4],
    ];

    private const MERGED_ROWS = [1, 2, 3, 4, 5, 7, 8];

    public static function binary(string $sheetName, array $headers, array $rows, array $meta = []): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'kaayos_xlsx');

        $tuy = self::imagePayload(public_path(self::TUY_LOGO_PATH));
        $peso = self::imagePayload(public_path(self::PESO_LOGO_PATH));
        $hasImages = $tuy !== null || $peso !== null;
        $rightCol = max(count($headers), 1) + 1;

        $zip = new ZipArchive;
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create spreadsheet archive.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes($hasImages));
        $zip->addFromString('_rels/.rels', self::rootRels());
        $zip->addFromString('xl/workbook.xml', self::workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($headers, $rows, $meta, $hasImages));

        if ($hasImages) {
            if ($tuy !== null) {
                $zip->addFromString('xl/media/image1.jpeg', $tuy);
            }
            if ($peso !== null) {
                $zip->addFromString('xl/media/image2.jpeg', $peso);
            }

            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', self::sheetRels());
            $zip->addFromString('xl/drawings/drawing1.xml', self::drawing($tuy !== null, $peso !== null, $rightCol));
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', self::drawingRels($tuy !== null, $peso !== null));
        }

        $zip->addFromString('xl/styles.xml', self::styles());

        if (! $zip->close()) {
            @unlink($tmp);
            throw new RuntimeException('Unable to finalize spreadsheet archive.');
        }

        $binary = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $binary;
    }

    private static function contentTypes(bool $hasImages): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="'.self::NS_CONTENT_TYPES.'">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .($hasImages ? '<Default Extension="jpeg" ContentType="image/jpeg"/>' : '')
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .($hasImages ? '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>' : '')
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="'.self::NS_PACKAGE.'">'
            .'<Relationship Id="rId1" Type="'.self::NS_DOC.'/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private static function workbook(string $sheetName): string
    {
        $name = self::sanitizeSheetName($sheetName);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="'.self::NS_SPREADSHEET.'" xmlns:r="'.self::NS_DOC.'">'
            .'<sheets>'
            .'<sheet name="'.self::e($name).'" sheetId="1" r:id="rId1"/>'
            .'</sheets>'
            .'</workbook>';
    }

    private static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="'.self::NS_PACKAGE.'">'
            .'<Relationship Id="rId1" Type="'.self::NS_DOC.'/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="'.self::NS_DOC.'/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private static function sheetRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="'.self::NS_PACKAGE.'">'
            .'<Relationship Id="rId1" Type="'.self::NS_DOC.'/drawing" Target="../drawings/drawing1.xml"/>'
            .'</Relationships>';
    }

    private static function drawing(bool $hasTuy, bool $hasPeso, int $rightCol): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<xdr:wsDr xmlns:xdr="'.self::NS_DRAWING.'" xmlns:a="'.self::NS_DRAWING_MAIN.'" xmlns:r="'.self::NS_DOC.'">';

        if ($hasTuy) {
            $xml .= self::pictureAnchor(1, 'Tuy Logo', 0, 'rId1');
        }
        if ($hasPeso) {
            $xml .= self::pictureAnchor(2, 'PESO Logo', $rightCol, 'rId2');
        }

        $xml .= '</xdr:wsDr>';

        return $xml;
    }

    private static function pictureAnchor(int $id, string $name, int $col, string $embed): string
    {
        return '<xdr:oneCellAnchor editAs="oneCell">'
            .'<xdr:from>'
            .'<xdr:col>'.$col.'</xdr:col>'
            .'<xdr:colOff>0</xdr:colOff>'
            .'<xdr:row>0</xdr:row>'
            .'<xdr:rowOff>0</xdr:rowOff>'
            .'</xdr:from>'
            .'<xdr:ext cx="'.self::LOGO_EMU.'" cy="'.self::LOGO_EMU.'"/>'
            .'<xdr:pic>'
            .'<xdr:nvPicPr>'
            .'<xdr:cNvPr id="'.$id.'" name="'.self::e($name).'"/>'
            .'<xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr>'
            .'</xdr:nvPicPr>'
            .'<xdr:blipFill><a:blip r:embed="'.$embed.'"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
            .'<xdr:spPr>'
            .'<a:xfrm><a:off x="0" y="0"/><a:ext cx="'.self::LOGO_EMU.'" cy="'.self::LOGO_EMU.'"/></a:xfrm>'
            .'<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            .'</xdr:spPr>'
            .'</xdr:pic>'
            .'<xdr:clientData/>'
            .'</xdr:oneCellAnchor>';
    }

    private static function drawingRels(bool $hasTuy, bool $hasPeso): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="'.self::NS_PACKAGE.'">'
            .($hasTuy ? '<Relationship Id="rId1" Type="'.self::NS_DOC.'/image" Target="../media/image1.jpeg"/>' : '')
            .($hasPeso ? '<Relationship Id="rId2" Type="'.self::NS_DOC.'/image" Target="../media/image2.jpeg"/>' : '')
            .'</Relationships>';
    }

    private static function sheet(array $headers, array $rows, array $meta, bool $hasImages): string
    {
        $colCount = max(count($headers), 1);
        $totalCols = $colCount + 2;
        $lastDataCol = self::colName($colCount);
        $lastHeaderColIdx = $colCount;

        $widths = array_fill(0, $totalCols, 0);
        $widths[0] = self::RESERVED_WIDTH;

        $body = '<sheetData>';

        foreach (self::LETTERHEAD as $i => $entry) {
            $r = $i + 1;
            [$line, $style] = $entry;
            $body .= '<row r="'.$r.'" ht="20" customHeight="1">';
            $body .= '<c r="B'.$r.'" s="'.$style.'" t="inlineStr"><is><t>'.self::e($line).'</t></is></c>';
            $body .= '</row>';
        }

        $body .= '<row r="6" ht="8" customHeight="1"/>';

        $title = $meta['title'] ?? $sheetName;
        $body .= '<row r="7" ht="24" customHeight="1">';
        $body .= '<c r="B7" s="5" t="inlineStr"><is><t>'.self::e((string) $title).'</t></is></c>';
        $body .= '</row>';

        $period = $meta['period'] ?? '';
        $generated = $meta['generated_at'] ?? Carbon::now()->format('Y-m-d H:i');
        $metaLine = ($period !== '' ? 'Period: '.$period.'  |  ' : '').'Generated: '.$generated;
        $body .= '<row r="8" ht="16" customHeight="1">';
        $body .= '<c r="B8" s="6" t="inlineStr"><is><t>'.self::e($metaLine).'</t></is></c>';
        $body .= '</row>';

        $body .= '<row r="9" ht="6" customHeight="1"/>';

        $headerRow = self::HEADER_ROW;
        $body .= '<row r="'.$headerRow.'">';
        foreach (array_values($headers) as $i => $header) {
            $colIdx = self::DATA_START_INDEX + $i;
            $ref = self::colName($colIdx).$headerRow;
            $body .= '<c r="'.$ref.'" t="inlineStr" s="1"><is><t>'.self::e((string) $header).'</t></is></c>';
            $widths[$colIdx] = max($widths[$colIdx], mb_strlen((string) $header));
        }
        $body .= '</row>';

        $rowIndex = $headerRow + 1;
        foreach ($rows as $row) {
            $values = array_values($row);
            $body .= '<row r="'.$rowIndex.'">';
            foreach ($values as $i => $value) {
                if ($i >= $colCount) {
                    break;
                }
                $colIdx = self::DATA_START_INDEX + $i;
                $col = self::colName($colIdx);
                $ref = $col.$rowIndex;

                if ($value === null || $value === '') {
                    $body .= '<c r="'.$ref.'" t="inlineStr"><is><t></t></is></c>';
                } elseif (is_numeric($value)) {
                    $body .= '<c r="'.$ref.'"><v>'.$value.'</v></c>';
                } else {
                    $body .= '<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.self::e((string) $value).'</t></is></c>';
                    $len = mb_strlen((string) $value);
                    if ($len > $widths[$colIdx]) {
                        $widths[$colIdx] = min($len, 48);
                    }
                }
            }
            $body .= '</row>';
            $rowIndex++;
        }

        $body .= '</sheetData>';

        $cols = '<cols>';
        for ($i = 0; $i < $totalCols; $i++) {
            $w = ($i === 0 || $i === $totalCols - 1)
                ? self::RESERVED_WIDTH
                : max(10, min(48, $widths[$i] + 2));
            $cols .= '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$w.'" customWidth="1"/>';
        }
        $cols .= '</cols>';

        $merges = '<mergeCells count="'.count(self::MERGED_ROWS).'">';
        foreach (self::MERGED_ROWS as $r) {
            $merges .= '<mergeCell ref="B'.$r.':'.$lastDataCol.$r.'"/>';
        }
        $merges .= '</mergeCells>';

        $lastRow = max($headerRow, $rowIndex - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="'.self::NS_SPREADSHEET.'" xmlns:r="'.self::NS_DOC.'">'
            .'<sheetViews><sheetView workbookViewId="0">'
            .'<pane ySplit="9" topLeftCell="A11" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .$cols
            .$body
            .$merges
            .'<autoFilter ref="B'.$headerRow.':'.$lastDataCol.$lastRow.'"/>'
            .($hasImages ? '<drawing r:id="rId1"/>' : '')
            .'</worksheet>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="'.self::NS_SPREADSHEET.'">'
            .'<fonts count="7">'
            .'<font><sz val="11"/><color rgb="FF3D4A56"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><color rgb="FF1F3864"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="12"/><color rgb="FF1F3864"/><name val="Calibri"/></font>'
            .'<font><sz val="10"/><color rgb="FF3D4A56"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="13"/><color rgb="FF042C53"/><name val="Calibri"/></font>'
            .'<font><i/><sz val="10"/><color rgb="FF7F7F7F"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="3">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF042C53"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="1">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="7">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="6" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private static function imagePayload(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $bytes = (string) file_get_contents($path);

        return $bytes === '' ? null : $bytes;
    }

    private static function sanitizeSheetName(string $name): string
    {
        $name = preg_replace('/[\\\\\/\?\*\[\]:]/', '', $name) ?: 'Report';

        return mb_substr($name, 0, 31);
    }

    private static function colName(int $index): string
    {
        $index++;
        $name = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $name = chr(65 + $mod).$name;
            $index = intdiv($index - 1, 26);
        }

        return $name;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}