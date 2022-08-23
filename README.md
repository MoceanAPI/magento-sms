# MoceanAPI Magento SMS Extension

## Contents
- [Features](#features)
- [Installation](#installation)
- [Frequently Asked Questions](#faq)

## Features
- Automatically send SMS to admin on new orders
- Automatically send SMS customers when order status changed

## Installation

1. Navigate to your `Magento` root directory

2. Download `MoceanAPI Magento SMS Extension using command below`

`composer require mocean/magento-sms`

3. Enable the extension and clear static view files using below command

`php bin/magento module:enable Mocean_Sms --clear-static-content`

4. Register the extension using below command

`php bin/magento setup:upgrade`

5. Clean the cache using below command

`php bin/magento cache:clean`

## FAQ
1. Can I get Test Credits ?

You can get 20 FREE credits and credits are valid for 14 days. Subject to approval.

2. Can I send international messages?

Yes. We are an international SMS provider. You can send out SMS both locally and internationally based on our price list.

3. What is the maximum characters per SMS I can put into the message?

160 characters for a normal text message, 70 characters for a Unicode text message (Arabic, Chinese, and etc)

4. Is there a limit to how many numbers I can send at one time?

There is no limit on numbers to be sent in one go.

5. What format does my phone number need to be in?

Mobile phone numbers need to be entered in international formatting with the country code and without spaces, plus signs or leading zeros.

**Did not find what you're looking for ?**

Raise a support ticket with our Support Team at support@moceanapi.com.

## Feature Request / Get Help
Raise a support ticket with our Support Team at support@moceanapi.com.
