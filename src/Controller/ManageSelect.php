<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class ManageSelect
{
    public function main(
        int $page = 1,
        int $size = 10
    ) {
        \app\model\Manage::macro('roles', function() {
            return $this->hasMany(\app\model\ManageRoleDetail::class, 'manageid')
                ->field('manageid, roleid')
                ->visible(['roleid', 'rolename']);
        });

        \app\model\ManageRoleDetail::macro('role', function() {
            return $this->belongsTo(\app\model\ManageRole::class, 'roleid')
                ->field('id, rolename')
                ->bind(['rolename']);
        });

        $query = \app\model\Manage::comment(__METHOD__);

        $lazy = fn() => $query->with('roles.role')
            ->order('id DESC')
            ->page($page, $size)
            ->select()
            ->map($this->getFormatter());

        return okListsOfLazy($query->count(), $lazy);
    }

    private function getFormatter()
    {
        return function ($model) {
            return [
                'id'              => $model->id,
                'username'        => $model->username,
                'nickname'        => $model->nickname,
                'login_number'    => $model->login_number,
                'login_time'      => $model->login_time,
                'login_last_time' => $model->login_last_time,
                'create_time'     => $model->create_time,
                'status'          => $model->status,
                'roles'           => $model->roles,
            ];
        };
    }
}
