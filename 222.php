<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;

use App\Repositories\EloquentRepository;
use App\Models\SalaryRoleModel;
use App\Models\SalaryActionModel;
use App\Models\SalaryRuleModel;
use App\Models\SalaryRoleActionRuleModel;

class ManagetRepository extends EloquentRepository
{
    protected $salaryRoleModel;
    protected $salaryActionModel;
    protected $salaryRuleModel;
    protected $salaryRoleActionRuleModel;

    public function __construct(
        SalaryRoleModel $salaryRoleModel,
        SalaryActionModel $salaryActionModel,
        SalaryRuleModel $salaryRuleModel,
        SalaryRoleActionRuleModel $salaryRoleActionRuleModel
    )
    {
        $this->salaryRoleModel = $salaryRoleModel;
        $this->salaryActionModel = $salaryActionModel;
        $this->salaryRuleModel = $salaryRuleModel;
        $this->salaryRoleActionRuleModel = $salaryRoleActionRuleModel;
    }

    /** 创建角色
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function addRole($param)
    {
        if (empty($param['name'])) {
            throw new \Exception('增加角色参数错误');
        }
        $data = $this->salaryRoleModel->onWriteConnection()->select('name')->where('name', $param['name'])->get()->toArray();
        if ($data) {
            throw new \Exception('角色名称已存在');
        }
        try {
            $this->salaryRoleModel->insert(['name' => $param['name']]);
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('增加角色插入数据库失败');
        }
        return [];
    }

    /** 更新角色
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function updateRole($param)
    {
        if (empty($param['name']) || empty($param['id']) || empty($param['old_name'])) {
            throw new \Exception('更新角色参数错误');
        }
        $data = $this->salaryRoleModel->onWriteConnection()->select('name')->where('name', $param['name'])->get()->toArray();
        if ($data) {
            throw new \Exception('角色名称已存在');
        }
        try {
            $this->salaryRoleModel
                ->where(['id' => $param['id']])
                ->where(['name' => $param['old_name']])
                ->update(['name' => $param['name']]);
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('更新角色操作数据库失败');
        }
        return [];
    }

    /** 查询角色
     * @return array
     * @throws \Exception
     */
    public function selectRole($param = [])
    {
        $select = $this->salaryRoleModel;
        !empty($param['name']) && $select = $select->where('name', $param['name']);
        !empty($param['id']) && $select = $select->where('id', $param['id']);
        try {
            $data = $select->select('id', 'name')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('查询角色操作数据库失败');
        }
        return $data;
    }

    /** 创建行为
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function addAction($param)
    {
        if (empty($param['name']) || empty($param['extra_param']) || !is_array($param['extra_param'])) {
            throw new \Exception('增加行为参数错误');
        }
        $data = $this->salaryActionModel->onWriteConnection()->select('name')->where('name', $param['name'])->get()->toArray();
        if ($data) {
            throw new \Exception('行为名称已存在');
        }
        try {
            $insert = [
                'name' => $param['name'],
                'extra_param' => json_encode($param['extra_param']),
            ];
            $this->salaryActionModel->insert($insert);
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('增加行为插入数据库失败');
        }
        return [];
    }

    /** 更新行为
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function updateAction($param)
    {
        if (empty($param['name']) || empty($param['id']) || empty($param['old_name'])) {
            throw new \Exception('更新行为参数错误');
        }
        $data = $this->salaryActionModel->onWriteConnection()->select('name')->where('name', $param['name'])->get()->toArray();
        if ($data) {
            throw new \Exception('行为名称已存在');
        }
        $update = [
            'name' => $param['name'],
        ];
        !empty($param['extra_param']) && is_array($param['extra_param']) && $update['extra_param'] = json_encode($param['extra_param']);
        try {
                $this->salaryActionModel
                ->where(['id' => $param['id']])
                ->where(['name' => $param['old_name']])
                ->update($update);
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('更新行为操作数据库失败');
        }
        return [];
    }

    /** 查询行为
     * @return array
     * @throws \Exception
     */
    public function selectAction($param = [])
    {
        $select = $this->salaryActionModel;
        !empty($param['name']) && $select = $select->where('name', $param['name']);
        !empty($param['id']) && $select = $select->where('id', $param['id']);
        try {
            $data = $select
                ->select('id', 'name','extra_param')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('查询行为操作数据库失败');
        }
        return $data;
    }

    public function setModule()
    {
        if (empty($param['role_id']) || empty($param['action_id']) || empty($param['rule_id']) || empty($param['extra_param']) || !is_array($param['extra_param'])) {
            Log::info(__METHOD__ . ':参数错误' . json_encode($param));
            throw new \Exception('参数错误');
        }
        $data = $this->salaryRoleActionRuleModel->onWriteConnection()
            ->select('id')
            ->where('role_id', $param['role_id'])
            ->where('action_id', $param['role_id'])
            ->where('role_id', $param['role_id'])
            ->where('role_id', $param['role_id'])
            ->get()
            ->toArray();
        if ($data) {
            throw new \Exception('规则名称已存在');
        }
        try {
            $this->salaryRuleModel
                ->where(['id' => $param['id']])
                ->where(['name' => $param['old_name']])
                ->update(['name' => $param['name']]);
        } catch (\Exception $e) {
            Log::info(__METHOD__ . ' :数据库报错: ' . $e->getMessage());
            throw new \Exception('更新规则操作数据库失败');
        }
        return [];

    }

}
