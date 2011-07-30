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

class sfWidgetFormChoiceCidadeBR extends sfWidgetForm
{

  protected $estados;
  protected static $cidades;
  protected $widgets;

  /**
   * Configures the current widget.
   *
   * Available options:
   *
   *  * model:        O model da cidade utilizado na consulta. (CidadeBr by default)
   *  * url:          Url utilizada para retornar a lista de cidades de um estado via AJAX.
   *  * format:       Formato de exibicao do widget (optional)
   *  * type_estado:  Formato de exibicao do estado (optional) (nome, sigla+nome, sigla).
   *  * cidade_add_empty: O texto que será exibido para o item vazio. (optional)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    sfProjectConfiguration::getActive()->loadHelpers('Url');

    $this->addOption('model', 'CidadeBr');

    $this->addOption('url', url_for('@br_form_extra_plugin_cidades_br_json'));
    $this->addOption('format', '<div class="widget_cidade"> %estado% &nbsp;&nbsp; %cidade%</div>');
    $this->addOption('item_format', '<div class="widget_cidade_item item-%item%">%label% %widget%</div>');

    $this->addOption('estado_type', 'nome');
    $this->addOption('estado_label', 'Estado');
    $this->addOption('cidade_label', 'Cidade');

    $this->addOption('cidade_add_empty', false);

  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The date displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $values['uf'] = null;
    $values['cidade'] = null;
    if (!is_null($value))
    {
      if (is_array($value))
      {
        $values['uf'] = $value['uf'];
        $values['cidade'] = $value['cidade'];
      } else
      {
        $cidade = Doctrine_Core::getTable($this->getOption('model'))->findOneById($value);
        if ($cidade)
        {
          $values['uf'] = $cidade->getUf();
          $values['cidade'] = $value;
        }
      }
    }


    switch ($this->getOption('estado_type'))
    {
      case 'nome':
        $this->estados = $this->getUFNome();
        break;

      case 'sigla+nome':
        $this->estados = $this->getUFNomeSigla();
        break;

      case 'sigla':
      default:
        $this->estados = $this->getUFSigla();
        break;
    }

    if (is_null($values['uf']))
    {
      foreach ($this->estados as $uf => $estado)
      {
        $values['uf'] = $uf;
        break;
      }
    }

    if (!isset(self::$cidades[$values['uf']]))
    {

      $cidades = Doctrine_Core::getTable($this->getOption('model'))->createQuery()->where('uf LIKE ?', $values['uf'])->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
      self::$cidades[$values['uf']] = array();
      foreach ($cidades as $c)
      {
        self::$cidades[$values['uf']][$c['id']] = $c['nome'];
      }
    }


    $widget = array();
    $emptyValues = $this->getOption('empty_values');

    $uf_name = $name . '[uf]';
    $widget['%estado%'] = $this->renderEstadoWidget($uf_name, $values['uf'], array('choices' => $this->estados, 'id_format' => $this->getOption('id_format')), array_merge($this->attributes, $attributes));
    $cidade_name = $name . '[cidade]';
    $widget['%cidade%'] = $this->renderCidadeWidget($cidade_name, $values['cidade'], array('choices' => $this->getOption('cidade_add_empty')
                                                                    ? array('' => $this->getOption('cidade_add_empty')) + self::$cidades[$values['uf']]
                                                                    : self::$cidades[$values['uf']], 'id_format' => $this->getOption('id_format')), array_merge($this->attributes, $attributes));

    $add_empty = "";
    if (false !== $this->getOption('cidade_add_empty'))
    {
      $add_empty = "+\"&add_empty=" . urlencode($this->getOption('cidade_add_empty')) . '"';
    }

    $uf_id = $this->generateId($uf_name);
    $cidade_id = $this->generateId($cidade_name);
    $cidade_wrapper_id = $cidade_id . '-wrapper';

    $widget['%cidade%'] = strtr($this->getOption('item_format'), array(
                                                                    '%label%' => $this->getOption('cidade_label'), '%item%' => 'cidade', '%widget%' =>$widget['%cidade%']
                                                               ));
    $widget['%estado%'] = strtr($this->getOption('item_format'), array(
                                                                    '%label%' => $this->getOption('estado_label'), '%item%' => 'estado', '%widget%' =>$widget['%estado%']
                                                               ));

    return strtr($this->getOption('format'), $widget) .
           strtr(<<<EOF
<script type="text/javascript">
  jQuery(document).ready(function() {

    function dc_widget_cidades_br_preenche_cidades_%cidade_id% (){
        jQuery.ajax({
            type: "GET",
            url: "%url%?uf="+jQuery('#%uf_id%').val()$add_empty,
            dataType: "json",
            success: function(data){
                var select_options = "";
                jQuery.each(data, function(i,n){
                  select_options += "<option value='"+n["id"]+"'>"+n["nome"]+"</option>";
                });
                jQuery('#%cidade_id%').html(select_options);
            }
        });
    }


    jQuery("#%uf_id%").change(function(){ dc_widget_cidades_br_preenche_cidades_%cidade_id%(); });
  });
</script>
EOF
             ,
             array(
                  '%url%' => $this->getOption('url'),
                  '%cidade_id%' => $cidade_id,
                  '%uf_id%' => $uf_id,
                  '%cidade_name%' => $cidade_name,
                  '%uf_name%' => $uf_name,
             )
           );
    //    return strtr($this->getOption('format'), $widget);
  }

  /**
   * @param string $name
   * @param string $value
   * @param array $options
   * @param array $attributes
   * @return string rendered widget
   */
  protected function renderEstadoWidget($name, $value, $options, $attributes)
  {
    $widget = new sfWidgetFormSelect($options, $attributes);
    $this->widgets['uf'] = $widget;
    return $widget->render($name, $value);
  }

  /**
   * @param string $name
   * @param string $value
   * @param array $options
   * @param array $attributes
   * @return string rendered widget
   */
  protected function renderCidadeWidget($name, $value, $options, $attributes)
  {
    $widget = new sfWidgetFormSelect($options, $attributes);
    $this->widgets['cidade'] = $widget;
    return $widget->render($name, $value);
  }


  public function getUFCompleto()
  {
    return array(
      "AC" => "Acre",
      "AL" => "Alagoas",
      "AP" => "Amapá",
      "AM" => "Amazonas",
      "BA" => "Bahia",
      "CE" => "Ceará",
      "DF" => "Distrito Federal",
      "ES" => "Espírito Santo",
      "GO" => "Goiás",
      "MA" => "Maranhão",
      "MG" => "Minas Gerais",
      "MS" => "Mato Grosso do Sul",
      "MT" => "Mato Grosso",
      "PA" => "Pará",
      "PB" => "Paraíba",
      "PR" => "Paraná",
      "PE" => "Pernambuco",
      "PI" => "Piauí",
      "RJ" => "Rio de Janeiro",
      "RN" => "Rio Grande do Norte",
      "RS" => "Rio Grande do Sul",
      "RO" => "Rondônia",
      "RR" => "Roraima",
      "SP" => "São Paulo",
      "SC" => "Santa Catarina",
      "SE" => "Sergipe",
      "TO" => "Tocantins",
    );
  }

  public function getUFSigla()
  {
    foreach ($this->getUfCompleto() as $key => $value)
    {
      $ufs[$key] = $key;
    }
    return $ufs;
  }

  public function getUFNome()
  {
    return $this->getUFCompleto();
  }

  public function getUFNomeSigla()
  {
    foreach ($this->getUfCompleto() as $key => $value)
    {
      $ufs[$key] = "$key - $value";
    }
    return $ufs;
  }


}
