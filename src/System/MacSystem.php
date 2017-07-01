<?php

/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Cloudgen\JupyterMultiPHP\System;
final class MacSystem extends UnixSystem{
  public function getOperativeSystem(){
    return self::OS_OSX;
  }
  public function getAppDataDirectory(){
    return $this->getCurrentUserHome().'/Library/jupyter-multi-php';
  }
}
