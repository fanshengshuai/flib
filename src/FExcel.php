<?php
class FExcel
{
    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
    private $footer = "</Workbook>";
    public function __construct($sEncoding = 'UTF-8', $bConvertTypes = false)
    {
        $this->bConvertTypes = $bConvertTypes;
        $this->setEncoding($sEncoding);
        // $this->setWorksheetTitle($sWorksheetTitle);
    }

    public function startOutput($filename, $charset = 'UTF-8')
    {
        ob_end_flush(); //关闭缓存
        ob_implicit_flush(true); // TRUE 打开绝对刷送 每次缓存即时输出 相当于每次输出后调用flush（）
        header('X-Accel-Buffering: no'); //关闭输出缓存

        $this->sEncoding = 'UTF-8';

        header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

        echo stripslashes(sprintf($this->header, $this->sEncoding));
    }

    public function addWorksheet($title)
    {
        $title = preg_replace("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
        $title = substr($title, 0, 31);
        $this->sWorksheetTitle = $title;

        echo "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n";
    }

    public function addRow($array)
    {
        $cells = "";
        foreach ($array as $k => $v):
            $type = 'String';
            if ($this->bConvertTypes === true && is_numeric($v)):
                $type = 'Number';
            endif;
            $v = htmlentities($v, ENT_COMPAT, $this->sEncoding);
            $cells .= "<Cell><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
        endforeach;
        echo "<Row>\n" . $cells . "</Row>\n";

        ob_flush();
        flush(); //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
    }

    public function end()
    {
        echo "</Table>\n</Worksheet>\n";
        echo $this->footer;

        ob_flush();
        flush(); //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
    }

    public function setEncoding($sEncoding)
    {
        $this->sEncoding = $sEncoding;
    }
}
