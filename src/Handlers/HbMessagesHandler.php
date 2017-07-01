<?php

/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Cloudgen\JupyterMultiPHP\Handlers;

final class HbMessagesHandler{
  use \Cloudgen\JupyterMultiPHP\Concern\Loggable;
  use \Cloudgen\JupyterMultiPHP\Concern\Heartbeatable;

  public function __construct( $hbSocket, $logger){
    $this->logger = $logger;
    $this->hbSocket = $hbSocket;
  }

  public function __invoke($msg){
    //$this->logger->debug('Received message', ['processId' => getmypid(), 'msg' => $msg]);
    if (['ping'] === $msg){
      $this->hbSocket->send($msg);
    } else{
      $this->logger->error('Unknown message', ['processId' => getmypid(), 'msg' => $msg]);
    }
  }
}
