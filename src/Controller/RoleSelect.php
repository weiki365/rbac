<?php

declare (strict_types = 1);

namespace Weiki\Rbac\Controller;

class RoleSelect
{
    public function main(
        int $page = 1,
        int $size = 10
    ) {
        $query = \app\model\ManageRole::comment(__METHOD__);

        $lazy = fn() => $query->order('id DESC')
            ->page($page, $size)
            ->select()
            ->map($this->getFormatter());

        return okListsOfLazy($query->count(), $lazy);
    }

    private function getFormatter()
    {
        return function ($model) {
            return [
                'id'          => $model->id,
                'rolename'    => $model->rolename,
                'create_time' => $model->create_time,
            ];
        };
    }
}
