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

final class HistoryAction implements Action{
  use \Cloudgen\JupyterMultiPHP\Concern\Brokable;
  use \Cloudgen\JupyterMultiPHP\Concern\ShellListenable;
  public function __construct($broker, $shellSocket){
    $this->broker = $broker;
    $this->shellSocket = $shellSocket;
  }
  public function call($header, $content, $zmqId = null){
    $this->broker->send($this->shellSocket, 'history_reply', ['history' => []], $header, [], $zmqId);
  }
}
