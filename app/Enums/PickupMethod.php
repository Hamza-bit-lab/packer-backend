<?php

namespace App\Enums;

/**
 * Who handles the origin-side handoff of an item — used on a Trip (the
 * traveler's stance: do they collect it themselves, or does the sender
 * bring it to them) and on a Good (the sender's stance: will they deliver
 * it to the traveler, or does the traveler need to come get it).
 */
enum PickupMethod: string
{
    case TravelerPicksUp = 'traveler_picks_up';
    case SenderDelivers = 'sender_delivers';
}
