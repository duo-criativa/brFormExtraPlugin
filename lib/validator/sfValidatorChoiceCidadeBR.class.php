<?php
 /** 
 * ${description}
 *
 * Propriedade intelectual de Duo Criativa (www.duocriativa.com.br).
 *
 * @author     Paulo R. Ribeiro <paulo@duocriativa.com.br>
 * @package    ${package}
 * @subpackage ${subpackage}
 */
 
class sfValidatorChoiceCidadeBR  extends sfValidatorBase {

    /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:        O model da cidade utilizado na consulta. (CidadeBr by default)
   *
   * Available error codes:
   *
   *  * invalid
   *  * invalid_uf
   *
   * @param array $options    An array of options
   * @param array $messages   An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {

    $this->addMessage('invalid', 'Cidade inválida.');
    $this->addMessage('invalid_uf', 'Cidade não pertece ao estado escolhido.');

    $this->addOption('model', 'CidadeBr');

  }

  /**
   * Cleans the input value.
   *
   * Every subclass must implements this method.
   *
   * @param  mixed $value  The input value
   *
   * @return mixed The cleaned value
   *
   * @throws sfValidatorError
   */
  protected function doClean($value)
  {
    if(!is_array($value)) throw new sfValidatorError($this, 'invalid', array());

    // cidade required e nenhuma escolhida
    if($this->getOption('required') && empty($value['cidade'])) throw new sfValidatorError($this, 'required');

    if(!empty($value['cidade'])){

      $cidade = Doctrine_Core::getTable($this->getOption('model'))->createQuery('c')->where('c.id = ?')->execute(array($value['cidade']), Doctrine_Core::HYDRATE_ARRAY);
      if(count($cidade)==0)
      {
        throw new sfValidatorError($this, 'invalid');
      }
      $cidade = array_shift($cidade);

      if($cidade['uf']!=$value['uf']) throw new sfValidatorError($this,'invalid_uf');

    }

    return (integer) $value['cidade'];
  }

}
