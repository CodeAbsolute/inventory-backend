<?php

use Illuminate\Support\Facades\Mail;

function sendEmail($filename, $data)
{
  try {
    Mail::send($filename, ['data' => $data], function ($message) use ($data) {
      $message
        ->to($data['email'])
        ->subject($data['title'])
        ->from('mahesh.gajakosh@peerconnexions.com');
    });
  } catch (Exception $e) {
    throw new Exception($e->getMessage());
  }
}