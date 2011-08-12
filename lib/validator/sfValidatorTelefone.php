<?php
/**
 * Created by PhpStorm.
 * User: Paulo
 * Date: 06/09/2010
 * Time: 07:43:37
 * To change this template use File | Settings | File Templates.
 */
/**
 * Original:
 *
 * sfValidatorPhone validates a phone number.
 *
 * @author Jason Swett (http://jasonswett.net/how-to-validate-a-phone-number-in-symfony)
 * @throws sfValidatorError
 */

class sfValidatorTelefone extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('invalid_telefone', 'Telefone inválido. Utilize o formato (99)9999-9999');
  }


  protected function doClean($value)
  {
    $clean = (string) $value;

    $phone_number_pattern = '/^(^(1\s*[-\/\.]?)?(\((\d{2})\)|(\d{2}))\s*[-\/\.]?\s*(\d{4})\s*[-\/\.]?\s*(\d{4})\s*(([xX]|[eE][xX][tT])\.?\s*(\d+))*$)*$/';

    // If the value isn't a phone number, throw an error.
    if (!preg_match($phone_number_pattern, $clean))
    {
      throw new sfValidatorError($this, 'invalid_telefone', array('value' => $value));
    }

    // Take out anything that's not a number.
    $clean = preg_replace('/[^0-9]/', '', $clean);

    // Split the phone number into its three parts.
    $first_part = substr($clean, 0, 2);

    // valida o DDD
    $validatorDDD = new sfValidatorDDD();
    $validatorDDD->clean($first_part);

    $second_part = substr($clean, 2, 4);
    $third_part = substr($clean, 6, 4);

    // Format the phone number.
    $clean = '(' . $first_part . ') ' . $second_part . '-' . $third_part;

    return $clean;
  }

}
