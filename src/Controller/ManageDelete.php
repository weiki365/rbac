<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class ManageDelete
{
    public function main(int $id)
    {
        $model = \app\model\Manage::find($id);

        if (empty($model)) {
            return notFound();
        }

        $model::macro('roleDetails', function () {
            return $this->hasMany(\app\model\ManageRoleDetail::class, 'manageid');
        });

        $relationRoleDetails = $model->roleDetails()->column('id');

        try {

            \think\facade\Db::startTrans();

            $model->delete();
            \app\model\ManageRoleDetail::whereIn('id', $relationRoleDetails)->delete();

            \think\facade\Db::commit();

        } catch (\Throwable $e) {

            \think\facade\Db::rollback();
            throw $e;
        }

        return ok('删除成功');
    }
}
