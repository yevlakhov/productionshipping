<?php
require_once dirname(__FILE__).'/vendor/autoload.php';
include_once(dirname(__FILE__).'/cron_include.php');
include_once(dirname(__FILE__).'/../../config/config.inc.php');

$devEmail = Configuration::get('DEV_EMAIL');

if (is_file(dirname(__FILE__).'/cron.log') && $devEmail) {
$log = file_get_contents(dirname(__FILE__).'/cron.log');
$file_attachment = [
    'content' => $log,
    'name' => 'cron.log',
    'mime' => 'text/plain',
];

$mailVars = [
    '{shop_name}' => '',
    '{shop_logo}' => '',
    '{shop_url}' => '',
    '{manufName}' => '',
    '{devReport}' => '',
];
Mail::Send(
    1,
    'dev-report',
    'Cron Log',
    $mailVars,
    $devEmail,
    'Developer',
    null,
    null,
    $file_attachment,
    null,
    _PS_MODULE_DIR_.'productionshipping/mails/',
    true,
    null);
}