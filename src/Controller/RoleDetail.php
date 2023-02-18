<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class RoleDetail
{
    public function main(int $id)
    {
        $model = \app\model\ManageRole::find($id);

        \app\model\ManageRole::macro('manageDetail', function() {
            return $this->hasMany(\app\model\ManageRoleDetail::class, 'roleid');
        });
        \app\model\ManageRole::macro('rolePrivilege', function() {
            return $this->hasMany(\app\model\ManageRolePrivilege::class, 'roleid');
        });

        if (empty($model)) {
            return notFound();
        }

        return ok($this->format($model));
    }

    private function format($model)
    {
        return [
            'id'          => $model->id,
            'rolename'    => $model->rolename,
            'create_time' => $model->create_time,
            'users'       => \app\model\Manage::comment(__METHOD__)
                ->field('id, nickname')
                ->select(
                    $model->manageDetail()->column('manageid')
                ),
            'privileges' => \app\model\ManagePrivilege::comment(__METHOD__)
                ->field('id, name')
                ->select(
                    $model->rolePrivilege()->column('privilegeid')
                ),
        ];
    }
}
