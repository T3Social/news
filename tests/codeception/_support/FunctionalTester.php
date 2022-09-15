<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace news;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use yii\swiftmailer\Message;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \FunctionalTester
{
    use _generated\FunctionalTesterActions;
    
   /**
    * Define custom actions here
    */

    /**
     * Check notification was sent about new created news
     *
     * @param string $newsTitle
     * @param Space $space
     * @param User $user
     * @return bool
     */
    public function checkCreatedNewsInLastEmail($newsTitle, $space, $user)
    {
        return (bool) preg_match('/' . preg_quote($user->getDisplayName()). ' has created the News "' . preg_quote($newsTitle). '" in Space ' . preg_quote($space->getDisplayName()). './', $this->grapLastEmailText());
    }

    /**
     * @param string $login
     * @return User|null
     */
    public function getUser($login)
    {
        return User::findOne(['username' => $login]);
    }

    /**
     * Get all last sent email addresses
     *
     * @return string[]
     */
    public function getLastSentEmailAddresses(): array
    {
        $emailAddresses = [];

        $sentMessages = $this->grabSentEmails();
        foreach ($sentMessages as $sentMessage) {
            /* @var Message $sentMessage */
            $emailAddress = array_keys($sentMessage->getTo());
            if (isset($emailAddress[0])) {
                $emailAddresses[] = $emailAddress[0];
            }
        }

        return $emailAddresses;
    }

    /**
     * Get last sent email address
     *
     * @return string
     */
    public function getLastSentEmailAddress(): string
    {
        $lastSentEmailAddress = array_keys($this->grabLastSentEmail()->getTo());
        return $lastSentEmailAddress[0];
    }
}
