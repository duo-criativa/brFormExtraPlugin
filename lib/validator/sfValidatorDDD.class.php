<?php
/**
 * Created by PhpStorm.
 * User: Paulo
 * Date: 06/09/2010
 * Time: 06:01:04
 * To change this template use File | Settings | File Templates.
 */

class sfValidatorDDD extends sfValidatorBase
{
  public static $DDDs = array(68, 82, 96, 92, 97, 71, 73, 74, 75, 77, 85, 88, 61, 27, 28, 61, 62, 64, 98, 99, 65, 66, 67, 31, 32, 33, 34, 35, 37, 38, 91, 93, 94, 83, 41, 42, 43, 44, 45, 46, 81, 87, 86, 89, 21, 22, 24, 84, 51, 53, 54, 55, 69, 95, 47, 48, 49, 11, 12, 13, 14, 15, 16, 17, 18, 19, 79, 63);

  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('invalid_ddd', 'DDD inválido.');
  }


  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {

    if (!self::inDDDs($value, self::$DDDs))
    {
      throw new sfValidatorError($this, 'invalid_ddd', array('value' => $value));
    }

    return $value;
  }


  /**
   * Checks if a value is part of given choices (see bug #4212)
   *
   * @param  mixed $value   The value to check
   * @param  array $choices The array of available choices
   *
   * @return Boolean
   */
  static protected function inDDDs($value, array $choices = array())
  {
    foreach ($choices as $choice)
    {
      if ((int) $choice == (int) $value)
      {
        return true;
      }
    }

    return false;
  }

}
