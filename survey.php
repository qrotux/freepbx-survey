#!/usr/bin/php -q
<?php
// parse the data from AGI
require('phpagi.php');
require('_utils.php');

define('IS_CLI', false);

$language = 'en';

if (IS_CLI) {
  $num = $argv[1];
  $operatorNumber = $argv[2];
  $operatorName = $argv[3];
  $queue = $argv[4];
  $valuation = $argv[5];
} else {
  $agi = new AGI();
  $num = $agi->request['agi_arg_1'];
  $operatorNumber = $agi->request['agi_arg_2']; // In format "Local/102@from-queue/n"
  $operatorName = $agi->request['agi_arg_3'];
  $queue = $agi->request['agi_arg_4'];
  $valuation = $agi->request['agi_arg_5'];

  preg_match('/\/(\d+)@/', $operatorNumber, $matches);

  if ($matches) {
    $operatorNumber = $matches[1];
  }
}

// date
$date = date("Y-m-d H:i:s");

if ($valuation > 5) {
  $valuation = 5;
}

$message = <<<TXT
*Call rate:*
Customer: :c:
Agent: :a: \[:n:]
Queue: :q:
Rate (1-5): :v:
Date: :d:
TXT;

if ($language === 'ru') {
  $message = <<<TXT
*Оценка звонка:*
Абонент: :c:
Агент: :a: \[:n:]
Очередь: :q:
Оценка (1-5): :v:
Дата: :d:
TXT;
}

$values = array(
  ':c:' => $num,
  ':a:' => $operatorName,
  ':n:' => $operatorNumber,
  ':q:'   => $queue,
  ':v:'   => $valuation,
  ':d:'   => $date,
);

$message = strtr($values);

write_log('message: ' . $message);

telegram_send('sendMessage', array('text' => $message, 'parse_mode' => 'Markdown'));
