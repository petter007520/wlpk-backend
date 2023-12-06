<?php

namespace app\job;

use app\model\system\Cron;
use think\facade\Log;
use think\queue\Job;

/**
 * 事件通过队列异步调用
 * Class Eventasync
 * @package app\job
 */
class Cronexecute
{
    public function fire(Job $job, $data)
    {
        $job->delete();
        try {

            $res = event($data[ 'event' ], [ 'relate_id' => $data[ 'relate_id' ] ]);
            $data_log = [
                'name' => $data[ 'name' ],
                'event' => $data[ 'event' ],
                'relate_id' => $data[ 'relate_id' ],
                'message' => json_encode($res)
            ];

            Log::write("计划任务:{$data[ 'event' ]} relate_id: {$data[ 'relate_id' ]}执行结果：" . json_encode($res, JSON_UNESCAPED_UNICODE));
            $cron_model = new Cron();
            //定义最新的执行时间或错误
            $cron_model->addCronLog($data_log);


        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $job->delete();
        }
    }

}
