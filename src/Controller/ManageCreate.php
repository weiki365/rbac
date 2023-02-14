<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class ManageCreate
{
    public function main(
        $username,
        $nickname,
        $password,
        string|null $roleids = null
    ) {
        $time  = time();
        $model = new \app\model\Manage();
        $manageid = 0;
        $manageRoleDetails = [];

        $model->username        = $username;
        $model->nickname        = $nickname;
        $model->password        = password_hash($password.$username, PASSWORD_DEFAULT);
        $model->login_number    = 0;
        $model->login_time      = 0;
        $model->login_last_time = 0;
        $model->status          = 1;
        $model->create_time     = $time;

        if (!empty($roleids)) {
            foreach (array_filter(explode(',', $roleids)) as $roleid) {
                $manageRoleDetails[] = [
                    'roleid'      => $roleid,
                    'manageid'    => & $manageid,
                    'create_time' => $time,
                    'delete_time' => 0,
                ];
            }
        }

        try {

            \think\facade\Db::startTrans();

            $model->save();
            $manageid = $model->id;

            if (!empty($manageRoleDetails)) {
                \app\model\ManageRoleDetail::insertAll($manageRoleDetails);
            }

            \think\facade\Db::commit();

        } catch (\Exception $e) {

            \think\facade\Db::rollback();
            throw $e;
        }

        return ok();
    }
}
