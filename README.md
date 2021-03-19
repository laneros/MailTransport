# Amazon SES Bounces Support

This addon creates an endpoint in your forum that will be used by AWS SNS to send notifications about bounces and complaints.

Since it doesn't creates the notification topics automatically it is your job to create them in the AWS console. Luckily enough, it consist only of a few steps detailed below.

## Create SNS topic

* Go to your AWS Console and look for the Simple Notification Service.
* Go to Topics and hit *Create topic*.
* Choose Standard for the topic type.
* Give it a name, for example mysite-topic.
* Save the topic by pressing the *Create topic* button.

## Create the SNS subscription

Once your topic is created we need to subscribe it to your forum

* Scroll down in the topic page you just created and press the *Create subscription* button
* Make sure the Topic ARN is the one you created in the previous step.
* In the Protocol section choose HTTPS.
* In the Endpoint section, type the following address `https://www.yoursite.com/api/amazon-ses/bounce` (change the www.yoursite.com to match your own domain)
* If everything goes as planned, the subscription should be confirmed automatically by the addon and you'll be ready to go!

## Assign the SNS Topic to your SES Domain Name

Now you need to tell to AWS SES that the topic you created in the first section needs to be associated with the Bounces and Complaints notification for your forum domain name.

* Go to the SES Home in your AWS Console and press the Domains section under Identity Manager (SES console version 1) or to the Verified Identities under Configuration (SES console version 2)
* Select the domain name used to send emails from your forum.
* Go to the Notifications section and press the Edit Configuration button (SES console version 1) or the Edit button under the Feedback Notifications section (SES console version 2)
* For the Bounces and Complaint feedback, choose the SNS Topic you created in the first section of this tutorial. It's the same SNS Topic for both options.
* Hit the Save button and that's it! Amazon should send a notification to the addon every time there's a bounce or complaint for one of your emails.


The end goal of this tutorial is to have a subscription pointing to the addon's bounce handler, have it in a *Confirmed* status and assignned to your forum domain name. If something goes wrong please leave a comment so we can further improve this tutorial.