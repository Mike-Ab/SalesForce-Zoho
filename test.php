<?php
require_once __DIR__.'/autoload.php';

require_once __DIR__.'/../autoload.php';
require_once (__DIR__.'/aws/aws-autoloader.php');

$zoho = new \ZohoMapper\ZohoMapper('ff5196138d9b9112b7fe675a9c6025d0');
$lead = [
    'SMOWNERID'     => '696292000002259143',
    'Email'         => 'it'.time().'@testOnlytest.com.au',
    'First Name'    => 'Guzzlle',
    'Last Name'     => 'testttttt',
    'Mobile'        => '044040404040',
    'Description'   => 'This is a junk lead'
];
$zoho->insertRecord($record, $type);