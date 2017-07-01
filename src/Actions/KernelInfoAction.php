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

final class KernelInfoAction implements Action{
  use \Cloudgen\JupyterMultiPHP\Concern\Brokable;
  use \Cloudgen\JupyterMultiPHP\Concern\IOPubListenable;
  use \Cloudgen\JupyterMultiPHP\Concern\ShellListenable;
  public function __construct($broker, $shellSocket, $iopubSocket){
    $this->broker = $broker;
    $this->shellSocket = $shellSocket;
    $this->iopubSocket = $iopubSocket;
  }

  public function call($header, $content, $zmqId = null){
    $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);
    $this->broker->send(
      $this->shellSocket,
      'kernel_info_reply',
      [
        'protocol_version' => '5.0',
        'implementation' => 'jupyter-multi-php',
        'implementation_version' => '0.1.0',
        'banner' => 'Jupyter-Multi-PHP Kernel',
        'language_info' => [
          'name' => 'PHP',
          'version' => phpversion(),
          'mimetype' => 'text/x-php',
          'file_extension' => '.php',
          'pygments_lexer' => 'PHP',
        ],
        'status' => 'ok',
      ],
      $header,
      [],
      $zmqId
    );
    $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $header);
  }
}
