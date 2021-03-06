<?php

require_once 'CoreExt/DataMapper.php';
require_once 'TabelaArredondamento/Model/Tabela.php';
require_once 'RegraAvaliacao/Model/Nota/TipoValor.php';
require_once 'App/Model/IedFinder.php';

/**
 * TabelaArredondamento_Model_TabelaDataMapper class.
 *
 * @author      Eriksen Costa Paixão <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     TabelaArredondamento
 * @subpackage  Modules
 * @since       Classe disponível desde a versão 1.1.0
 * @version     @@package_version@@
 */
class TabelaArredondamento_Model_TabelaDataMapper extends CoreExt_DataMapper
{
  protected $_entityClass = 'TabelaArredondamento_Model_Tabela';
  protected $_tableName   = 'tabela_arredondamento';
  protected $_tableSchema = 'modules';

  protected $_attributeMap = array(
    'id'          => 'id',
    'instituicao' => 'instituicao_id',
    'nome'        => 'nome',
    'tipoNota'    => 'tipo_nota'
  );

  protected $_primaryKey = array(
    'id'          => 'id',
    'instituicao' => 'instituicao_id'
  );

  /**
   * @var TabelaArredondamento_Model_TabelaValorDataMapper
   */
  protected $_tabelaValorDataMapper = NULL;

  /**
   * Setter.
   * @param TabelaArredondamento_Model_TabelaValorDataMapper $mapper
   * @return CoreExt_DataMapper Provê interface fluída
   */
  public function setTabelaValorDataMapper(TabelaArredondamento_Model_TabelaValorDataMapper $mapper)
  {
    $this->_tabelaValorDataMapper = $mapper;
    return $this;
  }

  /**
   * Getter.
   * @return TabelaArredondamento_Model_TabelaValorDataMappers
   */
  public function getTabelaValorDataMapper()
  {
    if (is_null($this->_tabelaValorDataMapper)) {
      require_once 'TabelaArredondamento/Model/TabelaValorDataMapper.php';
      $this->setTabelaValorDataMapper(new TabelaArredondamento_Model_TabelaValorDataMapper());
    }
    return $this->_tabelaValorDataMapper;
  }

  /**
   * Finder para instâncias de TabelaArredondamento_Model_TabelaValor que tenham
   * referências a instância TabelaArredondamento_Model_Tabela passada como
   * parâmetro.
   *
   * @param TabelaArredondamento_Model_Tabela $instance
   * @return array Um array de instâncias TabelaArredondamento_Model_TabelaValor
   */
  public function findTabelaValor(TabelaArredondamento_Model_Tabela $instance)
  {
    $where = array(
      'tabelaArredondamento' => $instance->id
    );
    return $this->getTabelaValorDataMapper()->findAll(array(), $where);
  }
}
