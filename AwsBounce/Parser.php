<?php

namespace Laneros\MailTransport\AwsBounce;

use Laminas\Mail\Storage\Message;
use XF\EmailBounce\ParseResult;

class Parser extends \XF\EmailBounce\Parser
{
    public function parseMessage($content)
    {
        $message = json_decode($content);

        if (empty($message->notificationType)) {
            throw new \InvalidArgumentException("No Amazon SNS notification type provided: " . $content);
            return false;
        }

        $result = new ParseResult();

        switch ($message->notificationType) {
            case 'Bounce':
                $result->messageType = ParseResult::TYPE_BOUNCE;

                $recipient = reset($message->bounce->bouncedRecipients);
                $result->remoteStatus = $recipient->status;
                $result->remoteDiagnostics = $recipient->diagnosticCode;

                break;

            case 'Complaint':
                $recipient = reset($message->mail->destination);
                $result->messageType = ParseResult::TYPE_BOUNCE;

                $result->remoteStatus = '5.1.1'; //We just want to treat this complaint as a hard bounce
                $result->remoteDiagnostics = 'complaint';
                break;

            case 'AmazonSnsSubscriptionSucceeded':
                return false;
                break;

            default:
                throw new \InvalidArgumentException("Unsupported notification type: " . $content);
                return false;
        }

        $dt = new \DateTime($message->mail->timestamp);
        $result->date = intval($dt->format('U'));

        $result->textContent = null;
        $result->originalContent = null;

        $xToValidate = '';

        foreach ($message->mail->headers as $header) {
            if ($header->name == 'X-To-Validate') {
                $xToValidate = $header->value;
            }
        }

        if ($this->verpBase && !empty($xToValidate)) {
            if (preg_match('#([a-z0-9]+)\+([^\s]+)#i', $xToValidate, $match)) {
                $email = $match[2];
                $hmac = hash_hmac('md5', $email, $this->verpHmacKey);

                $result->recipientTrusted = (substr($hmac, 0, strlen($match[1])) === $match[1]);
                $result->recipient = $email;
            }
        } else {
            // no VERP enabled, so we need to trust the recipient that we find
            $result->recipientTrusted = true;
            $result->recipient = $recipient->emailAddress ?? $recipient;
        }

        return $result;
    }
}
