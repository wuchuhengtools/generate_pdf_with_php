<?php

namespace Wuchuheng\GeneratePdf;

require __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use TCPDF;
use Wuchuheng\GeneratePdf\Seeds\SeedData;

class MYPDF extends TCPDF {
    public $_sideMargin= 10;

    // 表格边框style
    public  $tableBorder = ['LTRB' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => [189, 189, 189])];

    // 微软雅黑字体: 这个手动导入，详细说明看README.md
    public $font= 'microsoftyahei';

    //Page header height
    private $headerHeight = 30;
    private $footHeight = 15;
    public function Header():void {
        $border = 0;
        // Set font
        $this->setFont($this->font, 'B', 13);
        $cellWidth = $this->getBodyWidth() / 3;
        $cellHeight = 10;
        // Set the image path
        $imagePath = __DIR__ . "/public/assets/images/logo#1.png";
        // Output the image
        $this->Image($imagePath, $this->GetX(), $this->GetY(), 20 );
        // Add the second column in the header
        $this->Cell($cellWidth, $cellHeight, '', $border, 0, 'L');
        $this->Cell($cellWidth, $cellHeight, 'i 智能家居方案(预算版)', $border, 0, 'C');
        // Add the third column in the header
        $x = $cellWidth * 2 + $this->_sideMargin;
        $y = $this->GetY();
        $this->MultiCell($cellWidth, $cellHeight / 2, '<p style="font-size: 12px">以 AI 致 美 好 生 活</p>', $border=$border, $align='R', $fill=false, $ln=1, $x=$x, $y=$y,  $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=0, $valign='T', $fitcell=false);
        $this->MultiCell($cellWidth, $cellHeight / 2, '<a href="https://www.orvibo.com/" style="text-decoration: none;color: inherit;font-size: 10px">www.orvibo.com</a>', $border=$border, $align='R', $fill=false, $ln=1, $x=$x, $y=$y + $cellHeight / 2,  $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=0, $valign='T', $fitcell=false);
        // 添加分割线
        $this->MultiCell($this->getPageWidth() - 2 * $this->_sideMargin, 0.5, '', array('B' => $this->tableBorder['LTRB']), $align='J', $fill=false, $ln=1, $x=$this->GetX(), $y=$this->GetY() + 1,  $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false);
    }

    public function getBodyWidth(): float {
        return $this->getPageWidth() - $this->_sideMargin * 2;
    }

    // Page footer
    public function Footer():void {
        // Position at 15 mm from bottom
        $this->setY(-$this->footHeight);
        $this->setFont($this->font, 'B', 8);
        $cellWidth = $this->getBodyWidth() / 2;
        // Page number
        $border = 0;
        $footHeight = $this->footHeight;
        $this->Cell($cellWidth, $footHeight, '欧瑞博(总部) 杨总 13927068998', $border, false, 'L', 0, '', 0, false, 'T', 'M');
        $totalPage = $this->getAliasNbPages();
        $currentPageNo = $this->getAliasNumPage();
        $this->Cell($cellWidth, $footHeight, "第 {$currentPageNo} 页 / 共 {$totalPage} 页", $border, false, 'R', 0, '', 0, false, 'T', 'M');
    }

    // 行最小高度
    private  $_miniRowHeight = 10;
    // 写入表格第一行
    public function writeFirstTableRowInBody( $headerData) {
        $this->setFont($this->font, '', 10);
        $x = $this->_sideMargin;
        $y = $this->GetY();
        $this->SetFillColor(232, 232, 232); // Red color (RGB values)
        foreach ( $headerData as $index => $item) {
            $text = $item['value'];
            $width = $item['width'];
            $this->Rect($x, $y, $width - 0.4, $this->_miniRowHeight, 'F');
            $this->MultiCell(
                $width,
                $this->_miniRowHeight,
                $text,
                $this->tableBorder,
                $align='C', $fill=false, $ln=1, $x, $y, $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true);
            $x += $width;
        }
    }


    /**
     *  计算出当前行最大的高度
     * @param array $rowData
     * @return float
     */
    public function calculateGetRowMaxHeight(array $rowData): float {
        $maxHeight = $this->_miniRowHeight;
        foreach ($rowData as $index => $item) {
            $headerRowInfo  = SeedData::getHeaderCellByIndex($index, $this->getBodyWidth());
            switch ($headerRowInfo['name']) {
                case "#5":
                    $text = $item['isDiscount'] ? "{$item['oldPrice']}\n{$item['currentPrice']}" : $item['currentPrice'];
                    break;
                default:
                    $text = $item;
            }
            $width = $headerRowInfo['width'];

            $cHeight= $this->GetStringHeight($width, $text);
            $maxHeight = max($cHeight, $maxHeight);
        }
        return $maxHeight;
    }
    // 取消色,用于划线
    public $cancelColor = [197, 200, 209];

    // 字的默认色彩
    public $textColor = [0, 0, 0];

    // 写入表格其它行
    public function writeTableRowsInBody($tableRows) {
        // 设置字体
        $this->setFont($this->font, '', 9);
        // 在空白页面中先计算出每一行的可能的最大高度
        $indexMapCellHeight = $this->_calculateRowMaxHeight($tableRows);
        foreach ($tableRows as $rowIndex => $row) {
            $y = $this->GetY();
            $x = $this->_sideMargin;
            $this->setX($this->_sideMargin);
            $cellHeight = $this->calculateGetRowMaxHeight($row);
            foreach ($row as $index => $cell) {
                $cellHeaderRowInfo = SeedData::getHeaderCellByIndex($index, $this->getBodyWidth());
                $border = $this->tableBorder;
                $width = $cellHeaderRowInfo['width'];
                $text = $cellHeaderRowInfo['name'] != "#5" ? $cell  : $cell['currentPrice'];
                switch ($cellHeaderRowInfo['name']) {
                    // 图片列
                    case "#2":
                        $text = __DIR__ . "/" . $cell;
                        $imgSize = 10;
                        $imageX = ($width - $imgSize) / 2 + $x;
                        $imageY = ($cellHeight - $imgSize) / 2 + $y;
                        $this->Image($text,
                            $imageX,
                            $imageY,
                            $imgSize, $imgSize,
                            $type='',
                            $link='',
                            $align='C',
                            $resize=false,
                            $dpi=300,
                            $palign='',
                            $ismask=false,
                            $imgmask=false,
                            $border=1,
                            $fitbox=true,
                            $hidden=false,
                            $fitonpage=false,
                            $alt=false,
                            $altimgs=array()
                        );
                        $this->MultiCell(
                            $width,
                            $cellHeight,
                            '',
                            $border,
                            $align='C', $fill=false, $ln=1, $x, $y,
                            $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
                        );
                        break;
                    // 价格列处理
                    case "#5":
                        // 取消色彩A;
                        if (!$cell['isDiscount']) {
                            $text = $cell['currentPrice'];
                            $this->MultiCell(
                                $width,
                                $cellHeight,
                                $text,
                                $border,
                                $align='C', $fill=false, $ln=1, $x, $y,
                                $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
                            );
                        } else {
                            // 有下划线的部分
                            $text = $cell['oldPrice'];
                            $this->SetTextColor(...$this->cancelColor); // Red color
                            $this->MultiCell(
                                $width,
                                $cellHeight / 2,
                                $text,
                                0,
                                $align='C', $fill=false, $ln=1, $x, $y,
                                $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='B', $fitcell=true
                            );
                            // 画条上部线
                            $this->MultiCell(
                                $width,
                                $cellHeight / 2,
                                '',
                                ['T' => $border['LTRB']],
                                $align='C', $fill=false, $ln=1, $x , $y,
                                $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=true
                            );
                            // 画条横线
                            // Calculate the line position
                            $fontHeight = $this->getStringHeight($width, $text);
                            $textY = $y + $cellHeight / 2 - $fontHeight / 2;
                            $textWidth = $this->GetStringWidth($text);
                            $textLineX = $x + ($width - $textWidth) / 2;
                            // Draw the line
                            $this->Line($textLineX, $textY, $textLineX + $textWidth, $textY, ['color' => $this->cancelColor, 'width' => 0.3]);
                            $this->SetTextColor(...$this->textColor); // Red color
                            $this->MultiCell(
                                $width,
                                $cellHeight / 2,
                                $cell['currentPrice'],
                                0,
                                $align='C', $fill=false, $ln=1, $x , $y +  $cellHeight / 2,
                                $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=true
                            );
                            // 画条底下线
                            $this->MultiCell(
                                $width,
                                $cellHeight / 2,
                                '',
                                ['B' => $border['LTRB']],
                                $align='C', $fill=false, $ln=1, $x , $y +  $cellHeight / 2,
                                $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=true
                            );
                        }
                        break;
                        // 产品说明列
                    case "#9":
                        $this->MultiCell(
                            $width,
                            $cellHeight,
                            $text,
                            $border,
                            $align='L', $fill=false, $ln=1, $x, $y,
                            $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
                        );
                        break;
                    // 其它列处理
                    default:
                        $text = $cell;
                        $this->MultiCell(
                            $width,
                            $cellHeight,
                            $text,
                            $border,
                            $align='C', $fill=false, $ln=1, $x, $y,
                            $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
                        );
                }
                $x += $width;
            }
            // 是否还有下一列，有则判断当前页面有没有足够的空间写入新的一行表格数据
            if (count($tableRows) > $rowIndex + 1) {
                $cellHeight = $indexMapCellHeight[$rowIndex + 1];
                // 如果页面的高度不足够写新的一行，则添加新的一页
                $maxHeight = $this->getPageHeight() - $this->footHeight;
                if ($this->GetY() + $cellHeight + 10 > $maxHeight) {
                     $this->AddPage();
                }
            }
        }
    }

    private function _writeNo1CellForLastRow($rowHeight, $name, $border) {
        // 第一行
        $fistCellWidth = $this->getBodyWidth() * 0.8;
        $x = $this->_sideMargin;
        $y = $this->GetY();
        $text = $name;
        $this->MultiCell(
            $fistCellWidth,
            $rowHeight,
            $text,
            $border,
            $align='R',
            $fill=false,
            $ln=1,
            $x,
            $y,
            $reseth=true,
            $stretch=0,
            $ishtml=false,
            $autopadding=true,
            $maxh=0,
            $valign='M',
            $fitcell=true
        );
    }
    // 汇总行，第一行最后一列
    private function _writeNo1RowLastCellForSummaryRow(array $summaryData, float $No2CellWidth, float $rowHeight, float $x, float $y, float $fistCellWidth, int $border): void {
        if (!$summaryData['productDesignFee']['isDiscount']) {
            $this->MultiCell(
                $No2CellWidth,
                $rowHeight,
                $summaryData['productDesignFee']['discountFee'],
                $border,
                $align='R',
                $fill=false,
                $ln=1,
                $x + $fistCellWidth,
                $y,
                $reseth=true,
                $stretch=0,
                $ishtml=false,
                $autopadding=true,
                $maxh=0,
                $valign='M',
                $fitcell=true
            );

        } else {
            $this->MultiCell(
                $No2CellWidth,
                $rowHeight / 2,
                $summaryData['productDesignFee']['discountFee'],
                $border,
                $align='R',
                $fill=false,
                $ln=1,
                $x + $fistCellWidth,
                $y,
                $reseth=true,
                $stretch=0,
                $ishtml=false,
                $autopadding=true,
                $maxh=0,
                $valign='B',
                $fitcell=true
            );
            $this->SetTextColor(...$this->cancelColor); // set canncel color
            $text = $summaryData['productDesignFee']['originalFee'];
            $this->MultiCell(
                $No2CellWidth,
                $rowHeight / 2,
                $text,
                $border,
                $align='R',
                $fill=false,
                $ln=1,
                $x + $fistCellWidth,
                $y + $rowHeight / 2,
                $reseth=true,
                $stretch=0,
                $ishtml=false,
                $autopadding=true,
                $maxh=0,
                $valign='T',
                $fitcell=true
            );
            $this->SetTextColor(...$this->textColor); // set canncel color
            // 画条横线
            // Calculate the line position
            $textWidth = $this->GetStringWidth($text);
            $fontHeight = $this->getStringHeight($No2CellWidth, $text);
            $textY = $y + $rowHeight / 2 + $fontHeight / 2 + 0.5;
            $textLineX = $this->getBodyWidth() + $this->_sideMargin - $textWidth;
            // Draw the line
            $this->Line($textLineX, $textY, $textLineX + $textWidth, $textY, ['color' => $this->cancelColor, 'width' => 0.3]);
            // 写入第2行
        }
    }

    private function _writeNo2RowLastCellForSummaryRow(float $No2CellWidth, float $rowHeight, array $summaryData, float $x, float $y, float $fistCellWidth, int $border): void {
        $this->MultiCell(
            $No2CellWidth,
            $rowHeight,
            $summaryData['serviceFee'],
            $border,
            $align='R',
            $fill=false,
            $ln=1,
            $x + $fistCellWidth,
            $y,
            $reseth=true,
            $stretch=0,
            $ishtml=false,
            $autopadding=true,
            $maxh=0,
            $valign='M',
            $fitcell=true
        );
    }

    private function _writeNo3RowLastCellForSummaryRow(float $No2CellWidth, float $rowHeight, array $summaryData, float $x, float $y, float $fistCellWidth, int $border): void {
        $this->MultiCell(
            $No2CellWidth,
            $rowHeight,
            $summaryData['total'],
            $border,
            $align='R',
            $fill=false,
            $ln=1,
            $x + $fistCellWidth,
            $y,
            $reseth=true,
            $stretch=0,
            $ishtml=false,
            $autopadding=true,
            $maxh=0,
            $valign='M',
            $fitcell=true
        );
    }

    private function _writeSummaryRowBorder($width, $rowHeight, $x, $y, $fistCellWidth, $border) {
        $this->MultiCell(
            $width,
            $rowHeight,
            '',
            $border,
            'R',
            false,
            1,
            $x,
            $y
        );
    }


    /**
     * 获取在页面中有效的最大Y轴数
     * @return float
     */
    private function _getMaxYInBody() {
        // :xxx 我不知道这里为什么要减50,但只有减去50才能让换页判断生效，不然就排版短码
        return $this->getPageHeight() + $this->headerHeight - 50;
    }

    // 写入表格最后一行
    public function writeLastRow($summaryData) {
        // 每一行的高度
        $rowHeight = 10;
        // 第一行
        $fistCellWidth = $this->getBodyWidth() * 0.8;
        $No2CellWidth = $this->getBodyWidth() - $fistCellWidth;
        $border = 0;
        // 如是当前页面高度不足以写入新行的高度，则换页
        if($this->_getMaxYInBody() < $this->GetY() + $rowHeight * 3) {
          $this->AddPage();
        }
        $x = $this->_sideMargin;
        $y = $this->GetY();
        // 写入第1行第1列
        $this->_writeNo1CellForLastRow($rowHeight, '产品设计：', $border);
        // 写入第1行最后1列
        $this->_writeNo1RowLastCellForSummaryRow($summaryData, $No2CellWidth, $rowHeight, $x, $y, $fistCellWidth, $border);
        // 写入第2行，第1列
        $this->_writeNo1CellForLastRow($rowHeight, '服务费：', $border);
        // 写入第2行，第2列
        $this->_writeNo2RowLastCellForSummaryRow($No2CellWidth, $rowHeight, $summaryData, $x, $y + $rowHeight, $fistCellWidth, $border);
        // 写入第3行，第1列
        $this->_writeNo1CellForLastRow($rowHeight, '总计：', $border);
        // 写入第3行，第2列
        $this->_writeNo3RowLastCellForSummaryRow($No2CellWidth, $rowHeight, $summaryData, $x, $y + $rowHeight * 2, $fistCellWidth, $border);
        // 画个行的边框
        $this->_writeSummaryRowBorder($this->getBodyWidth(), $rowHeight * 3, $x, $y, $fistCellWidth, $this->tableBorder);
    }


    /**
     * 写入用户信息
     * @return void
     */
    public function writeUserInfo($userInfo)
    {
        // 在pdf中写入空行
        $rowHeight = 30;
        $emptyLIne = 10;
        // 计算是否需要换页
        $isOk = $this->_getMaxYInBody() < $this->GetY() + $rowHeight + $emptyLIne;
        $isOk ? $this->AddPage() : $this->Ln(10);
        // 画个框
        $x = $this->_sideMargin;
        $y = $this->GetY();
        $order = 0;
        $this->MultiCell(
            $this->getBodyWidth() / 2,
            $rowHeight,
            '',
            $order,
            $align='R',
            $fill=false,
            $ln=1,
            $x,
            $y
        );
        // 写入用户信息第1行第1列
        $this->_writeUserInfoNo1CellNo1Row(
            $this->getBodyWidth() / 2,
            $rowHeight / 2,
            $x,
            $y, "客户名称：{$userInfo['name']} 户型：{$userInfo['category']} 联系电话：{$userInfo['phone']} 地址：{$userInfo['address']}",
            $order
        );
        // 写入用户信息第1行第2列
        $this->_writeUserInfoNo1CellNo1Row(
            $this->getBodyWidth() / 2,
            $rowHeight / 2,
            $x,
            $y + $rowHeight / 2,
            '方案说明：',
            $order
        );
        // 写入第一行第2列第一行
        $x = $this->getBodyWidth() + $this->_sideMargin - 30;
        $qrSize = 90;
        $this->_writeUserInfoNo1CellNo2Row(
            $userInfo['qrCode'],
            __DIR__ . "/public/assets/images/logo#3.png",
            $x,
            $y,
          $qrSize
        );
        // 写入第一行第2列第一行
        $this->_writeUserInfoNo1CellNo2RowCell2($this->getBodyWidth(), $y);
    }

    private function _writeUserInfoNo1CellNo2RowCell2($x, $y) {
        $this->setFont($this->font, 'B', 9);
        $this->MultiCell(
            30,
            0,
            "微信扫描查看\n设计方案",
            0,
            $align='C',
            $fill=false,
            $ln=1,
            $x - 20,
            $y + 30 ,
            $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
        );
    }

    private function _writeUserInfoNo1CellNo2Row($text, $logo, $x, $y, $size)
    {
        $qrCode = new QrCode($text);

        $qrCode->setSize($size);
        $qrCode->setLogoPath($logo);
        $qrCode->setLogoWidth($size * 0.25); // Set the logo width (optional)
        $qrCode->setLogoHeight($size * 0.25); // Set the logo height (optional)
        $qrCode->setMargin(10);
//        $qrCode->setBackgroundColor([255, 255, 255]);
//        $qrCode->setForegroundColor([0, 0, 0]);
        $qrCode->setEncoding('UTF-8');

//        // Retrieve the base64 image data
        $base64Image = $qrCode->writeDataUri();
        // Decode the base64 image data
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
        // Add the image to the PDF
        $this->Image('@' . $imageData, $x, $y);
    }

    private function _writeUserInfoNo1CellNo1Row($width, $height, $x, $y, $text, $order)
    {
        $this->MultiCell(
            $width,
            $height,
            $text,
            $order,
            $align='L',
            $fill=false,
            $ln=1,
            $x,
            $y,
            $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
        );
    }

    public function writeDetail(array $detailItems)
    {
        $y = $this->GetY();
        foreach ($detailItems as $item) {
            // 写入标题
            $x = $this->_sideMargin;
            $textHeight = 10;
            $this->MultiCell(
                $this->getBodyWidth(),
                $textHeight,
                $item['title'],
                $border=0,
                $align='C',
                $fill=false,
                $ln=1,
                $x,
                $y,
                $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true
            );
            $y += $textHeight;
            foreach ($item['images'] as $index => $image) {
                $file = __DIR__ . "/{$image}";
                $imageHeight = 100;
                $imageWidth = $this->getBodyWidth();
                $headerHeight = 27;
                if ($this-> getPageHeight() - $this->footHeight - $y < $imageHeight) {
                    $this->AddPage();
                    $y = $headerHeight;
                }
                $this->Image(
                    $file,
                    $x,
                    $y,
                    $imageWidth,
                    $imageHeight,
                    '',
                    '',
                    'C',
                    true,
                    300,
                    $palign='',
                    $ismask=false,
                    $imgmask=false
                );
                $y += $imageHeight;
            }
        }
    }

    private function _calculateRowMaxHeight($tableRows)
    {
        $result = [];
        foreach ($tableRows as $row) {
            $maxHeight = 0;
            $index = 2;
            // 计算出产品字段内容需要的高度
            $headerInfo = SeedData::getHeaderCellByIndex($index, $this->getBodyWidth());
            $text = $row[$index];
            $maxHeight = max($this->getStringHeight($headerInfo['width'], $text), $maxHeight);
            // 计算出产品介绍字段内容需要的高度
            $index = 8;
            $text = $row[$index];
            $headerInfo = SeedData::getHeaderCellByIndex($index, $this->getBodyWidth());
            $maxHeight = max($this->getStringHeight($headerInfo['width'], $text), $maxHeight);
            $result[] = $maxHeight;
        }
        return $result;
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// 设置纸张为横向
$pdf->SetPageOrientation('L');
// Set the page margins (left, top, right, bottom)
$pdf->SetMargins($pdf->_sideMargin, 20, $pdf->_sideMargin, 20);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Nicola Asuni');
$pdf->setTitle('TCPDF Example 003');
$pdf->setSubject('TCPDF Tutorial');
$pdf->setKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();
$pdf->writeFirstTableRowInBody( Seeds\SeedData::getHeaderRowData($pdf->getBodyWidth()) ); // 写入表格头部
$pdf->writeTableRowsInBody( Seeds\SeedData::getTableRowData()); // 写入表格数据
//写入表格最后一行
$pdf->writeLastRow([
    'total' => '￥25054.00', // 总费用,
    // 服务费
    'serviceFee' => '￥25223.00',
    // 产品设计费
    'productDesignFee' => [
        // 是否打折
        'isDiscount' => true,
        // 打折后的费用
        'discountFee' => '￥25223.00',
        // 打折前的费用
        'originalFee' => '￥25223.00',
    ]
]);
// 写入用户信息
$pdf->writeUserInfo([
    'name' => '张三',
    'category' => 'xxx种类',
    'address' => '广东省深圳市南山区科技园',
    'phone' => '1342xxxxx90',
    'qrCode' => 'https://partner.orvibo.com//share/#/design/eyUcBzJ0ZW5hbnRJZCI6MSwib3JnSWQiOjE0NjMsImludGVudGlvbklkIjo0NjQwNTh9',

]);
//// 写入详细说明
$pdf->writeDetail([
    [
        'title' => '一室一厅C户型',
        'images' => [
        "public/assets/images/detail#1.png",
    ],
    ],
    [
        'title' => '一室一厅C户型#',
        'images' => [
        "public/assets/images/detail#1.png",
        "public/assets/images/detail#1.png",
    ],
    ]
]);


$outputFile =  "src/runtime/report_" . date("Ymd-His") .".pdf";
!is_dir( dirname($outputFile) ) && mkdir(dirname($outputFile));
$pdf->Output(__DIR__  . "/../" . $outputFile, 'F');
echo "{$outputFile}\n";

