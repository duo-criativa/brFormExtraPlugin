<?php
/*
* This file is part of the brFormExtraPlugin package.
*
* (c) Paulo Ribeiro <paulo@duocriativa.com.br>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*
* @author Paulo Ribeiro <paulo@duocriativa.com.br>
*/
class FormatarCpfCnpjTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
                           new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
                           new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
                           new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->addArgument('model', sfCommandArgument::REQUIRED, 'O nome do model a ser utilizado. Exemplo: sfGuardUser');
    $this->addArgument('field', sfCommandArgument::REQUIRED, 'O campo do model a ser formatado. Exemplo: cpf_cnpj');
    $this->addArgument('where', sfCommandArgument::OPTIONAL, "A clausura WHERE para restringir os registros a serem formatados. Exemplo: created_at>'2011-06-01'");

    $this->namespace = 'br';
    $this->name = 'formatar-cpf-cnpj';
    $this->briefDescription = 'Formata o CPF/CPNJ de uma coluna de uma tabela';
    $this->detailedDescription = <<<EOF
A tarefa [formatar-cpf-cnpj|INFO] formata o CPF/CPNJ de uma coluna de uma tabela.
Execute por exemplo:

  [php symfony br:formatar-cpf-cnpj sfGuardUser cpf_cnpj|INFO]
  [php symfony br:formatar-cpf-cnpj sfGuardUser cpf_cnpj " created_at>'2011-06-01' " |INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $model = $arguments['model'];
    $field = $arguments['field'];
    $method_get = 'get' . sfInflector::camelize($field);
    $method_set = 'set' . sfInflector::camelize($field);
    $where = false;
    if(isset($arguments['where']))
      $where = $arguments['where'];

    try {
//      $connection->beginTransaction();

      if(!class_exists($model)) throw new sfCommandArgumentsException(sprintf('A classe %s nao foi encontrada.', $model));
      $obj = new $model();
      if(!is_callable(array($obj, $method_get))) throw new sfCommandArgumentsException(sprintf('O metodo %s nao foi encontrado na classe.', $method_get, $model));
      if(!is_callable(array($obj, $method_set))) throw new sfCommandArgumentsException(sprintf('O metodo %s nao foi encontrado na classe.', $method_set, $model));

      $query = Doctrine_Core::getTable($model)->createQuery();
      if($where!==false) $query->addWhere($where);

      $validator = new sfValidatorCpfCnpj(array('required'=>false));

      $num_registros_alterados = 0;
      $num_registros_nao_alterados= 0;
      $num_registros_invalidos = 0;
      $num_registros_vazios = 0;

//      $this->log($query->getSqlQuery());
      $results = $query->execute();
      foreach ($results as $record) {
        $antigo = $record->$method_get();
        if($antigo){
          try{
            $novo = $validator->clean($antigo);
            if($novo==$antigo) {
              $num_registros_nao_alterados++;
            } else {
              $record->$method_set($novo);
              $record->save();
              $num_registros_alterados++;
            }
          } catch (Exception $e){
            $num_registros_invalidos++;
            $this->log(sprintf('O CPF/CPNJ %s do %s com id %s nao eh valido: %s.', $antigo, $model, $record->getId(), $e->getMessage()));
          }
        } else {
          $num_registros_vazios++;
        }
      }

      $this->logSection('Estatisticas', 'Veja o resumo da operacao');
      $this->log(sprintf('   Numero de registros alterados: %s', $num_registros_alterados));
      $this->log(sprintf('   Numero de registros NAO alterados: %s', $num_registros_nao_alterados));
      $this->log(sprintf('   Numero de registros vazios: %s', $num_registros_vazios));
      $this->log(sprintf('   Numero de registros invalidos: %s', $num_registros_invalidos));

//      $connection->commit();
    } catch (Exception $e){
//      $connection->rollback();
      $this->logSection('ERRO', $e->getMessage());
      throw $e;
    }


  }


}
