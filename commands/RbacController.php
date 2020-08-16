<?php

declare(strict_types=1);

namespace app\commands;

use app\models\User;
use app\rbac\models\Role;
use app\rbac\rules\AuthorRule;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\Console;

class RbacController extends Controller
{
    /**
     * Initializes the RBAC authorization data.
     * @throws Exception
     */
    public function actionInit(): void
    {
        $auth = Yii::$app->authManager;

        //---------- RULES ----------//

        // add the rule
        $rule = new AuthorRule();
        $auth->add($rule);

        //---------- PERMISSIONS ----------//


        // add "adminData" permission
        $basicPerm = $auth->createPermission('basic');
        $basicPerm->description = 'Allows User to do basic things!';
        $auth->add($basicPerm);


        //---------- ROLES ----------//

        // add "member" role
        $member = $auth->createRole(Role::ROLE_MEMBER);
        $member->description = 'Authenticated user, equal to "@"';
        $auth->add($member);


        // add "admin" role
        $admin = $auth->createRole(Role::ROLE_ADMIN);
        $admin->description = 'Administrator of this application';
        $auth->add($admin);
        $auth->addChild($admin, $basicPerm);

        if ($auth) {
            $this->stdout("\nRbac authorization data are installed successfully.\n", Console::FG_GREEN);
        }

        $user = new User();
        $user->username = 'admin';
        $user->email = "admin@admin.com";
        $user->setPassword('admin');
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = $user->updated_at = time();
        $user->save();

        $role = $auth->getRole(Role::ROLE_ADMIN);
        $auth->assign($role, $user->id);
    }
}