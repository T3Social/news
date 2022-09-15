<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\reminder\forms;

use humhub\modules\news\Module;
use humhub\modules\user\models\User;
use humhub\modules\news\models\News;
use humhub\modules\news\models\reminder\NewsReminder;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Throwable;
use Yii;
use yii\base\Model;
use yii\db\StaleObjectException;

class ReminderSettings extends Model
{
    const REMINDER_TYPE_NONE = 0;
    const REMINDER_TYPE_DEFAULT = 1;
    const REMINDER_TYPE_CUSTOM = 2;

    /**
     * @var ContentContainerActiveRecord
     */
    public $container;

    /**
     * @var News
     */
    public $news;

    /**
     * @var User
     */
    public $user = false;

    /**
     * @var NewsReminder[]
     */
    public $reminders;

    /**
     * @var boolean whether or not the defaults are currently loaded
     */
    private $isDefaultsLoaded;

    /**
     * @var boolean whether or not there are global or space level defaults available
     */
    private $hasDefaults;

    /**
     * @var integer
     */
    public $reminderType;

    public function init()
    {
        parent::init();
        $this->initReminders();
        $this->initFlags();
    }

    protected function initReminders()
    {
        $this->reminders = $this->loadReminder();
        if (count($this->reminders) < Module::MAX_REMINDER) {
            $this->reminders[] = new NewsReminder();
        }
    }

    protected function loadReminder($defaults = true)
    {
        if ($this->news) {
            return NewsReminder::getNewsLevelReminder($this->news, $this->user, $defaults);
        }

        return NewsReminder::getDefaults($this->container, $defaults);
    }

    public function getDefaults()
    {
        if ($this->isDefaultsLoaded) {
            return $this->reminders;
        }

        if ($this->news) {
            return NewsReminder::getNewsLevelDefaults($this->news, $this->user);
        }

        return NewsReminder::getDefaults();
    }

    private function initFlags()
    {
        $this->isDefaultsLoaded = null;
        $this->hasDefaults = null;
        $this->isDefaultsLoaded();
        $this->hasDefaults();

        if ($this->hasDefaults && $this->isDefaultsLoaded()) {
            $this->reminderType = static::REMINDER_TYPE_DEFAULT;
        } else if ($this->isDisabled()) {
            $this->reminderType = static::REMINDER_TYPE_NONE;
        } else {
            $this->reminderType = static::REMINDER_TYPE_CUSTOM;
        }
    }

    private function isDisabled()
    {
        if (count($this->reminders) === 1 && $this->reminders[0]->isNewRecord) {
            return true;
        }

        // check for explicitly disabled reminders
        foreach ($this->reminders as $reminder) {
            if ($reminder->disabled) {
                return true;
            }
        }
        return false;
    }

    public function rules()
    {
        return [
            ['reminderType', 'integer'],
        ];
    }

    /**
     * @return array
     */
    public static function getUnitSelection()
    {
        return [
            NewsReminder::UNIT_HOUR => Yii::t('NewsModule.reminder', 'Hour'),
            NewsReminder::UNIT_DAY => Yii::t('NewsModule.reminder', 'Day'),
            NewsReminder::UNIT_WEEK => Yii::t('NewsModule.reminder', 'Week'),
        ];
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool
     * @throws Throwable
     */
    public function load($data, $formName = null)
    {
        // Keep this position, we need reminderType after this line
        $parentLoad = parent::load($data, $formName);

        $reminderLoaded = false;

        if (!$parentLoad) {
            return false;
        }

        if ($this->isReminderType(static::REMINDER_TYPE_DEFAULT) || $this->isReminderType(static::REMINDER_TYPE_NONE)) {
            $this->reminders = [];
        } else if (isset($data[NewsReminder::instance()->formName()])) {
            $this->reminders = [];
            $reminderLoaded = true;
            foreach ($data[NewsReminder::instance()->formName()] as $reminderData) {
                $reminder = $this->initReminder();
                $reminder->load($reminderData, '');
                $reminder->ensureValidValue();
                $this->reminders[] = $reminder;

                if (count($this->reminders) >= Module::MAX_REMINDER) {
                    break;
                }
            }
        }

        return $reminderLoaded || $parentLoad;
    }

    public function isReminderTypeUseDefault()
    {
        return $this->isReminderType(static::REMINDER_TYPE_DEFAULT);
    }

    public function isReminderType($type)
    {
        return $type == $this->reminderType;
    }

    public function save()
    {
        // Delete all reminder which do not match a submitted one
        $preservedReminders = $this->reset(true);

        $result = [];
        foreach ($this->reminders as $newReminder) {
            if ($this->news) {
                $newReminder->news_id = $this->news->id;
            }

            $newReminder = $this->findReminder($newReminder, $preservedReminders) ?: $newReminder;

            if (!empty($newReminder->value)) {
                $newReminder->save();
                $result[] = $newReminder;
            }
        }

        $this->reminders = $result;

        // Check for disabled reminder if not global settings
        if (empty($this->reminders) && $this->hasDefaults && !$this->isReminderTypeUseDefault() && !$this->isGlobalSettings()) {
            $this->initReminder(1)->save();
        }

        if (count($this->reminders) < Module::MAX_REMINDER) {
            $this->reminders[] = new NewsReminder();
        }

        if ($this->isGlobalSettings() || $this->isContainerLevelSettings()) {
            NewsReminder::flushDefautlts();
        }

        $this->initFlags();
        return true;
    }

    /**
     * @param NewsReminder $reminder
     * @param NewsReminder[] $reminders
     * @return bool
     */
    public function findReminder(NewsReminder $reminder, $reminders)
    {
        foreach ($reminders as $existingReminder) {
            if ($existingReminder->compare($reminder)) {
                return $existingReminder;
            }
        }

        return false;
    }

    public function isGlobalSettings()
    {
        return !$this->container && !$this->news;
    }

    public function initReminder($disabled = false)
    {
       if ($this->news) {
           $result = $disabled
               ? NewsReminder::initDisableNewsLevelDefaults($this->news, $this->user)
               : NewsReminder::initNewsLevel(null, null, $this->news, $this->user);
       } else if ($this->container) {
           $result = $disabled
               ? NewsReminder::initDisableContainerDefaults($this->container)
               : NewsReminder::initContainerDefault(null, null, $this->container);
       } else {
           // There is no disabled global reminder
           $result = NewsReminder::initGlobalDefault(null, null);
       }

       return $result;
    }

    public function isDefaultsLoaded()
    {
        if ($this->isDefaultsLoaded !== null) {
            return $this->isDefaultsLoaded;
        }

        if (empty($this->reminders) || $this->isGlobalSettings()) {
            return $this->isDefaultsLoaded = false;
        }

        if ($this->reminders[0]->isNewRecord) {
            return $this->isDefaultsLoaded = false;
        }

        if ($this->isContainerLevelSettings()) {
            return $this->isDefaultsLoaded = !$this->reminders[0]->isContainerLevelReminder();
        }

        if ($this->isUserLevelNewsSettings()) {
            return $this->isDefaultsLoaded = !$this->reminders[0]->isUserLevelReminder();
        }

        // This is an news level reminder, so the first loaded must be an news level reminder to, otherwise global default was loaded
        return $this->isDefaultsLoaded = $this->reminders[0]->isDefaultReminder();
    }

    public function isContainerLevelSettings()
    {
        return $this->container !== null;
    }

    public function isUserLevelNewsSettings()
    {
        return $this->news && $this->user;
    }

    public function isNewsLevelSettings()
    {
        return $this->news !== null;
    }

    public function hasDefaults()
    {
        if ($this->hasDefaults !== null) {
            return $this->hasDefaults;
        }


        if ($this->isGlobalSettings()) {
            return  $this->hasDefaults = false;
        }

        if ($this->isNewsLevelSettings()) {
            return $this->hasDefaults = !empty(NewsReminder::getNewsLevelDefaults($this->news, $this->user));
        }

        if ($this->isContainerLevelSettings()) {
            return $this->hasDefaults = !empty(NewsReminder::getDefaults());
        }

        return $this->hasDefaults = false;
    }

    /**
     * Deletes old reminders, if $preserve flag is set to true, this function will only delete old reminders,
     * which are not present in the current $reminders array.
     *
     * @return NewsReminder[]
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function reset($preserve = false)
    {
        // Load actual reminders without default fallback
        $oldReminders = $this->loadReminder(false);

        // Delete old reminders not existing in newReminders
        $preservedReminders = [];
        foreach ($oldReminders as $oldReminder) {
            if (!$preserve || !$this->findReminder($oldReminder, $this->reminders)) {
                $oldReminder->delete();
            } else {
                $preservedReminders[] = $oldReminder;
            }
        }

        return $preservedReminders;
    }

    public function getReminderTypeOptions()
    {
        $result = [static::REMINDER_TYPE_NONE => Yii::t('NewsModule.reminder', 'No reminder')];

        if ($this->hasDefaults) {
            $result[static::REMINDER_TYPE_DEFAULT] =  Yii::t('NewsModule.reminder', 'Use default reminder');
        }

        $result[static::REMINDER_TYPE_CUSTOM] =  Yii::t('NewsModule.reminder', 'Custom reminder');

        return $result;
    }
}