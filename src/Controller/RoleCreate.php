<?php

namespace Weiki\Rbac\Controller;

class RoleCreate
{
    function main(string$name, string|null $manageids = null, string|null $privilegeids = null)
    {
        $model = new \app\model\ManageRole();
        $time  = time();

        $model->rolename    = $name;
        $model->create_time = time();
        $roleid = null;

        if ($manageids) {
            $manageRoleDetails = [];
            foreach (explode(',', $manageids) as $manageid) {
                $manageRoleDetails[] = [
                    'roleid'      => & $roleid,
                    'manageid'    => $manageid,
                    'create_time' => $time,
                    'delete_time' => $time,
                ];
            }
        }

        if ($privilegeids) {
            $manageRolePrivileges = [];
            foreach (explode(',', $privilegeids) as $privilegeid) {
                $manageRolePrivileges[] = [
                    'roleid'      => & $roleid,
                    'privilegeid' => $privilegeid,
                    'create_time' => $time,
                    'delete_time' => $time,
                ];
            }
        }

        try {

            \think\facade\Db::startTrans();

            $model->save();
            $roleid = $model->id;

            if (!empty($manageRoleDetails)) {
                \app\model\ManageRoleDetail::insertAll($manageRoleDetails);
            }

            if (!empty($manageRolePrivileges)) {
                \app\model\ManageRolePrivilege::insertAll($manageRolePrivileges);
            }

            \think\facade\Db::commit();

        } catch (\Exception $e) {

            \think\facade\Db::rollback();
            throw $e;
        }

        return ok();
    }
}
