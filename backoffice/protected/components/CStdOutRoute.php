<?php

class CStdOutRoute extends CLogRoute
{			
    public function processLogs($logs)
    {
        $STDOUT = fopen("php://stdout", "w");
        foreach($logs as $log)
            fwrite($STDOUT, $this->formatLogMessage($log[0],$log[1],$log[2],$log[3])); //write the message [1] = level, [2]=category
        fclose($STDOUT);
    }
}

?>