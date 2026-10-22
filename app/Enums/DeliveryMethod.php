<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case ReceiverPickup = 'receiver_pickup';
    case TravelerDelivers = 'traveler_delivers';
}
