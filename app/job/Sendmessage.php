<?php

namespace app\job;

use think\facade\Log;
use think\queue\Job;

class Sendmessage
{
    public function fire(Job $job, $data)
    {
        $job->delete();
        try {
            $res = event("SendMessageTemplate", $data, true);
            Log::write("SendMessageTemplate" . json_encode($res));

        } catch (\Exception $e) {
            Log::write($e->getMessage() . $e->getFile() . $e->getLine());
        }
    }

}
