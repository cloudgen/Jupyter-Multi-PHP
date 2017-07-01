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

use Cloudgen\JupyterMultiPHP\Actions\ExecuteAction;
use Cloudgen\JupyterMultiPHP\Actions\HistoryAction;
use Cloudgen\JupyterMultiPHP\Actions\KernelInfoAction;
use Cloudgen\JupyterMultiPHP\Actions\ShutdownAction;
use Cloudgen\JupyterMultiPHP\Broker\JupyterBroker;
use Cloudgen\JupyterMultiPHP\Kernel\KernelOutput;
use Psy\Shell;

final class ShellMessagesHandler{
  use \Cloudgen\JupyterMultiPHP\Concern\Loggable;
  /** @var ExecuteAction */
  private $executeAction;
  /** @var HistoryAction */
  private $historyAction;
  /** @var KernelInfoAction */
  private $kernelInfoAction;
  /** @var ShutdownAction */
  private $shutdownAction;
  /** @var Shell */
  private $shellSoul;
  public function __construct(
     $broker,
     $iopubSocket,
     $shellSocket,
     $logger
  ){
    $this->shellSoul = new Shell();
    $this->executeAction = new ExecuteAction($broker, $iopubSocket, $shellSocket, $this->shellSoul);
    $this->historyAction = new HistoryAction($broker, $shellSocket);
    $this->kernelInfoAction = new KernelInfoAction($broker, $shellSocket, $iopubSocket);
    $this->shutdownAction = new ShutdownAction($broker, $iopubSocket, $shellSocket);
    $this->logger = $logger;
    $broker->send(
      $iopubSocket, 'status', ['execution_state' => 'starting'], []
    );
    $this->shellSoul->setOutput(new KernelOutput($this->executeAction, $this->logger->withName('KernelOutput')));
  }

  public function __invoke(array $msg){
    list($zmqId, $delim, $hmac, $header, $parentHeader, $metadata, $content) = $msg;

    $header = json_decode($header, true);
    $content = json_decode($content, true);

    /*$this->logger->debug('Received message', [
      'processId' => getmypid(),
      'zmqId' => htmlentities($zmqId, ENT_COMPAT, "UTF-8"),
      'delim' => $delim,
      'hmac' => $hmac,
      'header' => $header,
      'parentHeader' => $parentHeader,
      'metadata' => $metadata,
      'content' => $content
    ]);*/

    if ('kernel_info_request' === $header['msg_type']){
      $this->kernelInfoAction->call($header, $content, $zmqId);
    } elseif ('execute_request' === $header['msg_type']){
      $this->executeAction->call($header, $content, $zmqId);
    } elseif ('history_request' === $header['msg_type']){
      $this->historyAction->call($header, $content, $zmqId);
    } elseif ('shutdown_request' === $header['msg_type']){
      $this->shutdownAction->call($header, $content, $zmqId);
    } elseif ('comm_open' === $header['msg_type']){
      // TODO: Research about what should be done.
    } else{
      $this->logger->error('Unknown message type', ['processId' => getmypid(), 'header' => $header]);
    }
  }
}
