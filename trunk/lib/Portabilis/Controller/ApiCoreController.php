<?php

#error_reporting(E_ALL);
#ini_set("display_errors", 1);

/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas D'Avila <lucasdavila@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Portabilis
 * @subpackage  lib
 * @since   Arquivo disponível desde a versão ?
 * @version   $Id$
 */

require_once 'include/clsBanco.inc.php';

require_once 'Core/Controller/Page/EditController.php';
require_once 'CoreExt/Exception.php';

require_once 'lib/Portabilis/Messenger.php';
require_once 'lib/Portabilis/Validator.php';
require_once 'lib/Portabilis/DataMapper/Utils.php';
require_once 'lib/Portabilis/Array/Utils.php';
require_once 'lib/Portabilis/Utils/Database.php';
require_once 'lib/Portabilis/String/Utils.php';

class ApiCoreController extends Core_Controller_Page_EditController
{
  protected $_dataMapper  = ''; #Avaliacao_Model_NotaComponenteDataMapper';
  protected $_processoAp  = 0;
  protected $_nivelAcessoOption = App_Model_NivelAcesso::SOMENTE_ESCOLA;
  protected $_saveOption  = FALSE;
  protected $_deleteOption  = FALSE;
  protected $_titulo   = '';


  public function __construct() {
    $this->messenger = new Messenger();
    $this->validator = new Validator($this->messenger);
    $this->response = array();
    $this->db = new clsBanco();
  }


  protected function validatesUserIsLoggedIn(){
    return $this->validator->validatesPresenceOf($this->getSession()->id_pessoa, '', false, 'Usuário deve estar logado');
  }


  protected function canAcceptRequest() {
    return $this->validatesUserIsLoggedIn() &&
           $this->validatesPresenceOf(array('oper', 'resource'));
  }


  protected function notImplementedOperationError() {
    $this->messenger->append("Operação '{$this->getRequest()->oper}' não implementada para o recurso '{$this->getRequest()->resource}'");
  }


  protected function appendResponse($name, $value = '') {
    if (is_array($name)) {
      foreach($name as $k => $v) {
        $this->response[$k] = $v;
      }
    }
    elseif (! is_null($name))
      $this->response[$name] = $value;
  }


  // subscrever nas classes filhas sentando os recursos disponibilizados e operacoes permitidas, ex:
  // return array('resources1' => array('get'), 'resouce2'    => array('post', 'delete'));
  protected function getAvailableOperationsForResources() {
    throw new CoreExt_Exception('É necessário sobrescrever o método "getExpectedOperationsForResources()" de ApiCoreController.');
  }


  protected function isRequestFor($oper, $resource) {
    return $this->getRequest()->resource == $resource &&
           $this->getRequest()->oper == $oper;
  }


  protected function prepareResponse(){
    try {
      if (isset($this->getRequest()->oper))
        $this->appendResponse('oper', $this->getRequest()->oper);

      if (isset($this->getRequest()->resource))
        $this->appendResponse('resource', $this->getRequest()->resource);

      $this->appendResponse('msgs', $this->messenger->getMsgs());
      $this->appendResponse('any_error_msg', $this->messenger->hasMsgWithType('error'));

      $response = json_encode($this->response);
    }
    catch (Exception $e){
      error_log("Erro inesperado no metodo prepareResponse da classe ApiCoreController: {$e->getMessage()}");
      $response = array('msgs' => array('msg' => 'Erro inesperado no servidor. Por favor, tente novamente.',
                        'type' => 'error'));

      $response = json_encode($response);
    }

    echo $response;
  }


  public function generate(CoreExt_Controller_Page_Interface $instance){
    header('Content-type: application/json; charset=UTF-8');

    try {
      if ($this->canAcceptRequest())
        $instance->Gerar();
    }
    catch (Exception $e){
      $this->messenger->append('Exception: ' . $e->getMessage(), 'error', $encodeToUtf8 = true);
    }

    echo $this->prepareResponse();
  }


  /*  subescrever nas classes filhas setando as verificações desejadas e retornando a resposta,
      conforme recurso e operação recebida ex: get, post ou delete, ex:

      if ($this->getRequest()->oper == 'get')
      {
        // caso retorne apenas um recurso
        $this->appendResponse('matriculas', $matriculas);

        // ou para multiplos recursos, pode-se usar o argumento resource
        if ($this->getRequest()->resource == 'matriculas')
          $this->appendResponse('matriculas', $this->getMatriculas());
        elseif ($this->getRequest()->resource == 'alunos')
          $this->appendResponse('alunos', $this->getAlunos);
        else
          $this->notImplementedError();
      }
      elseif ($this->getRequest()->oper == 'post')
        $this->postMatricula();
      else
        $this->notImplementedError();
  */
  public function Gerar(){
    throw new CoreExt_Exception('É necessário sobrescrever o método "ApiCoreController()" de ApiCoreController.');
  }


  // #TODO mover validadores para classe lib/Portabilis/Validator.php / adicionar wrapper para tais

  protected function validatesPresenceOf($requiredParamNames) {
    if (! is_array($requiredParamNames))
      $requiredParamNames = array($requiredParamNames);

      $valid = true;

      foreach($requiredParamNames as $param) {
        if (! $this->validator->validatesPresenceOf($this->getRequest()->$param, $param) and $valid) {
          $valid = false;
        }
      }

    return $valid;
  }


  protected function validatesExistenceOf($resourceName, $value, $options = array()) {
    $defaultOptions = array('schema_name'      => 'pmieducar',
                            'field_name'       => "cod_{$resourceName}",
                            'add_msg_on_error' => true);

    $options        = $this->mergeOptions($options, $defaultOptions);

    return $this->validator->validatesValueIsInBd($options['field_name'],
                                                  $value,
                                                  $options['schema_name'],
                                                  $resourceName,
                                                  $raiseExceptionOnFail = false,
                                                  $addMsgOnError        = $options['add_msg_on_error']);
  }

  protected function validatesUniquenessOf($resourceName, $value, $options = array()) {
    $defaultOptions = array('schema_name'      => 'pmieducar',
                            'field_name'       => "cod_{$resourceName}",
                            'add_msg_on_error' => true);

    $options        = $this->mergeOptions($options, $defaultOptions);

    return $this->validator->validatesValueNotInBd($options['field_name'],
                                                   $value,
                                                   $options['schema_name'],
                                                   $resourceName,
                                                   $raiseExceptionOnFail = false,
                                                   $addMsgOnError        = $options['add_msg_on_error']);
  }


  protected function validatesIsNumeric($expectedNumericParamNames) {
    if (! is_array($expectedNumericParamNames))
      $expectedNumericParamNames = array($expectedNumericParamNames);

      $valid = true;

      foreach($requiredParamNames as $param) {
        if (! $this->validator->validatesValueIsNumeric($this->getRequest()->$param, $param) and $valid) {
          $valid = false;
        }
      }

    return $valid;
  }


  // wrappers for Portabilis_*Utils*


  // DEPRECADO #TODO nas classes filhas, migrar chamadas de fetchPreparedQuery para usar novo padrao com array de options
  protected function fetchPreparedQuery($sql, $params = array(), $hideExceptions = true, $returnOnly = '') {
    $options = array('params'      => $params,
                     'show_errors' => ! $hideExceptions,
                     'return_only' => $returnOnly,
                     'messenger'   => $this->messenger);

    return Portabilis_Utils_Database::fetchPreparedQuery($sql, $options);
  }

  protected function getDataMapperFor($packageName, $modelName){
    return Portabilis_DataMapper_Utils::getDataMapperFor($packageName, $modelName);
  }

  protected static function mergeOptions($options, $defaultOptions) {
    return Portabilis_Array_Utils::merge($options, $defaultOptions);
  }

  protected function toUtf8($str, $options = array()) {
    return Portabilis_String_Utils::toUtf8($str, $options);
  }

  protected function toLatin1($str, $options = array()) {
    return Portabilis_String_Utils::toLatin1($str, $options);
  }

  // DEPRECADO #TODO nas classe filhas migrar de safeString => toUtf8
  protected function safeString($str, $transform = true) {
    return $this->toUtf8($str, array('transform' => $transform));
  }

  // DEPRECADO #TODO nas classe filhas migrar de safeStringForDb => toLatin1
  protected function safeStringForDb($str) {
    return $this->toLatin1($str);
  }
}
