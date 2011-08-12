<?php
/**
 * Created by PhpStorm.
 * User: Paulo
 * Date: 11/07/2010
 * Time: 17:33:30
 * To change this template use File | Settings | File Templates.
 */
 
class sfValidatorSoftCep extends sfValidatorBase {
  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    /* Retira todos os caracteres que nao sejam 0-9 */
    $nclean = "";
    for ($i = 1; $i <= strlen($clean); $i = $i + 1)
    {
      $ch = substr($clean, $i - 1, 1);
      if (ord($ch) >= 48 && ord($ch) <= 57)
      {
        $nclean = $nclean . $ch;
      }
    }
    $clean = $nclean;
    /**/

    /* valida o tamanho do valor digitado */
    $length = function_exists('mb_strlen') ? mb_strlen($clean, $this->getCharset()) : strlen($clean);

    if ($length != 8)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
    /**/

    return $clean;

  }
}
