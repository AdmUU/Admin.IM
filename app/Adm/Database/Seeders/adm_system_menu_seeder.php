<?php

declare(strict_types=1);
/**
 * This file is part of Admin.IM
 *
 * @link     https://www.admin.im
 * @github   https://github.com/AdmUU/Admin.IM
 * @contact  dev@admin.im
 * @license  https://github.com/AdmUU/Admin.IM/blob/main/LICENSE
 */
use App\System\Model\SystemMenu;
use Hyperf\Database\Seeders\Seeder;
use Hyperf\DbConnection\Db;

class AdmSystemMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if (env('DB_DRIVER') === 'odbc-sql-server') {
            Db::unprepared('SET IDENTITY_INSERT [' . SystemMenu::getModel()->getTable() . '] ON;');
        }
        $data = $this->data();
        foreach ($data as $i => $value) {
            SystemMenu::create($value);
        }
        Db::statement('ALTER TABLE ' . SystemMenu::getModel()->getTable() . ' AUTO_INCREMENT = 100000');
        if (env('DB_DRIVER') === 'odbc-sql-server') {
            Db::unprepared('SET IDENTITY_INSERT [' . SystemMenu::getModel()->getTable() . '] OFF;');
        }
    }

    public function data(): array
    {
        return [
            ['id' => '10000', 'parent_id' => '0', 'level' => '0', 'name' => '节点管理', 'code' => 'adm:node', 'icon' => 'icon-relation', 'route' => 'adm/node', 'component' => 'adm/node/index', 'redirect' => null, 'is_hidden' => '2', 'type' => 'M', 'status' => '1', 'sort' => '990', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10001', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理列表', 'code' => 'adm:node:index', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10002', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理保存', 'code' => 'adm:node:save', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10003', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理更新', 'code' => 'adm:node:update', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10004', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理删除', 'code' => 'adm:node:delete', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10005', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理回收站', 'code' => 'adm:node:recycle', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10006', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理恢复', 'code' => 'adm:node:recovery', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10007', 'parent_id' => '10000', 'level' => '0,10000', 'name' => '节点管理真实删除', 'code' => 'adm:node:realDelete', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10100', 'parent_id' => '0', 'level' => '0', 'name' => '节点分组', 'code' => 'adm:nodeGroup', 'icon' => 'icon-unordered-list', 'route' => 'adm/nodeGroup', 'component' => 'adm/nodeGroup/index', 'redirect' => null, 'is_hidden' => '2', 'type' => 'M', 'status' => '1', 'sort' => '980', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10101', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组列表', 'code' => 'adm:nodeGroup:index', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10102', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组保存', 'code' => 'adm:nodeGroup:save', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10103', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组更新', 'code' => 'adm:nodeGroup:update', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10104', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组读取', 'code' => 'adm:nodeGroup:read', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10105', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组删除', 'code' => 'adm:nodeGroup:delete', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10106', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组回收站', 'code' => 'adm:nodeGroup:recycle', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10107', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组恢复', 'code' => 'adm:nodeGroup:recovery', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
            ['id' => '10108', 'parent_id' => '10100', 'level' => '0,10100', 'name' => '节点分组真实删除', 'code' => 'adm:nodeGroup:realDelete', 'icon' => null, 'route' => null, 'component' => null, 'redirect' => null, 'is_hidden' => '2', 'type' => 'B', 'status' => '1', 'sort' => '0', 'created_by' => '1', 'updated_by' => null, 'deleted_at' => null, 'remark' => null],
        ];
    }
}
