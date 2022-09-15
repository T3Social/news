<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\news\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use Yii;

/**
 * ManagePages Permissions
 */
class ManageNews extends BasePermission
{

    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_MODERATOR,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_GUEST,
        Space::USERGROUP_USER,
    ];

    /**
     * @inheritdoc
     */
    protected $moduleId = 'news';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('NewsModule.base', 'Can manage news');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('NewsModule.base', 'Allows the user to manage news.');
    }

}
