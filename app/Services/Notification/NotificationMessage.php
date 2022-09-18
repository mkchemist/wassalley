<?php

namespace App\Services\Notification;

class NotificationMessage {


  private bool $mutable_content = false;

  private string $title;

  private string $description;

  private string $image;

  private array $extra;

  public function __construct(string $title = '', string $description = '', string $image = '', $extra = [])
  {
    $this->title = $title;
    $this->description = $description;
    $this->image = $image;
    $this->extra = $extra;
  }

  public function build(string $to):array
  {
    return [
      "to" => $to,
      "mutable_content" => $this->mutable_content,
      "data" => array_merge([
        "title" => $this->title,
        "body" => $this->description,
        "image" => $this->image
      ], $this->extra),
      "notification" => array_merge([
        "title" => $this->title,
        "body" => $this->description,
        "image" => $this->image
      ], $this->extra)
    ];
  }

}
