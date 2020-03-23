<?php

namespace Spatie\MailcoachMailgunFeedback;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Models\Send;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\ProcessWebhookJob;
use Spatie\Mailcoach\Support\Config;

class ProcessMailgunWebhookJob extends ProcessWebhookJob
{
    public function __construct(WebhookCall $webhookCall)
    {
        parent::__construct($webhookCall);

        $this->queue = config('mailcoach.perform_on_queue.process_feedback_job');

        $this->connection = $this->connection ?? Config::getQueueConnection();
    }

    public function handle()
    {
        $payload = $this->webhookCall->payload;

        if (!$send = $this->getSend()) {
            return;
        };

        $mailgunEvent = MailgunEventFactory::createForPayload($payload);

        $mailgunEvent->handle($send);
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
