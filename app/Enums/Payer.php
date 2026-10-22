<?php

namespace App\Enums;

enum Payer: string
{
    case Sender = 'sender';
    case Receiver = 'receiver';
}
