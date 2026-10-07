<?php

namespace App\Notifications\Contracts;

interface FcmNotification
{
    public function toFcm(object $notifiable): array;
}
