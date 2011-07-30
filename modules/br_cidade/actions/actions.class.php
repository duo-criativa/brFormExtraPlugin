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

class br_cidadeActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    if($request->isXmlHttpRequest()){

      $cidades = Doctrine_Core::getTable('CidadeBr')->createQuery('c')->select('uf,nome')->where('uf = ?', $request->getParameter('uf'))->execute(null,Doctrine_Core::HYDRATE_ARRAY);

      if($request->getParameter('add_empty')) {
        $add_empty = array();
        $add_empty[] = array (
          'id' => '',
          'uf' => $request->getParameter('uf'),
          'nome' => urldecode($request->getParameter('add_empty')),
        );
        $cidades = array_merge($add_empty,$cidades);
      }

      return $this->renderText(json_encode($cidades));
    }
    $this->forward404();
  }
}
