<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class RoleUpdate
{
    public function main(
        $id,
        string $rolename,
        string|null $privilegeids = null,
        string|null $manageids = null
    ) {
        $time  = time();
        $model = \app\model\ManageRole::find($id);

        if (empty($model)) {
            return notFound();
        }


        $model::macro('manages', function () {
            return $this->hasMany(\app\model\ManageRoleDetail::class, 'roleid')
                ->field(['id', 'manageid', 'delete_time']);
        });

        \app\model\ManageRole::macro('privileges', function () {
            return $this->hasMany(\app\model\ManageRolePrivilege::class, 'roleid');
        });

        $model->rolename = $rolename;

        $manageids    = explode(',', $manageids ?? '');
        $privilegeids = explode(',', $privilegeids ?? '');

        $manages    = $model->manages;
        $privileges = $model->privileges;

        foreach (array_filter(array_diff($manageids, $manages->column('manageid'))) as $manageid) {
            $addManages[] = [
                'roleid'      => $id,
                'manageid'    => $manageid,
                'create_time' => $time,
                'delete_time' => 0,
            ];
        }

        foreach (array_filter(array_diff($privilegeids, $privileges->column('privilegeid'))) as $privilegeid) {
            $addPrivileges[] = [
                'roleid'      => $id,
                'privilegeid' => $privilegeid,
                'create_time' => $time,
                'delete_time' => 0,
            ];
        }

        try {

            \think\facade\Db::startTrans();

            foreach ($manages as $manage) {
                if (in_array($manage->manageid, $manageids) && $manage->delete_time > 0) {
                    $manage->delete_time = 0;
                    $manage->save();
                } elseif (!in_array($manage->manageid, $manageids) && $manage->delete_time == 0) {
                    $manage->delete_time = $time;
                    $manage->save();
                }
            }

            foreach ($privileges as $privilege) {
                if (in_array($privilege->privilegeid, $privilegeids) && $privilege->delete_time > 0) {
                    $privilege->delete_time = 0;
                    $privilege->save();
                } elseif (!in_array($privilege->privilegeid, $privilegeids) && $privilege->delete_time == 0) {
                    $privilege->delete_time = $time;
                    $privilege->save();
                }
            }

            $model->save();

            if (!empty($addManages)) {
                \app\model\ManageRoleDetail::insertAll($addManages);
            }

            if (!empty($addPrivileges)) {
                \app\model\ManageRolePrivilege::insertAll($addPrivileges);
            }

            \think\facade\Db::commit();

        } catch (\Throwable $e) {

            \think\facade\Db::rollback();
            throw $e;
        }

        return ok($model);
    }
}
