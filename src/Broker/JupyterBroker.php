<?php
/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Cloudgen\JupyterMultiPHP\Broker;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use React\ZMQ\SocketWrapper;

final class JupyterBroker{
  use \Cloudgen\JupyterMultiPHP\Concern\Loggable;
  use \Cloudgen\JupyterMultiPHP\Concern\Signable;
  /** @var UuidInterface */
  private $sessionId;

  public function __construct($key, $signatureScheme,  $sessionId, $logger = null){
    $this->key = $key;
    $this->setHashAlogrithm($signatureScheme);
    $this->sessionId = $sessionId;
    $this->logger = $logger;
  }

  public function send(
    SocketWrapper $stream,
    $msgType,
    array $content = [],
    array $parentHeader = [],
    array $metadata = [],
    $zmqId = null
  ){
    $header = $this->createHeader($msgType);
    $msgDef = [
      json_encode(empty($header) ? new \stdClass : $header),
      json_encode(empty($parentHeader) ? new \stdClass : $parentHeader),
      json_encode(empty($metadata) ? new \stdClass : $metadata),
      json_encode(empty($content) ? new \stdClass : $content),
    ];

    if (null !== $zmqId){
      $finalMsg = [$zmqId];
    } else{
      $finalMsg = [];
    }

    $finalMsg = array_merge(
      $finalMsg,
      ['<IDS|MSG>', $this->sign($msgDef)],
      $msgDef);

      if (null !== $this->logger){
        //$this->logger->debug('Sending message', ['processId' => getmypid(), 'message' => $finalMsg]);
      }

      $stream->send($finalMsg);
    }

    private function createHeader($msgType){
      return [
        'date'     => (new \DateTime('NOW'))->format('c'),
        'msg_id'   => Uuid::uuid4()->toString(),
        'username' => "kernel",
        'session'  => $this->sessionId->toString(),
        'msg_type' => $msgType,
        'version'  => '5.0',
      ];
    }
  }
