*/
Bitte README.md lesen.
*/

<?php
require_once '../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;

$params = [
    'dbname' => 'reisebuero',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost:3306',
    'driver' => 'pdo_mysql',
];
$conn = DriverManager::getConnection($params);

$qb = new QueryBuilder($conn);
$qb = $qb->select('r.pk_reiseNr','a.datumAbreise', 'r.reiseBezeichnung', 'r.reiseBeschreibung')
    ->from('reise', 'r')->join('r', 'reiseangebot', 'a' );

$reisen = $qb->fetchAllAssociative();

$type = $_GET['type'] ?? 'json';

if ($type === 'xml') {
    $xml = new SimpleXMLElement('<reisen/>');

    foreach ($reisen as $reise) {
        $reiseXml = $xml->addChild('reise');
        $reiseXml->addChild('id', $reise['pk_reiseNr']);
        $reiseXml->addChild('datum', $reise['datumAbreise']);
        $reiseXml->addChild('titel', $reise['reiseBezeichnung']);
        $reiseXml->addChild('beschreibung', $reise['reiseBeschreibung']);
    }

    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;

    file_put_contents(__DIR__ . '/reisen.xml', $dom->saveXML());

    header('Content-Type: application/xml');
    echo $dom->saveXML();

} else {
    $json = json_encode($reisen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents(__DIR__ . '/reisen.json', $json);
    header('Content-Type: application/json');
    echo $json;
}
if (!$dom->schemaValidate(__DIR__ . '/reisen.dtd')) {
    die('Ungültige XML-Datei gemäß Schema!');
}