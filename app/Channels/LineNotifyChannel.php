<?php

namespace App\Channels;

use App\Notifications\LineNotify;
use Exception;

class LineNotifyChannel
{
    /**
     * 发送指定的通知。
     *
     * @param  mixed  $notifiable
     * @param  \App\Notifications\LineNotify  $notification
     * @return void
     */
    public function send($notifiable, LineNotify $notification)
    {
        $data = $notification->toLineNotify($notifiable);

        if(!empty($data['token'])) {
            $apiUrl = "https://notify-api.line.me/api/notify";
            $params = [
                'message' => $data['message'],
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $data['token']
            ]);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }
}
