<?php
/*
* This file is part of Jupyter-Multi-PHP.
*
* (c) 2017 Cloudgen Wong
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Cloudgen\JupyterMultiPHP\Concern;
use React\ZMQ\SocketWrapper;
trait Signable{
  /** @var string */
  private $key;
  /** @var string */
  private $hashAlgorithm;
  /** @var string */
  private $signatureScheme;
  private function setHashAlogrithm($signatureScheme){
    $this->signatureScheme = $signatureScheme;
    $this->hashAlgorithm = preg_split('/-/', $signatureScheme)[1];
  }
  private function sign($message_list){
    $hm = hash_init(
      $this->hashAlgorithm,
      HASH_HMAC,
      $this->key
    );
    foreach ($message_list as $item){
      hash_update($hm, $item);
    }
    return hash_final($hm);
  }
}
