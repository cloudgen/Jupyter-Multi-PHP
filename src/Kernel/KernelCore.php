<?php

/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Cloudgen\JupyterMultiPHP\Kernel;

use Cloudgen\JupyterMultiPHP\Handlers\HbErrorHandler;
use Cloudgen\JupyterMultiPHP\Handlers\HbMessagesHandler;
use Cloudgen\JupyterMultiPHP\Handlers\IOPubMessagesHandler;
use Cloudgen\JupyterMultiPHP\Handlers\ShellMessagesHandler;
use React\EventLoop\Factory as ReactFactory;
use React\ZMQ\Context as ReactZmqContext;
use React\ZMQ\SocketWrapper;

/**
* Class KernelCore (no pun intended)
*/
final class KernelCore{
  use \Cloudgen\JupyterMultiPHP\Concern\Loggable;
  use \Cloudgen\JupyterMultiPHP\Concern\Brokable;
  use \Cloudgen\JupyterMultiPHP\Concern\Heartbeatable;
  use \Cloudgen\JupyterMultiPHP\Concern\IOPubListenable;
  use \Cloudgen\JupyterMultiPHP\Concern\ShellListenable;
  /** @var \React\EventLoop\ExtEventLoop|\React\EventLoop\LibEventLoop|\React\EventLoop\LibEvLoop|\React\EventLoop\StreamSelectLoop */
  private $reactLoop;
  /** @var SocketWrapper|\ZMQSocket */
  private $controlSocket;
  /** @var SocketWrapper|\ZMQSocket */
  private $stdinSocket;
  public function __construct( $jupyterBroker,  $connUris,  $logger){
    $this->broker = $jupyterBroker;
    $this->logger = $logger;
    $this->initSockets($connUris);
    $this->registerHandlers();
  }
  public function run(){
    $this->reactLoop->run();
  }
  /**
  * @param array [string]string $connUris
  */
  private function initSockets(array $connUris){
    // Create context
    $this->reactLoop = ReactFactory::create();
    /** @var ReactZmqContext|\ZMQContext $reactZmqContext */
    $reactZmqContext = new ReactZmqContext($this->reactLoop);
    $this->hbSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_REP);
    $this->hbSocket->bind($connUris['hb']);
    $this->iopubSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_PUB);
    $this->iopubSocket->bind($connUris['iopub']);
    $this->controlSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_ROUTER);
    $this->controlSocket->bind($connUris['control']);
    $this->stdinSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_ROUTER);
    $this->stdinSocket->bind($connUris['stdin']);
    $this->shellSocket = $reactZmqContext->getSocket(\ZMQ::SOCKET_ROUTER);
    $this->shellSocket->bind($connUris['shell']);
    //$this->logger->debug('Initialized sockets', ['processId' => getmypid()]);
  }
  private function registerHandlers(){
    $this->hbSocket->on(
      'error',
      new HbErrorHandler($this->logger->withName('HbErrorHandler'))
    );
    $this->hbSocket->on(
      'messages',
      new HbMessagesHandler($this->hbSocket, $this->logger->withName('HbMessagesHandler'))
    );
    $this->iopubSocket->on(
      'messages',
      new IOPubMessagesHandler($this->logger->withName('IOPubMessagesHandler'))
    );
    $this->shellSocket->on(
      'messages',
      new ShellMessagesHandler(
        $this->broker,
        $this->iopubSocket,
        $this->shellSocket,
        $this->logger->withName('ShellMessagesHandler')
        )
    );
    //$this->logger->debug('Registered handlers', ['processId' => getmypid()]);
  }
}
