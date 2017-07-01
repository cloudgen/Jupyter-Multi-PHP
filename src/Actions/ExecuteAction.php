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

use Cloudgen\JupyterMultiPHP\Broker\JupyterBroker;
use Psy\Exception\BreakException;
use Psy\Exception\ThrowUpException;
use Psy\ExecutionLoop\Loop;
use Psy\Shell;

final class ExecuteAction implements Action{
  use \Cloudgen\JupyterMultiPHP\Concern\Brokable;
  use \Cloudgen\JupyterMultiPHP\Concern\IOPubListenable;
  use \Cloudgen\JupyterMultiPHP\Concern\ShellListenable;
  /** @var Shell */
  private $shellSoul;
  /** @var array */
  private $header;
  /** @var string */
  private $code;
  /** @var bool */
  private $silent;
  /** @var int */
  private $execCount = 0;
  public function __construct(
    $broker,
    $iopubSocket,
    $shellSocket,
    $shellSoul
  ){
    $this->broker = $broker;
    $this->iopubSocket = $iopubSocket;
    $this->shellSocket = $shellSocket;
    $this->shellSoul = $shellSoul;
  }

  public function call($header, $content, $zmqId = null){
    $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'busy'], $header);
    $this->header = $header;
    $this->code = $content['code'];
    $this->silent = $content['silent'];
    if (!$this->silent){
      $this->execCount = $this->execCount + 1;
      $this->broker->send(
        $this->iopubSocket,
        'execute_input',
        ['code' => $this->code, 'execution_count' => $this->execCount],
        $this->header
      );
    }
    $temp = $this->getClosure();
    $temp();
    $replyContent = [
      'status' => 'ok',
      'execution_count' => $this->execCount,
      'payload' => [],
      'user_expressions' => new \stdClass
    ];
    $this->broker->send($this->shellSocket, 'execute_reply', $replyContent, $this->header, [], $zmqId);
    $this->broker->send($this->iopubSocket, 'status', ['execution_state' => 'idle'], $this->header);
  }

  public function notifyMessage($message){
    $this->broker->send(
      $this->iopubSocket,
      'execute_result',
      ['execution_count' => $this->execCount, 'data' => ['text/plain' => $message], 'metadata' => new \stdClass],
      $this->header
    );
  }

  private function getClosure(){
    $closure = function (){
      extract($this->shellSoul->getScopeVariables());
      try{
        $this->shellSoul->addCode($this->code);
        // evaluate the current code buffer
        ob_start(
          [$this->shellSoul, 'writeStdout'],
          version_compare(PHP_VERSION, '5.4', '>=') ? 1 : 2
        );
        set_error_handler([$this->shellSoul, 'handleError']);
        $_ = eval($this->shellSoul->flushCode() ?: Loop::NOOP_INPUT);
        restore_error_handler();
        ob_end_flush();
        $this->shellSoul->writeReturnValue($_);
      } catch (BreakException $_e){
        restore_error_handler();
        if (ob_get_level() > 0){
          ob_end_clean();
        }
        $this->shellSoul->writeException($_e);
        return;
      } catch (ThrowUpException $_e){
        restore_error_handler();
        if (ob_get_level() > 0){
          ob_end_clean();
        }
        $this->shellSoul->writeException($_e);
        throw $_e;
      } catch (\Exception $_e){
        restore_error_handler();
        if (ob_get_level() > 0){
          ob_end_clean();
        }
        $this->shellSoul->writeException($_e);
      }
      $this->shellSoul->setScopeVariables(get_defined_vars());
    };
    return $closure;
  }
}
