<?php

namespace App\Repositories\TeacherSuspend;

use App\Models\Teacher\TeacherSuspend;
use App\Models\Teacher\TeacherSuspendLog;
use DB;

class TeacherSuspendRepository implements TeacherSuspendRepositoryInterFace
{

    protected $teacherSuspend;
    protected $teacherSuspendLog;

    public function __construct(TeacherSuspend $teacherSuspend,TeacherSuspendLog $teacherSuspendLog)
    {
        $this->teacherSuspend = $teacherSuspend;
        $this->teacherSuspendLog = $teacherSuspendLog;
    }
    /** 根据tid获取单条记录
     * @param $tid
     * @return array
     */
    public function findOneByTid($tid)
    {
        $res = $this->teacherSuspend::onWriteConnection()->where('tid',$tid)->first();
        if($res){
            return $res->toArray();
        } else {
            return [];
        }
    }
    /** 插入封号数据
     * @param $param
     * @return bool
     */
    private function insert($param)
    {
        DB::beginTransaction();
        $this->teacherSuspend->insert([
            'tid'=>$param['tid'],
            'status'=>$param['status'],
            'start_time'=>$param['start_time'],
            'end_time'=>$param['end_time'],
        ]);
        $log = [
            'tid'       => $param['tid'],
            'tid_name'  => $param['tid_name'],
            'aid'       => $param['aid'],
            'aid_name'  => $param['aid_name'],
            'status'    => $param['status'],
            'job_type'  => $param['job_type'],
            'reason'    => $param['reason'],
            'start_time'=> $param['start_time'],
            'end_time'  => $param['end_time'],
        ];
        if($this->teacherSuspendLog->insert($log)){
            DB::commit();
            return true;
        } else {
            DB::rollback();
            return false;
        }
    }
    /** 更新封号数据
     * @param $param
     * @return bool
     */
    private function update($param)
    {
        DB::beginTransaction();
        $this->teacherSuspend->where('tid',$param['tid'])->update([
            'status'=>$param['status'],
            'start_time'=>$param['start_time'],
            'end_time'=>$param['end_time'],
        ]);
        $log = [
            'tid'       => $param['tid'],
            'tid_name'  => $param['tid_name'],
            'aid'       => $param['aid'],
            'aid_name'  => $param['aid_name'],
            'status'    => $param['status'],
            'job_type'  => $param['job_type'],
            'reason'    => $param['reason'],
            'start_time'=> $param['start_time'],
            'end_time'  => $param['end_time'],
        ];
        if($this->teacherSuspendLog->insert($log)){
            DB::commit();
            return true;
        } else {
            DB::rollback();
            return false;
        }
    }

    /** 设置封号数据
     * @param $param
     * @return bool
     */
    public function setTeacherSuspend($param)
    {
        $result = $this->teacherSuspend::onWriteConnection()->where('tid',$param['tid'])->first();
        if($result){
            return $this->update($param);
        } else {
            return $this->insert($param);
        }
    }

    /** 批量查询tid封号
     * @param $param
     * @return mixed
     */
    public function getListByTids($param)
    {
        return $this->teacherSuspend->whereIn('tid',$param['tid'])->get()->toArray();

    }

    /** 通过时间段获取封号记录
     * @param $param
     * @return array
     */
    public function getLogsByTime($param)
    {
        $res = $this->teacherSuspendLog
            ->where('create_time','>=',$param['start_time'])
            ->where('create_time','<',$param['end_time'])
            ->orderBy('create_time','asc')
            ->get()
            ->toArray();
        if(!empty($res)) {
            return $res;
        }
        return [];
    }

    /** 根据单个tid获取封号日志
     * @param $param
     * @return array
     */
    public function getLogsByTid($param)
    {
        $res = $this->teacherSuspendLog
            ->where('tid',$param['tid'])
            ->orderBy('create_time','asc')
            ->get()
            ->toArray();
        if(!empty($res)) {
            return $res;
        }
        return [];
    }

    /** 根据时间判断封号状态
     * @param $tid
     * @param $start_time
     * @return array
     */
    public function checkTeacherSuspendSatus($tid,$start_time){
        $res = $this->teacherSuspend
            ->where('tid',$tid)
            ->where('status',1)
            ->where('end_time','>',$start_time)
            ->first();
        if($res) {
            return $res->toArray();
        }
        return [];
    }

    /** 根据tid获取当前是否封号，如果封号则返回时间和原因
     * @param $tid
     * @return array
     */
    public function getSuspendReasonByTid($tid){
        $res = $this->checkTeacherSuspendSatus($tid,time());
        if ($res) {
            $data = $this->teacherSuspendLog
                ->where('tid',$res['tid'])
                ->where('start_time',$res['start_time'])
                ->where('end_time',$res['end_time'])
                ->where('status',$res['status'])
                ->orderBy('create_time','desc')
                ->first();
            if($data) {
                return $data->toArray();
            }

        }
        return [];
    }

}
