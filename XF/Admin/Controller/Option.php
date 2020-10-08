<?php

namespace Laneros\MailTransport\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class Option extends XFCP_Option
{
    public function actionEmailTransportSetup(ParameterBag $params)
    {
        $option = $this->assertEmailTransportOption($params->option_id);

        $view = parent::actionEmailTransportSetup($params);

        if ($this->isPost())
        {
            $newType = $this->filter('new_type', 'str');

            switch ($newType)
            {
                case 'mt_amazonses':
                    $viewParams = [
                        'option' => $option,
						'type' => $newType,
					];
					return $this->view('Laneros\MailTransport:Option\EmailTransportAmazonSes', 'mt_option_email_transport_amazonses', $viewParams);
            }
        }

        return $view;
    }

    public function actionEmailTransportAmazonses(ParameterBag $params)
    {
        $option_id = $params->get('option_id');

        $optionValue = $this->filter([
            'amazonses_access_key_id' => 'str',
            'amazonses_secret_access_key' => 'str'
        ]);

        $optionValue['emailTransport'] = 'amazonses';


        $this->getOptionRepo()->updateOption($option_id, $optionValue);

        return $this->redirect($this->getDynamicRedirect());
    }
}