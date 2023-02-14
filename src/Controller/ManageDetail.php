<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class ManageDetail
{
    public function main(int $id)
    {
        $model = \app\model\Manage::find($id);

        if (empty($model)) {
            return notFound();
        }

        return ok($this->format($model));
    }

    private function format($model)
    {
        $model::macro('roles', function () {
            return $this->hasManyThrough(\app\model\ManageRole::class, \app\model\ManageRoleDetail::class, 'manageid', 'id', 'id', 'roleid')
                ->visible(['id', 'rolename']);
        });

        \app\model\ManageRole::macro('manageRolePrivilege', function () {
            return $this->hasManyThrough(\app\model\ManagePrivilege::class, \app\model\ManageRolePrivilege::class, 'roleid', 'id', 'id', 'id');
        });

        $roles = $model->roles;

        $privilegeids = \app\model\ManageRolePrivilege::whereIn('roleid', $roles->column('id'))
            ->column('id');

        $privileges = \app\model\ManagePrivilege::whereIn('id', $privilegeids)
            ->column('id');

        $privilegeDetails = \app\model\ManagePrivilegeDetail::whereIn('privilegeid', $privileges)
            ->column('key');

        return [
            'id'              => $model->id,
            'username'        => $model->username,
            'nickname'        => $model->nickname,
            'login_number'    => $model->login_number,
            'login_time'      => toDateTime($model->login_time),
            'login_last_time' => toDateTime($model->login_last_time),
            'create_time'     => toDateTime($model->create_time),
            'roles'           => $roles,
            'privileges'      => $privilegeDetails,
        ];
    }
}
