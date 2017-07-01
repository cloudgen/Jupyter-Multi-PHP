<?php

/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Cloudgen\JupyterMultiPHP\Actions;

final class ShutdownAction implements Action{
  use \Cloudgen\JupyterMultiPHP\Concern\Brokable;
  use \Cloudgen\JupyterMultiPHP\Concern\IOPubListenable;
  use \Cloudgen\JupyterMultiPHP\Concern\ShellListenable;

  public function __construct($broker, $iopubSocket, $shellSocket){
    $this->broker = $broker;
    $this->iopubSocket = $iopubSocket;
    $this->shellSocket = $shellSocket;
  }
  public function call( $header,  $content, $zmqId = null){
    $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);
    $replyContent = ['restart' => $content['restart']];
    $this->broker->send($this->shellSocket, 'shutdown_reply', $replyContent, $header, [], $zmqId);
    $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
  }
}
