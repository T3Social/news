<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace news\functional;

use news\FunctionalTester;

class NewsCest
{

    public function testSendEmailNotification(FunctionalTester $I)
    {
        $I->wantTo('send email notification');
        $I->amAdmin();

        $I->amGoingTo('install the news module for space 1');
        $I->enableModule(1, 'news');
        $I->amOnSpace1('/news/manager');
        $I->see('Create News');

        $I->amGoingTo('create a news for space 1');
        $I->click('Create News');
        $I->see('Write a News article and define its settings.');

        $newsTitle = 'Test news';
        $I->fillField('News[title]', 'Test news');
        $I->fillField('News[article]', 'Test article');
        $I->checkOption('#news-confirm_reading');
        $I->checkOption('#news-send_notification');
        $I->click('button[type=submit]', '#news-form');
        $I->see($newsTitle);

        $I->amGoingTo('find the created news in sidebar');
        $I->amUser2(true);
        $I->amOnSpace1();
        $I->see('Test news');

        $I->amGoingTo('find the created news in email notification');
        $sentMessageEmailAddresses = $I->getLastSentEmailAddresses();
        $checkEmails = ['admin@example.com', 'user2@example.com'];
        foreach ($checkEmails as $checkEmail) {
            if (!in_array($checkEmail, $sentMessageEmailAddresses)) {
                $I->see($checkEmail . ' is not found in sent emails');
            }
        }

        if (!$I->checkCreatedNewsInLastEmail($newsTitle, $I->getFixtureSpace(0), $I->getUser('Admin'))) {
            $I->see($I->getLastSentEmailAddress() . ' was not notified about new created news');
        }
    }

}
