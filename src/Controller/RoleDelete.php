<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class RoleDelete
{
    public function main($id)
    {
        \app\model\ManageRole::macro('roleDetails', function () {
            return $this->hasMany(\app\model\ManageRoleDetail::class, 'roleid');
        });
        \app\model\ManageRole::macro('rolePrivilege', function () {
            return $this->hasMany(\app\model\ManageRolePrivilege::class, 'roleid');
        });

        $model = \app\model\ManageRole::find($id);

        if (empty($model)) {
            return notFound();
        }

        $relationRoleDetails   = $model->roleDetails()->column('id');
        $relationRolePrivilege = $model->rolePrivilege()->column('id');

        try {

            \think\facade\Db::startTrans();

            $model->delete();
            \app\model\ManageRoleDetail::whereIn('id', $relationRoleDetails)->delete();
            \app\model\ManageRolePrivilege::whereIn('id', $relationRolePrivilege)->delete();

            \think\facade\Db::commit();

        } catch (\Throwable $e) {

            \think\facade\Db::rollback();
            throw $e;
        }

        $model->delete();

        return ok('删除成功');
    }
}
