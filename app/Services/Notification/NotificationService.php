<?php

namespace App\Services\Notification;

use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Http;

class NotificationService {

  static private $url = "https://fcm.googleapis.com/fcm/send";

  static private function getKey()
  {
    return Helpers::get_business_settings('push_notification_key');
  }


  static public function toDevice(string $token, NotificationMessage $message)
  {
    $key = self::getKey();
    $headers = [
      'Authorization' => "key={$key}",
      'Content-Type' => 'application/json'
    ];

    $notification = $message->build($token);

    $http = Http::withHeaders($headers)
    ->post(self::$url,$notification);

    if ($http->status() === 200) {
      return $http->json();
    } else {
      return "Unable to send message";
    }

  }

}
