<?php
error_reporting(E_ALL);
class Calculator {
    public $result = 0;
    public $queue = Array();
    public parseString($text) {
       // parse input string
       $cmds = explode(" ", $text);
       foreach($cmds as $cmd) {
          $cmd = trim($cmd);
          if(!$cmd) continue; // blank or space, ignoring
          $this->queue[] = $cmd;
       }

       // lets process commands
       $command = false;
       foreach($this->queue as $index => $cmd) { 
           if(is_number($cmd)) {
               // if it's number fire previous command if exists
               if(!$command || !method_exists($this, $command)) {
                   throw new Exception("Unknown command $command");
               }
               $this->$command($index, $cmd);
           }else{
               $command = $cmd;
           }
       }
    }
    public function apply($index, $number) {
       // manipulate $result, $queue, $number
    }
    public function add($index, $number) {
       // manipulate $result, $queue, $number
    }
    public function substract($index, $number) {
       // manipulate $result, $queue, $number
    }
}

$calculator = new Calculator();
$calculator->parseString('add 5');
?>