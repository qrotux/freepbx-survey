<?php

date_default_timezone_set('Asia/Almaty');

define('TELEGRAM_BOT_TOKEN', '');
define('TELEGRAM_GROUP_ID', '-');

define('LOG_FILE', '/var/log/apps.log');


// Send message tp group
function telegram_send($command, $query)
{
    $token = TELEGRAM_BOT_TOKEN;
    $apiLink = "https://api.telegram.org/bot${token}/${command}";

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
        )
    ));

    $query = http_build_query($query + array(
            'chat_id' => TELEGRAM_GROUP_ID,
        ));

    $res = file_get_contents($apiLink . '?' . $query, false, $context);

    if (!$res) {
        $err = print_r($http_response_header, 1);
        write_log('response code: ', $err);
    }
}

// Convert WAV-file to Telegram OGG format and send to group
function telegram_sendAudio($cid, $file) {
    $oggFile = str_replace('.wav', '.ogg', $file);

    exec("ffmpeg -y -i ${file} -c:a libopus ${oggFile}");

    $token = TELEGRAM_BOT_TOKEN;
    $chat = TELEGRAM_GROUP_ID;

    $cmd = array(
        "curl -s -X POST \"https://api.telegram.org/bot${token}/sendVoice\"",
        "-F caption=\"${cid}\"",
        "-F chat_id=${chat}",
        "-F voice=\"@${oggFile}\"",
    );

    exec(implode(' ', $cmd));
//    exec("rm -f ${oggFile}");
};

function write_log($message) {
    if (!LOG_FILE) return;

    if (!file_exists(LOG_FILE)) {
        file_put_contents(LOG_FILE, '');
    }

    file_put_contents(LOG_FILE, sprintf("[%s]: %s\n", date('c'), $message), FILE_APPEND);
}
