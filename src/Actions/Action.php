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
interface Action{
  public function call($header, $content, $zmqId = null);
}
