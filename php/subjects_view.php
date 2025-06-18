<?php
/*
 * subjects_view.php
 *
 * Generates and optionally downloads an HTML view of all subjects within a faculty.
 * Uses XML + XSLT transformation to render the data, with schema validation.
 */

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: Toplogin.php');
    exit;
}

$facultyId = filter_input(INPUT_GET, 'faculty_id', FILTER_VALIDATE_INT);
if (!$facultyId) {
    header('Location: Topdashboard.php');
    exit;
}

$isDownload = (isset($_GET['download']) && $_GET['download'] === 'html');
if ($isDownload) {
    header('Content-Type: text/html; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"subjects_faculty_{$facultyId}.html\"");
}

ob_start();
$_GET['faculty_id'] = $facultyId;
include __DIR__ . '/export_subjects.php';
$xmlString = ob_get_clean();

libxml_use_internal_errors(true);
$xml = new DOMDocument();
if (!$xml->loadXML($xmlString)) {
    foreach (libxml_get_errors() as $e)
        error_log($e->message);
    die('Error: Malformed XML (check log).');
}
if (!$xml->schemaValidate(__DIR__ . '/subjects.xsd')) {
    foreach (libxml_get_errors() as $e)
        error_log($e->message);
    die('Error: XML does not conform to schema (check log).');
}

$xsl = new DOMDocument();
if (!$xsl->load(__DIR__ . '/subjects.xsl')) {
    die('Error loading subjects.xsl');
}
$proc = new XSLTProcessor();
$proc->importStylesheet($xsl);
echo $proc->transformToXML($xml);
exit;
