<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorCpfCnpj validates a CPF (Brazilian individual taxpayer identification)
 * or CNPJ (Brazilian taxpayer identification)
 * Accepts and return values formated/non-formated
 *
 * @package    symfony
 * @subpackage validator
 * @author     Rafael Goulart <rafaelgou@rgou.net>
 */
class sfValidatorCpfCnpj extends sfValidatorString
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * type: Type of validation (cpfcnpj, cpf, cnpj - default: cpfcnpj)
   *  * formated: If true "clean" method returns a formated value, i.e. 000.000.000-00 (default: false)
   *              Use to store formated value in DB
   *  * use_cnpj_with_15_chars: Returns CNPJ with 15 characters
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', 'CPF/CNPJ Inválido');
    $this->setMessage('required', 'CPF/CNPJ Obrigatório');
    $this->addOption('type', 'cpfcnpj');
    $this->addOption('formated', false);
    $this->addOption('use_cnpj_with_15_chars', false);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $is_form_filter = false;
    if(is_array($value) && isset($value['text'])){ // isso acontece qdo utiliza-se em FormFilters
        $clean = (string) $value['text'];
        $is_form_filter = true;
    } else {
        $clean = (string) $value;
    }

    if($clean=='' && $this->getOption('required')==false) return $value;

    $length = function_exists('mb_strlen') ? mb_strlen($clean, $this->getCharset()) : strlen($clean);

    $is_valid_cpf = $this->checkCPF($clean);
    $is_valid_cnpj = $this->checkCNPJ($clean);
    switch ($this->getOption('type')) {

      case 'cnpj':
        if (!$is_valid_cnpj) throw new sfValidatorError($this, 'invalid');
        break;

      case 'cpf':
        if (!$is_valid_cpf) throw new sfValidatorError($this, 'invalid');
        break;

      case 'cpfcnpj':
      default:
        if (!($is_valid_cpf || $is_valid_cnpj)) throw new sfValidatorError($this, 'invalid');
        break;

    }

    if ($this->getOption('formated'))
    {

      return $this->formatCPFCNPJ($clean);

    } else {

      $clean = $this->valueClean($clean);

      if($is_valid_cnpj && strlen($clean) == 14 && $this->getOption('use_cnpj_with_15_chars'))
	    {
	      $clean =  '0' . $clean;
	    }

      if($is_form_filter) return array('text'=>$clean);
      return $clean;

    }
    
  }

  /**
   * checkCPF
   * Baseado em http://www.vivaolinux.com.br/script/Validacao-de-CPF-e-CNPJ/
   * Algoritmo em http://www.geradorcpf.com/algoritmo_do_cpf.htm
   * @param $cpf string
   * @author Rafael Goulart <rafaelgou@rgou.net>
   */
  protected function checkCPF($cpf) {

    // Limpando caracteres especiais
    $cpf = $this->valueClean($cpf);

    // Quantidade mínima de caracteres ou erro
    if (strlen($cpf) <> 11) return false;

    // Primeiro dígito
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
       $soma += ((10-$i) * $cpf[$i]);
    }
    $d1 = 11 - ($soma % 11);
    if ($d1 >= 10) $d1 = 0;

    // Segundo Dígito
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
       $soma += ((11-$i) * $cpf[$i]);
    }
    $d2 = 11 - ($soma % 11);
    if ($d2 >= 10) $d2 = 0;

    if ($d1 == $cpf[9] && $d2 == $cpf[10]) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * checkCNPJ
   * Baseado em http://www.vivaolinux.com.br/script/Validacao-de-CPF-e-CNPJ/
   * Algoritmo em http://www.geradorcnpj.com/algoritmo_do_cnpj.htm
   * @param $cnpj string
   * @author Rafael Goulart <rafaelgou@rgou.net>
   */
  protected function checkCNPJ($cnpj) {
    $cnpj = $this->valueClean($cnpj);
    if (strlen($cnpj) <> 14) return false;

    // Primeiro dígito
    $multiplicadores = array(5,4,3,2,9,8,7,6,5,4,3,2);
    $soma = 0;
    for ($i = 0; $i <= 11; $i++) {
       $soma += $multiplicadores[$i] * $cnpj[$i];
    }
    $d1 = 11 - ($soma % 11);
    if ($d1 >= 10) $d1 = 0;

    // Segundo dígito
    $multiplicadores = array(6,5,4,3,2,9,8,7,6,5,4,3,2);
    $soma = 0;
    for ($i = 0; $i <= 12; $i++) {
       $soma += $multiplicadores[$i] * $cnpj[$i];
    }
    $d2 = 11 - ($soma % 11);
    if ($d2 >= 10) $d2 = 0;

    if ($cnpj[12] == $d1 && $cnpj[13] == $d2) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * valueClean
   * Retira caracteres especiais
   * @param $value string
   * @author Rafael Goulart <rafaelgou@rgou.net>
   */
  protected function valueClean ($value)
  {
    $value = str_replace (array(')','(','/','.','-',' '),'',$value);
    if(strlen($value) == 15)
    {
      $value =  substr($value, 1, 15); 
    }
    return $value;
  }

  /**
   * formatCPFCNPJ
   * Retorna CPF/CNPJ Formatado
   * @param $value string
   * @author Rafael Goulart <rafaelgou@rgou.net>
   */
  protected function formatCPFCNPJ ($value) {
    $value = $this->valueClean($value);
    if (strlen($value) == 11)
    {
      return substr($value, 0, 3) . '.' .
             substr($value, 3, 3) . '.' .
             substr($value, 6, 3) . '-' .
             substr($value, 9, 2);

    } else {
      $value = substr($value, 0, 2) . '.' .
               substr($value, 2, 3) . '.' .
               substr($value, 5, 3) . '/' .
               substr($value, 8, 4) . '-' .
               substr($value, 12, 2);
     

      if(strlen($value) == 14 && $this->getOption('use_cnpj_with_15_chars'))
	    {
	      $value =  '0' . $value;
	    }
      return $value;
    }

  }

}
