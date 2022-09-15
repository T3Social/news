<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\models\reminder;

use DateTime;
use humhub\components\ActiveRecord;
use humhub\modules\news\helpers\NewsUtils;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\news\models\News;
use humhub\modules\user\models\User;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class NewsReminder
 *
 * Types of reminder:
 *
 * # Global Default Reminder (global)
 *
 * - contentcontainer_id: null
 * - news_id: null
 * - unit
 * - value
 * - active: 1
 * - disabled: 0
 *
 * # Container Default Reminder (container level)
 *
 * - contentcontainer_id: space container id
 * - news_id: null
 * - unit
 * - value
 * - active: 1
 * - disabled: 0
 *
 * # Space Level Exception for an news (container wide news level)
 *
 * - contentcontainer_id: null   // Note this is required for easy seperation of space level and user level exceptions
 * - news_id Model id
 * - unit
 * - value
 * - active: 1
 * - disabled: 0
 *
 * # User Level Model Exception (user wide news level)
 *
 * - contentcontainer_id: Space container id
 * - news_id: Model id
 * - unit
 * - value
 * - active: 1
 * - disabled: 0
 *
 *
 * The following cases are used to disable a reminder in order to ignore defaults
 *
 * # Disabled container level reminder
 *
 * - contentcontainer_id: Space container Id
 * - news_id: null
 * - unit: null
 * - value: null
 * - active: 1
 * - disabled: 1
 *
 * # Disabled container news level reminder
 *
 * - contentcontainer_id: Space container Id
 * - news_id: news content id
 * - unit: null
 * - value: null
 * - active: 1
 * - disabled: 1
 *
 * # Disabled space news level reminder
 *
 * - contentcontainer_id: Space id
 * - news_id: news content id
 * - unit: null
 * - value: null
 * - active: 1
 *
 * @package humhub\modules\news\models
 *
 * @property integer $id
 * @property integer $contentcontainer_id
 * @property string $news_id
 * @property integer $unit
 * @property integer $value
 * @property integer $active
 * @property integer $disabled
 */
class NewsReminder extends ActiveRecord
{
    const UNIT_HOUR = 1;
    const UNIT_DAY = 2;
    const UNIT_WEEK = 3;

    const MAX_VALUES = [
        self::UNIT_HOUR => 24,
        self::UNIT_DAY => 31,
        self::UNIT_WEEK => 4,
    ];

    /**
     * @var NewsReminder[]
     */
    private static $globalDefaults;

    /**
     * @var array
     */
    private static $containerDefaults = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_reminder';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->active === null) {
            $this->active = 1;
        }

        if ($this->disabled === null) {
            $this->disabled = 0;
        }
    }

    public function rules()
    {
        $rules = [
            [['unit'], 'in', 'range' => [static::UNIT_HOUR, static::UNIT_DAY, static::UNIT_WEEK]],
            [['value'], 'number', 'min' => 1],
            [['value'], 'validateValue']
        ];

        if ($this->active && !$this->disabled) {
            //$rules[] = [['unit', 'value'], 'required'];
        }

        return $rules;
    }

    public function validateValue($attribute, $params)
    {
        if (!$this->unit) {
            return;
        }

        $max = static::MAX_VALUES[$this->unit];
        if (!$this->validateMaxRange()) {
            $this->addError('value',"Only values from 1 to $max are allowed");
        }
    }

    private function validateMaxRange()
    {
        if (!$this->unit) {
            return true;
        }

        return ((int) $this->value) <= static::MAX_VALUES[$this->unit];
    }

    public function ensureValidValue()
    {
        if ($this->validateMaxRange()) {
            return;
        }
        
        switch ($this->unit) {
            case static::UNIT_HOUR:
                $this->unit = static::UNIT_DAY;
                $this->value = round(((int) $this->value) / 24);
                break;
            case static::UNIT_DAY:
                $this->unit = static::UNIT_WEEK;
                $this->value = round(((int) $this->value) / 7);
                break;
            case static::UNIT_WEEK:
                $this->value = static::MAX_VALUES[static::UNIT_WEEK];
                break;
            default:
                return;
        }

        $this->ensureValidValue();
    }

    /**
     * @param $unit
     * @param $value
     * @return NewsReminder
     */
    public static function initGlobalDefault($unit, $value)
    {
        return new static(['unit' => $unit, 'value' => $value, 'active' => 1, 'disabled' => 0]);
    }

    /**
     * @param $unit
     * @param $value
     * @param ContentContainerActiveRecord $container
     * @return NewsReminder
     */
    public static function initContainerDefault($unit, $value, ContentContainerActiveRecord $container)
    {
        return new static([
            'unit' => $unit,
            'value' => $value,
            'contentcontainer_id' => $container->contentcontainer_id,
            'active' => 1,
            'disabled' => 0
        ]);
    }

    /**
     * Initializes an inactive reminder for the given container, this is used in order to ignore global defaults.
     *
     * @param ContentContainerActiveRecord $container
     * @return NewsReminder
     */
    public static function initDisableContainerDefaults(ContentContainerActiveRecord $container)
    {
        $instance = static::initContainerDefault(null, null, $container);
        $instance->disabled = 1;
        return $instance;
    }

    public static function getMinReminderHoursInPast()
    {
        $hourlyReminder = static::getMinReminder(static::UNIT_HOUR);
        $dailyReminder = static::getMinReminder(static::UNIT_DAY);
        $weeklyReminder = static::getMinReminder(static::UNIT_WEEK);

        $maxHours = 31 * 24; // 31 days
        $hour = $hourlyReminder ? $hourlyReminder->value : $maxHours;
        $day = $dailyReminder ? $dailyReminder->value * 24 : $maxHours;
        $week = $weeklyReminder ? $weeklyReminder->value * 24 * 7 : $maxHours;

        return min($hour, $day, $week);
    }

    /**
     * @param $unit
     * @return array|NewsReminder|\yii\db\ActiveRecord
     */
    private static function getMinReminder($unit)
    {
        return static::find()
            ->where(['unit' => $unit])
            ->andWhere(['active' => 1])
            ->andWhere(['disabled' => 0])
            ->andWhere('value IS NOT NULL')
            ->orderBy('value ASC')
            ->one();
    }

    /**
     * @param $unit
     * @param $value
     * @param News $model
     * @param User|null $user
     * @return NewsReminder
     */
    public static function initNewsLevel($unit, $value, News $model, $user = null)
    {
        $instance = new static(['unit' => $unit, 'value' => $value, 'active' => 1, 'news_id' => $model->id, 'disabled' => 0]);

        if ($user) {
            $instance->contentcontainer_id = $user->contentcontainer_id;
        }

        return $instance;
    }

    /**
     * Initializes an inactive news level reminder, this is used in order to ignore global and container defaults.
     *
     * @param ContentContainerActiveRecord $container
     * @return NewsReminder
     */
    public static function initDisableNewsLevelDefaults(News $model, $user = null)
    {
        $instance = static::initNewsLevel(null, null, $model, $user);
        $instance->disabled = 1;
        return $instance;
    }

    /**
     * @return bool
     */
    public function isUserLevelReminder()
    {
        return $this->isNewsLevelReminder() && $this->contentcontainer_id !== null;
    }

    /**
     * @return bool
     */
    public function isContainerWideNewsLevelReminder()
    {
        return $this->isNewsLevelReminder() && $this->contentcontainer_id === null;
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        foreach (NewsReminderSent::findByReminder($this)->all() as $reminderSent) {
            $reminderSent->delete();
        }

        parent::afterDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * @return ActiveQuery
     */
    public function getNewsContent()
    {
        return $this->hasOne(News::class, ['id' => 'news_id']);
    }

    /**
     * @return News
     * @throws \yii\db\IntegrityException
     */
    public function getNews()
    {
        return NewsUtils::getNews($this->newsContent);
    }

    /**
     * @return bool
     */
    public function isNewsLevelReminder()
    {
        return $this->news_id !== null;
    }

    /**
     * @return bool
     */
    public function isDefaultReminder()
    {
        return !$this->isNewsLevelReminder();
    }

    /**
     * @return bool
     */
    public function isContainerLevelReminder()
    {
        return !$this->isNewsLevelReminder() && $this->contentcontainer_id !== null;
    }

    /**
     * @return ContentContainer[]
     */
    public static function getContainerWithDefaultReminder()
    {
        $subQuery = static::find()
            ->where(['IS', 'news_id', new Expression('NULL')])
            ->andWhere('news_reminder.contentcontainer_id = contentcontainer.id');

        return ContentContainer::find()->where(['EXISTS',  $subQuery])->all();
    }

    /**
     * @param ContentContainerActiveRecord|null $container
     * @return static[]
     */
    public static function getDefaults(ContentContainerActiveRecord $container = null, $globalFallback = false)
    {
        $result = static::getDefaultFromCache($container, $globalFallback);

        if ($result !== null) {
            return $result;
        }

        $query = static::find();

        if ($container) {
            $query->andWhere(['contentcontainer_id' => $container->contentcontainer_id]);
        } else {
            $query->andWhere(['IS', 'contentcontainer_id', new Expression('NULL')]);
        }

        $query->andWhere(['IS', 'news_id', new Expression('NULL')]);
        $query->orderBy('unit ASC, value ASC');

        $result = $query->all();

        static::setDefaultResult($container, $result);

        if ($container && empty($result) && $globalFallback) {
            $result = static::getDefaults();
        }

        return $result;
    }

    private static function setDefaultResult(ContentContainerActiveRecord $container = null, $result = null)
    {
        if ($container) {
            static::$containerDefaults[$container->contentcontainer_id] = $result;
        } else {
            static::$globalDefaults = $result;
        }
    }

    public static function flushDefautlts()
    {
        static::$containerDefaults = [];
        static::$globalDefaults  = null;
    }

    private static function getDefaultFromCache(ContentContainerActiveRecord $container = null, $globalFallback = false)
    {
        if ($container && !isset(static::$containerDefaults[$container->contentcontainer_id])) {
            return null; // No cached results
        }

        if ($container) {
            $result = static::$containerDefaults[$container->contentcontainer_id];
            if (!empty($result) || !$globalFallback) {
                return $result;
            }
        }

        if (static::$globalDefaults !== null) {
            return static::$globalDefaults;
        }

        return null;
    }

    /**
     * @param bool $filterNotSent
     * @return ActiveQuery
     */
    public static function findNewsLevelReminder($active = true)
    {
        $query = static::find()->where(['IS NOT', 'news_id', new Expression('NULL')]);

        if ($active) {
            $query->andWhere(['active' => 1]);
        }

        $query->orderBy('news_reminder.contentcontainer_id DESC, unit ASC, value ASC');

        return $query;
    }

    /**
     * Finds reminder by model, this does not include default reminders.
     *
     * This function returns the reminder in the following order:
     *
     *  - Sort User level reminder first, ordered by the container id of the user
     *  - Sort reminder close to the news first
     *
     * @param News $model
     * @return NewsReminder[]
     */
    public static function getNewsLevelReminder(News $model, $user = true, $defaultFallback = false)
    {
        if ($model->isNewRecord) {
            return $defaultFallback ? static::getNewsLevelDefaults($model, $user) : [];
        }

        $query = static::find()
            ->where(['news_id' => $model->id])
            // We want user news level first with given contentcontainer_id, then sort by interval
            ->orderBy('news_reminder.contentcontainer_id DESC, disabled DESC, unit ASC, value ASC');

        if ($user === false) {
            $query->andWhere(['IS' ,'contentcontainer_id', new Expression('NULL')]);
        } else if ($user instanceof User) {
            $query->andWhere(['contentcontainer_id' => $user->contentcontainer_id]);
        } else {
            //$query->andWhere(['contentcontainer_id' => $model->content->contentcontainer_id]);
        }

        $result = $query->all();

        return empty($result) && $defaultFallback ? static::getNewsLevelDefaults($model, $user) : $result;
    }

    public static function getNewsLevelDefaults(News $model, $user = true)
    {
        $result = [];

        if ($user instanceof User) {
            $result = static::getNewsLevelReminder($model, false, true);
        }

        if (empty($result)) {
            $result = static::getDefaults($model->content->container, true);
        }

        return $result;
    }

    /**
     * @param News $news
     * @return bool
     */
    public function isActive(News $news)
    {
        // Non default reminder are deactivated after first sent
        if (!$this->active || $this->disabled) {
            return false;
        }

        if ($this->isDefaultReminder() && NewsReminderSent::check($this, $news)) {
            return false;
        }

        return true;
    }

    /**
     * Checks the due date of the reminder message.
     * @param News $model
     * @return bool
     * @throws \Exception
     */
    public function checkMaturity(News $model)
    {
        if (!$this->active || $this->isDisabled()) {
            return false;
        }

        $sendDate = $model->getStartDateTime()->modify($this->getModify());
        return $sendDate <= new DateTime();
    }

    public function isDisabled()
    {
        return $this->disabled;
    }

    private function getModify()
    {
        if (!$this->unit || ! $this->value) {
            return '-0 hours';
        }

        switch ($this->unit) {
            case static::UNIT_HOUR:
                $modifyUnit = 'hours';
                break;
            case static::UNIT_DAY:
                $modifyUnit = 'days';
                break;
            case static::UNIT_WEEK:
                $modifyUnit = 'weeks';
                break;
            default:
                $modifyUnit = 'hours';
        }

        // add tolerance for cron delay....
        return '-'.$this->value.' '.$modifyUnit;
    }

    /**
     * @param News $news
     */
    public function acknowledge(News $news)
    {
        if ($this->isNewsLevelReminder()) {
            $this->updateAttributes(['active' => 0]);
        }

        NewsReminderSent::create($this, $news);
    }

    public function compare(NewsReminder $reminder)
    {
        return $this->unit == $reminder->unit
            && $this->value == $reminder->value
            && $this->contentcontainer_id == $reminder->contentcontainer_id
            && $this->news_id == $reminder->news_id;
    }
}