<?php

namespace Spatie\MailcoachMailgunFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessMailgunWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.perform_on_queue.process_feedback_job');
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        if (!$send = $this->getSend()) {
            return;
        };

        $event = Arr::get($payload, 'event-data.event');

        if ($event === 'opened') {
            $send->registerOpen();

            return;
        }

        if ($event === 'clicked') {
            $url = Arr::get($payload, 'event-data.url');

            $send->registerClick($url);

            return;
        }

        if ($event === 'failed') {
            if (Arr::get($this->payload, 'event-data.severity') !== 'permanent') {
                $send->registerBounce();

                return;
            }
        }
    }

    protected function getSend(): ?Send
    {
        $messageId = Arr::get($this->webhookCall->payload, 'event-data.message.headers.message-id');

        if (!$messageId) {
            return null;
        }

        return Send::findByTransportMessageId($messageId);
    }
}
