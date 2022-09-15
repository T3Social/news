<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace news\acceptance;

use news\AcceptanceTester;

class NewsCest
{

    public function testCreateNews(AcceptanceTester $I)
    {
        $I->wantTo('create a news by form');
        $I->amAdmin();

        $I->amGoingTo('install the news module for space 1');
        $I->enableModule(1, 'news');
        $I->amOnSpace1('/news/manager');
        $I->waitForText('Create News');

        $I->amGoingTo('create a news for space 1');
        $I->click('Create News');
        $I->waitForText('Write a News article and define its settings.');

        $newsTitle = 'Test news';
        $I->fillField('News[title]', $newsTitle);
        $I->fillField('#news-article .humhub-ui-richtext', 'Test article');
        $I->click('[for="news-confirm_reading"]');
        $I->click('[for="news-send_notification"]');
        $I->click('button[type=submit]', '#news-form');
        $I->seeSuccess('Saved');
        $I->see($newsTitle);

        $I->amGoingTo('mark the created news as read');
        $I->amUser2(true);
        $I->amOnSpace1();
        $I->waitForText($newsTitle);
        $I->waitForText('Test article');
        $I->waitForText('Mark as read');
        $I->click('#news-mark-read');
        $I->waitForText('Unmark');
    }

}
