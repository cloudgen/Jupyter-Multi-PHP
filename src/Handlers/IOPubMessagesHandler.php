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
 final class IOPubMessagesHandler{
   use \Cloudgen\JupyterMultiPHP\Concern\Loggable;
   public function __invoke($msg){
     $this->message_received($msg);
   }
 }
