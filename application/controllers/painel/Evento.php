<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Evento extends CI_Controller {
	private $tabela ='evento';
		
	public function __construct(){
		parent::__construct();

		if(!$this->session->userdata('quiz_logado')) redirect('painel/Login');

		date_default_timezone_set('America/Sao_Paulo');
		
		$this->layout = LAYOUT_PAINEL;
		$this->load->model('/Padrao_Model', 'PadraoM');
		$this->load->model('/LogSistema_Model', 'LogM');
		$this->load->helpers('/fileuploader_helper');
	}

	public function index(){
		$data = array();
		$data['URL_NOVO'] 	= site_url('painel/Evento/novo');
		$data['URL_EXCLUIR']= site_url('painel/Evento/excluir');
		$data['RES_ERRO']	= $this->session->flashdata('reserro');
		$data['RES_OK']		= $this->session->flashdata('resok');
		$data['LIST_DADOS']	= array();
		$data['SEM_DADOS'] 	= null;
		
		//Buscando dados no Banco
		$res = $this->PadraoM->fmSearch($this->tabela, 'evenome', []);
		
		if($res){
			foreach($res as $r){
				if($r->evesituacao == 0) $situacao = "Criado";
				else if($r->evesituacao == 1) $situacao = "Iniciado";
				else $situacao = "Finalizado";
				
				$data['LIST_DADOS'][] = array(
					'id' 			=> $r->id,
					'evenome' 		=> $r->evenome,
					'evesituacao' 	=> $situacao,
					'COR_ATIVO'		=> ($r->id == $this->session->userdata('quiz_ideventoativo')) ? 'style="background-color:#d4edda;"' : null,
					'URL_EDITAR'	=> site_url('painel/Evento/edita/'.$r->id)
				);
			}
		}
		
		$this->parser->parse('painel/evento/evento-list', $data);
	}
	

	public function novo(){
		$data = array();
		$data['LABEL_ACAO'] 	= 'Novo';
		$data['URL_FRM'] 		= site_url('painel/Evento/salvar');
		$data['URL_CANCELAR']	= site_url('painel/Evento');
		$data['RES_ERRO']		= $this->session->flashdata('reserro');
		$data['RES_OK']			= $this->session->flashdata('resok');
		
		$data['id'] 		= null;
		$data['evenome'] 	= null;
		$data['evesituacao'] = null;
		$data['eveautocheck']  = '';
		$data['eveimg'] = 'assets/img/evento-banner.png';

		$this->parser->parse('painel/evento/evento-form', $data);
	}
	

	public function edita($id){
		$data = array();
		$data['LABEL_ACAO'] 	= 'Editar';
		$data['URL_FRM'] 		= site_url('painel/Evento/salvar');
		$data['URL_CANCELAR']	= site_url('painel/Evento');
		$data['RES_ERRO']		= $this->session->flashdata('reserro');
		$data['RES_OK']			= $this->session->flashdata('resok');
		
		//Buscando dados no Banco
		$res = $this->PadraoM->fmSearch($this->tabela, null, array('id' => $id), TRUE);
		
		if($res){
			foreach($res as $chave => $valor){
				$data[$chave] = $valor;
			}
			$data['eveautocheck'] = ($data['eveautocheck'] == 1) ? 'checked="checked"' : null;
		}
		else 
			show_error('Erro ao pesquisar registro para edição.', 500, 'Ops, erro encontrado.');


		$this->parser->parse('painel/evento/evento-form', $data);
	}

	public function excluir(){
		$id = $this->input->post('idexcluir');	
		$cond = array('id' => $id);	
			
		$res = $this->PadraoM->fmDelete($this->tabela, $cond);
		
		if($res){
			//--- Grava Log ---
			$log = "Exclui ". $this->tabela ." | Id: $id";
			$itens_log = array('logtexto' => $log,'idusuario' => $this->session->userdata('quiz_idusuario'));
			$res_log = $this->LogM->fmNew($itens_log);
			//--- Fim Log ---
			
			$this->session->set_flashdata('resok', fazNotificacao('success', 'Sucesso! Registro excluído.'));
		}
		else
			$this->session->set_flashdata('reserro', fazAlerta('danger', 'Erro!', 'Problemas ao excluir o registro.'));

		redirect('painel/Evento');
	}
	
	public function salvar(){
		$itens = array();

		//Inicializando variáveis com dados enviados
		foreach($this->input->post() as $chave => $valor){
			$valor = (isset($valor)) ? $valor : null;
			$$chave = $valor;
			// print $chave."[".$valor."]<br>";

			if(substr($chave, 0, 3) == 'eve')
				$itens[$chave] = $valor;
		}

		//Tratamento dos itens
		$itens['eveautocheck'] = ($this->input->post('eveautocheck')) ? $this->input->post('eveautocheck') : 0;
		$itens['atualizado_em'] = date("Y-m-d H:i:s");

		//Salvando os dados
		if($id){ //Edição
			$cond = array('id' => $id);
		
			$res_id = $this->PadraoM->fmUpdate($this->tabela, $cond, $itens);
		}
		else //Novo
			$res_id = $this->PadraoM->fmNew($this->tabela, $itens);
		
		//Se dados salvos no BD com sucesso
		if($res_id){
			if($id) { //Edição
				$this->session->set_flashdata('resok', fazNotificacao('success', 'Sucesso! Dados atualizados.'));
			} else { //Novo
				$this->session->set_flashdata('resok', fazNotificacao('success', 'Sucesso! Dados inseridos.'));
			}

			$this->criarDiretorioEvento($evenome);
			$this->salvarBannerEvento($res_id, $evenome, $id);
			
			//--- Grava Log ---
			$log = ($id) ? "Edita ".$this->tabela." | Id: ".$res_id : "Novo ". $this->tabela." | Id: ".$res_id;
			$log .= " | Valores: ";
			foreach($itens as $key => $val)
				$log .= $key."=".$val.", ";
			$itens_log = array('logtexto' => $log,'idusuario' => $this->session->userdata('quiz_idusuario'));
			$res_log = $this->LogM->fmNew($itens_log);
			//--- Fim Log ---

			//Redireciona
			redirect('painel/Evento');
		}
		//Se dados NÃO salvos com sucesso
		else{
			$this->session->set_flashdata('reserro', fazAlerta('danger', 'Erro!', 'Problemas ao realizar a operação.'));
			
			if($id) //Edição
				redirect('painel/Evento/edita/'.$id);
			else //Novo
				redirect('painel/Evento/novo');
		}
	}


	public function criarDiretorioEvento($evenome){
		$evenome_dir = retirarAcentos($evenome);
		$diretorio = 'assets/uploads/'.$evenome_dir;

		if (!is_dir($diretorio)) {
			$res_mkdir = mkdir($diretorio);

			if (!$res_mkdir)
				$this->session->set_flashdata('reserro', fazAlerta('danger', 'Erro!', 'Problemas ao realizar a operação.'));
		}
	}

	
	public function salvarBannerEvento($idevento, $evenome, $id = null){		
		if($_FILES['eveimg']['error'] == 0 && $_FILES['eveimg']['size'] > 0){
			$evenome_dir = retirarAcentos($evenome);
			$diretorio = 'assets/uploads/'.$evenome_dir.'/';

			$fileupload = new FileUploader('eveimg', ['uploadDir' => $diretorio]);
			$upload_res = $fileupload->upload();
			
			if($upload_res['isSuccess'])
				$itens['eveimg'] = $upload_res['files'][0]['file'];
		}
		else {
			if (!$id)
				$itens['eveimg'] = 'assets/img/evento-banner.png';
			else 
				return;
		}

		$cond = array('id' => $idevento);
		$res_id = $this->PadraoM->fmUpdate($this->tabela, $cond, $itens);

		if (!$res_id)
			$this->session->set_flashdata('reserro', fazAlerta('danger', 'Erro!', 'Problemas ao salvar banner.'));
	}

}
