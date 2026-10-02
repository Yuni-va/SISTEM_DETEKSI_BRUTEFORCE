<?php

session_start();

define("AUTH_LOGIN_PATH","../../login.php");
require "../middleware/Auth.php";

require "../../config/database.php";
require "../models/Report.php";

$model = new Report($conn);

$format = $_GET['format'] ?? "";
$from = $_GET['from'] ?? "";
$to = $_GET['to'] ?? "";

$summary = $model->summary($from,$to);
$detections = $model->detections($from,$to);

$period = reportPeriodLabel($from,$to);
$filenameDate = date("Ymd_His");
$exportedBy = $_SESSION['fullname'] ?? $_SESSION['username'] ?? "Administrator";

if($format === "excel"){
    exportExcel($summary,$detections,$period,$filenameDate,$exportedBy);
    exit;
}

if($format === "pdf"){
    exportPdf($summary,$detections,$period,$filenameDate,$exportedBy);
    exit;
}

header("Location: ../../reports.php");
exit;

function reportPeriodLabel($from,$to)
{
    if($from !== "" && $to !== ""){
        return $from." sampai ".$to;
    }

    if($from !== ""){
        return "Mulai ".$from;
    }

    if($to !== ""){
        return "Sampai ".$to;
    }

    return "Semua data";
}

function exportExcel($summary,$detections,$period,$filenameDate,$exportedBy)
{
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=report_bruteforce_".$filenameDate.".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF";
    ?>
    <table border="1">
        <tr>
            <th colspan="6" style="font-size:16px;background:#1f4e79;color:#ffffff;">Report Sistem Deteksi Brute Force</th>
        </tr>
        <tr>
            <td colspan="6">Periode: <?= htmlspecialchars($period) ?></td>
        </tr>
        <tr>
            <td colspan="6">Dicetak oleh: <?= htmlspecialchars($exportedBy) ?></td>
        </tr>
        <tr>
            <td colspan="6">Tanggal cetak: <?= date("d-m-Y H:i:s") ?></td>
        </tr>
        <tr><td colspan="6"></td></tr>
        <tr style="background:#d9edf7;font-weight:bold;">
            <th>Login Berhasil</th>
            <th>Login Gagal</th>
            <th>Blocked IP</th>
            <th>Threshold Logs</th>
            <th>Detection</th>
            <th>Periode</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($summary['success_login']) ?></td>
            <td><?= htmlspecialchars($summary['failed_login']) ?></td>
            <td><?= htmlspecialchars($summary['blocked_ip']) ?></td>
            <td><?= htmlspecialchars($summary['threshold']) ?></td>
            <td><?= htmlspecialchars($summary['detection']) ?></td>
            <td><?= htmlspecialchars($period) ?></td>
        </tr>
        <tr><td colspan="6"></td></tr>
        <tr style="background:#eeeeee;font-weight:bold;">
            <th>No</th>
            <th>Username</th>
            <th>IP Address</th>
            <th>Tipe</th>
            <th>Deskripsi</th>
            <th>Waktu</th>
        </tr>
        <?php
        $no = 1;
        while($row = mysqli_fetch_assoc($detections)){
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                <td><?= htmlspecialchars($row['detection_type']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= date('d-m-Y H:i:s',strtotime($row['detected_at'])) ?></td>
            </tr>
        <?php } ?>
        <?php if($no === 1){ ?>
            <tr>
                <td colspan="6">Data laporan belum tersedia.</td>
            </tr>
        <?php } ?>
        <tr><td colspan="6"></td></tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="2">Administrator</td>
        </tr>
        <tr style="height:70px;">
            <td colspan="4"></td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="2"><?= htmlspecialchars($exportedBy) ?></td>
        </tr>
    </table>
    <?php
}

function exportPdf($summary,$detections,$period,$filenameDate,$exportedBy)
{
    $lines = [
        "REPORT SISTEM DETEKSI BRUTE FORCE",
        "Periode     : ".$period,
        "Dicetak oleh: ".$exportedBy,
        "Tanggal     : ".date("d-m-Y H:i:s"),
        "",
        "RINGKASAN",
        "Login Berhasil : ".$summary['success_login'],
        "Login Gagal    : ".$summary['failed_login'],
        "Blocked IP     : ".$summary['blocked_ip'],
        "Threshold Logs : ".$summary['threshold'],
        "Detection      : ".$summary['detection'],
        "",
        "DETECTION LOGS",
        str_pad("No",4).str_pad("Username",18).str_pad("IP Address",18).str_pad("Tipe",16)."Waktu",
        str_repeat("-",76),
    ];

    $no = 1;

    while($row = mysqli_fetch_assoc($detections)){
        $lines[] =
            str_pad($no,4).
            str_pad(limitPdfText($row['username'],16),18).
            str_pad(limitPdfText($row['ip_address'],16),18).
            str_pad(limitPdfText($row['detection_type'],14),16).
            date('d-m-Y H:i:s',strtotime($row['detected_at']));
        $lines[] = "    ".limitPdfText($row['description'],90);
        $no++;
    }

    if($no === 1){
        $lines[] = "Data laporan belum tersedia.";
    }

    $lines[] = "";
    $lines[] = "";
    $lines[] = "Administrator,";
    $lines[] = "";
    $lines[] = "";
    $lines[] = "";
    $lines[] = $exportedBy;

    $pdf = buildSimplePdf($lines);

    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=report_bruteforce_".$filenameDate.".pdf");
    header("Content-Length: ".strlen($pdf));

    echo $pdf;
}

function limitPdfText($text,$limit)
{
    $text = preg_replace('/\s+/', ' ', (string) $text);

    if(strlen($text) <= $limit){
        return $text;
    }

    return substr($text,0,$limit - 3)."...";
}

function escapePdfText($text)
{
    return str_replace(
        ["\\","(",")","\r","\n"],
        ["\\\\","\\(","\\)","",""],
        $text
    );
}

function buildSimplePdf($lines)
{
    $objects = [];
    $pages = [];
    $linesPerPage = 48;
    $chunks = array_chunk($lines,$linesPerPage);
    $fontObjectNumber = 3;
    $nextObjectNumber = 4;

    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[$fontObjectNumber] = "<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>";

    foreach($chunks as $chunk){
        $pageObjectNumber = $nextObjectNumber++;
        $contentObjectNumber = $nextObjectNumber++;
        $pages[] = $pageObjectNumber." 0 R";

        $content = "BT /F1 10 Tf 40 800 Td ";

        foreach($chunk as $index => $line){
            if($index > 0){
                $content .= "0 -14 Td ";
            }

            $content .= "(".escapePdfText($line).") Tj ";
        }

        $content .= "ET";

        $objects[$pageObjectNumber] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ".$fontObjectNumber." 0 R >> >> /Contents ".$contentObjectNumber." 0 R >>";
        $objects[$contentObjectNumber] = "<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream";
    }

    $objects[2] = "<< /Type /Pages /Kids [".implode(" ",$pages)."] /Count ".count($pages)." >>";
    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach($objects as $number => $body){
        $offsets[$number] = strlen($pdf);
        $pdf .= $number." 0 obj\n".$body."\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
    $pdf .= "0000000000 65535 f \n";

    for($i = 1; $i <= count($objects); $i++){
        $pdf .= str_pad($offsets[$i],10,"0",STR_PAD_LEFT)." 00000 n \n";
    }

    $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
    $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

    return $pdf;
}
