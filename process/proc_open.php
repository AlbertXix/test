<?php

class Process {
    public $resource;
    public $pipes;
    public $script;
    public $max_execution_time;
    public $start_time;
   
    function __construct(&$executable, &$root, $script, $max_execution_time) {
        $this->script = $script;
        $this->max_execution_time = $max_execution_time;
        $descriptorspec    = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        $this->resource    = proc_open($executable." ".$root.$this->script, $descriptorspec, $this->pipes, null, $_ENV);
        $this->start_time = mktime();
    }
   
    // is still running?
    function isRunning() {
        $status = proc_get_status($this->resource);
        return $status["running"];
    }

    // long execution time, proccess is going to be killer
    function isOverExecuted() {
        if ($this->start_time+$this->max_execution_time<mktime()) return true;
        else return false;
    }

}

class Processmanager {
    public $executable = "E:\\program files\\php";
    public $root = "C:\\www\\";
    public $scripts = array();
    public $processesRunning = 0;
    public $processes = 3;
    public $running = array();
    public $sleep_time = 2;
   
    function addScript($script, $max_execution_time = 300) {
        $this->scripts[] = array("script_name" => $script,
                            "max_execution_time" => $max_execution_time);
    }
   
    function exec() {
        $i = 0;
        for(;;) {
        // Fill up the slots
        while (($this->processesRunning<$this->processes) and ($i<count($this->scripts))) {
        echo "<span style='color: orange;'>Adding script: ".$this->scripts[$i]["script_name"]."</span><br />";
        ob_flush();
        flush();
        $this->running[] =& new Process($this->executable, $this->root, $this->scripts[$i]["script_name"], $this->scripts[$i]["max_execution_time"]);
        $this->processesRunning++;
        $i++;
        }
       
        // Check if done
        if (($this->processesRunning==0) and ($i>=count($this->scripts))) {
            break;
        }
        // sleep, this duration depends on your script execution time, the longer execution time, the longer sleep time
      sleep($this->sleep_time);
     
      // check what is done
        foreach ($this->running as $key => $val) {
                if (!$val->isRunning() or $val->isOverExecuted()) {
            if (!$val->isRunning()) echo "<span style='color: green;'>Done: ".$val->script."</span><br />";
            else echo "<span style='color: red;'>Killed: ".$val->script."</span><br />";
                    proc_close($val->resource);
                    unset($this->running[$key]);
                    $this->processesRunning--;
            ob_flush();
            flush();
                }
            }
        }
    }
}
?>