<?php

/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Cloudgen\JupyterMultiPHP\Concern;
use Monolog\Logger;
trait Loggable{
  /** @var Logger */
  private $logger;
  public function __construct(Logger $logger){
    $this->logger = $logger;
  }
  public function message_received($msg){
    $this->logger->debug('Received message', ['processId' => getmypid(), 'msg' => $msg]);
  }
  public function error_received($e){
    $this->logger->debug('Received message', ['processId' => getmypid(), 'error' => $e]);
  }
}
