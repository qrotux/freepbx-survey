#!/usr/bin/php -q
<?php
$argv = $_SERVER['argv'];

require('_utils.php');

$VM_CONTEXT = $argv[1];
$VM_EXT = $argv[2];
$VM_INDEX = $argv[3] - 1;

$VM_DIR = "/var/spool/asterisk/voicemail/default/${VM_EXT}/INBOX/";
$VM_PREFIX = 'msg' . str_pad($VM_INDEX, 4, '0', STR_PAD_LEFT);

$VM_FILE = $VM_DIR . $VM_PREFIX . '.wav';
$VM_INFO = $VM_DIR . $VM_PREFIX . '.txt';

write_log(sprintf('info #%s: %s, %s', $VM_INDEX, $VM_INFO, (int)file_exists($VM_INFO)));

$info = parse_ini_file($VM_INFO);

$cid = $info['callerid'];
$time = $info['origtime'];
$duration = $info['duration'];

// Skip if duration is nan
if (!$duration) {
    exit;
}

preg_match('/^([\+0-9]+).*$/', $cid, $matches);

$cid = isset($matches[1]) ? $matches[1] : $cid;
$date = date('Y-m-d H:i:s', (int)$time);

function telegram_sendMessageVoicemail($cid, $time, $duration, $index = null) {

    $message = $index ? "#${index}\n" : '';

    $message .= <<<TXT
Сообщение от: *${cid}*
Дата: *${time}*
Длительность: *${duration} сек*
TXT;

    telegram_send('sendMessage', array('text' => $message, 'parse_mode' => 'Markdown'));
};


telegram_sendMessageVoicemail($cid, $date, $duration, $VM_INDEX);
telegram_sendAudio($cid, $VM_FILE);
