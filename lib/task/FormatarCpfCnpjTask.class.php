<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paulo
 * Date: 12/2/11
 * Time: 10:01 AM
 * To change this template use File | Settings | File Templates.
 */
class FormatarCpfCnpjTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
                           new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
                           new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
                           new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
                           // add your own options here
                           new sfCommandOption('model', null, sfCommandOption::PARAMETER_REQUIRED, 'O nome do model a ser utilizado. Exemplo: sfGuardUser', false),
                           new sfCommandOption('field', null, sfCommandOption::PARAMETER_REQUIRED, 'O campo do model a ser formatado. Exemplo: cpf_cnpj', false),
                           new sfCommandOption('where', null, sfCommandOption::PARAMETER_REQUIRED, "A clausura WHERE para restringir os registros a serem formatados. Exemplo: created_at>'2011-06-01'", false),
    ));

    $this->namespace = 'br';
    $this->name = 'formatar-cpf-cnpj';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
A tarefa [formatar-cpf-cnpj|INFO] formata o CPF/CPNJ de uma coluna de uma tabela.
Execute:

  [php symfony br:formatar-cpf-cnpj|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();



  }


}
