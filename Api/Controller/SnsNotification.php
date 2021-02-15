<?php

namespace Laneros\MailTransport\Api\Controller;

use Laneros\MailTransport\AwsBounce\Parser;
use XF\Api\Controller\AbstractController;

class SnsNotification extends AbstractController
{
    public function actionPost()
    {
        $content = $this->request()->getInputRaw();

        $message = json_decode($content);

        if (isset($message->notificationType) && $message->notificationType = 'AmazonSnsSubscriptionSucceeded') {
            return;
        }

        if (empty($message->Type)) {
            \XF::logError('Amazon SNS payload does not contains a type: ' . $content);
            return $this->apiError('Amazon SNS payload does not contains a type', 'amazon_no_type');
        }

        switch ($message->Type) {
            case 'SubscriptionConfirmation':
                if (!empty($message->SubscribeURL)) {
                    file_get_contents($message->SubscribeURL);
                }

                return $this->apiSuccess();
                break;
            case 'Notification':
                if (empty($message->Message)) {
                    \XF::logError('No message provided: ' . $content);
                    return $this->apiError('No message provided', 'amazon_no_message');
                }
                break;
            default:
                \XF::logError('Unsupported notification message: ' . $content);
                return $this->apiError('Unsupported notification message', 'amazon_no_notification_message');
        }

        $parser = $this->getParser();

        $processor = $this->getProcessor($parser);

        $processor->processMessage($content);


        return $this->apiSuccess();
    }

    protected function getParser()
    {
        $options = $this->app->options();

        $class = $this->app->extendClass('Laneros\MailTransport\AwsBounce\Parser');

        return new $class(
            $options->enableVerp ? $options->bounceEmailAddress : null,
            $this->app->config('globalSalt')
        );
    }

    protected function getProcessor(Parser $parser)
    {
        $class = $this->app->extendClass('XF\EmailBounce\Processor');

        return new $class($this->app, $parser);
    }

    public function allowUnauthenticatedRequest($action)
    {
        return $action === 'post';
    }
}
