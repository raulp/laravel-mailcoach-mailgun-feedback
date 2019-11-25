<?php

namespace Spatie\MailcoachMailgunFeedback\MailgunEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\CampaignSend;

class PermanentBounce extends MailgunEvent
{
    public function canHandlePayload(): bool
    {
        if ($this->event !== 'failed') {
            return false;
        };

        if (Arr::get($this->payload, 'event-data.severity') !== 'permanent') {
            return false;
        }

        return true;
    }

    public function handle(CampaignSend $campaignSend)
    {
        $campaignSend->markAsBounced();
    }
}
