<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class ManageUpdate
{
    public function main(
        int $id,
        string $nickname,
        int $status,
        string|null $roleids = null
    ) {
        $time    = time();
        $roleids = array_filter(explode(',', $roleids));

        \app\model\Manage::macro('rolesDetail', function() {
            return $this->hasMany(\app\model\ManageRoleDetail::class, 'manageid')
                ->field('manageid, roleid');
        });

        $model = \app\model\Manage::find($id);

        if (empty($model)) {
            return notFound();
        }

        $rolesDetail = $model->rolesDetail()
            ->comment(__METHOD__)
            ->field('roleid,id')
            ->select();


        $inserts = array_diff($roleids, $rolesDetail->column('roleid'));
        $deletes = array_diff(array_merge($rolesDetail->column('roleid'), $inserts), $roleids);
        $updates = array_intersect($rolesDetail->column('roleid'), $roleids);

        foreach ($inserts as & $data) {
            $data = [
                'manageid'    => $id,
                'roleid'      => $data,
                'create_time' => $time,
                'delete_time' => 0,
            ];
        }

        $model->id       = $id;
        $model->nickname = $nickname;
        $model->status   = $status;

        try {

            \think\facade\Db::startTrans();

            $model->save();

            if (!empty($inserts)) {
                \app\model\ManageRoleDetail::comment(__METHOD__)
                    ->insertAll($inserts);
            }

            if (!empty($updates)) {
                \app\model\ManageRoleDetail::comment(__METHOD__)
                    ->whereIn('id', $rolesDetail->column('id'))
                    ->whereIn('roleid', $updates)
                    ->update(['delete_time' => 0]);
            }

            if (!empty($deletes)) {
                \app\model\ManageRoleDetail::comment(__METHOD__)
                    ->whereDeleteTime(0)
                    ->whereIn('id', $rolesDetail->column('id'))
                    ->whereIn('roleid', $deletes)
                    ->update(['delete_time' => $time]);
            }

            \think\facade\Db::rollback();
            \think\facade\Db::commit();

        } catch (\Throwable $e) {

            \think\facade\Db::rollback();
            throw $e;
        }

        return ok();
    }
}
