<?php

require_once __DIR__ . 
'/../models/Playlist.php';
require_once __DIR__ . 
'/../models/Tela.php';

class PlaylistController {
    private $playlistModel;
    private $telaModel;
    
    public function __construct() {
        $this->playlistModel = new Playlist();
        $this->telaModel = new Tela();
    }
    
    public function create($nome, $telaId, $dataInicio, $dataFim, $midias = []) {
        try {
            // Log para debug
            error_log("PlaylistController->create() chamado");
            error_log("Parâmetros: nome=$nome, telaId=$telaId, dataInicio=$dataInicio, dataFim=$dataFim");
            error_log("Mídias recebidas: " . json_encode($midias));
            
            // Validações básicas
            if (empty($nome)) {
                return [
                    'success' => false,
                    'message' => 'Nome da playlist é obrigatório.'
                ];
            }
            
            if (empty($telaId)) {
                return [
                    'success' => false,
                    'message' => 'Tela é obrigatória.'
                ];
            }
            
            // Verifica se a tela existe
            $tela = $this->telaModel->getById($telaId);
            if (!$tela) {
                return [
                    'success' => false,
                    'message' => 'Tela não encontrada.'
                ];
            }
            
            // Cria a playlist primeiro
            error_log("Criando playlist no banco...");
            $playlistId = $this->playlistModel->create($nome, $telaId, $dataInicio, $dataFim, 2); // user_id = 2
            
            if (!$playlistId) {
                error_log("ERRO: Falha ao criar playlist no banco");
                return [
                    'success' => false,
                    'message' => 'Erro ao criar playlist.'
                ];
            }
            
            error_log("Playlist criada com ID: $playlistId");
            
            // Processa as mídias se fornecidas
            if (!empty($midias)) {
                error_log("Processando " . count($midias) . " mídias...");
                
                $midiasSalvas = 0;
                
                foreach ($midias as $midiaData) {
                    $midiaId = intval($midiaData['id']);
                    $ordem = intval($midiaData['ordem']);
                    $tempoExibicao = intval($midiaData['tempo_exibicao']);
                    
                    if ($midiaId > 0) {
                        error_log("Adicionando mídia ID: $midiaId, ordem: $ordem, tempo: $tempoExibicao");
                        
                        $resultado = $this->playlistModel->addMidia($playlistId, $midiaId, $ordem, $tempoExibicao);
                        
                        if ($resultado) {
                            $midiasSalvas++;
                            error_log("Mídia $midiaId adicionada com sucesso");
                        } else {
                            error_log("ERRO: Falha ao adicionar mídia $midiaId");
                        }
                    } else {
                        error_log("AVISO: Mídia ID inválido: " . ($midiaData['id'] ?? ''));
                    }
                }
                
                error_log("Total de mídias salvas: $midiasSalvas");
                
                if ($midiasSalvas == 0) {
                    error_log("AVISO: Nenhuma mídia foi salva");
                }
            } else {
                error_log("AVISO: Nenhuma mídia fornecida");
            }
            
            return [
                'success' => true,
                'message' => 'Playlist criada com sucesso.',
                'playlist_id' => $playlistId
            ];
            
        } catch (Exception $e) {
            error_log("ERRO FATAL em PlaylistController->create(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function update($id, $nome, $dataInicio, $dataFim, $midias = null) {
        try {
            // Verifica se a playlist existe
            $playlist = $this->playlistModel->getById($id);
            if (!$playlist) {
                return [
                    'success' => false,
                    'message' => 'Playlist não encontrada.'
                ];
            }
            
            // Atualiza a playlist
            if (!$this->playlistModel->update($id, $nome, $dataInicio, $dataFim)) {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar playlist.'
                ];
            }
            
            // Atualiza as mídias se fornecidas
            if ($midias !== null) {
                // Limpa mídias existentes
                $this->playlistModel->clearMidias($id);
                
                // Adiciona novas mídias
                if (!empty($midias)) {
                    foreach ($midias as $midiaData) {
                        $midiaId = intval($midiaData['id']);
                        $ordem = intval($midiaData['ordem']);
                        $tempoExibicao = intval($midiaData['tempo_exibicao']);
                        
                        if ($midiaId > 0) {
                            $this->playlistModel->addMidia($id, $midiaId, $ordem, $tempoExibicao);
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => 'Playlist atualizada com sucesso.'
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao atualizar playlist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function delete($id) {
        try {
            // Verifica se a playlist existe
            $playlist = $this->playlistModel->getById($id);
            if (!$playlist) {
                return [
                    'success' => false,
                    'message' => 'Playlist não encontrada.'
                ];
            }
            
            // Deleta a playlist
            if ($this->playlistModel->delete($id)) {
                return [
                    'success' => true,
                    'message' => 'Playlist deletada com sucesso.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao deletar playlist.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao deletar playlist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
    
    public function getById($id) {
        try {
            $playlist = $this->playlistModel->getById($id);
            
            if ($playlist) {
                // Busca as mídias da playlist
                $midias = $this->playlistModel->getMidias($id);
                $playlist['midias'] = $midias;
                
                return [
                    'success' => true,
                    'playlist' => $playlist
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Playlist não encontrada.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erro ao buscar playlist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar playlist.'
            ];
        }
    }
    
    public function getAll($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            $playlists = $this->playlistModel->getAll(null, $limit, $offset);
            $totalPlaylists = $this->playlistModel->getCount(); // Obtém o total de playlists
            
            return [
                'success' => true,
                'playlists' => $playlists,
                'page' => $page,
                'limit' => $limit,
                'total' => $totalPlaylists // Adiciona o total de playlists aqui
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao listar playlists: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar playlists.',
                'playlists' => [],
                'total' => 0
            ];
        }
    }
    
    public function getMidias($playlistId) {
        try {
            $midias = $this->playlistModel->getMidias($playlistId);
            return [
                'success' => true,
                'midias' => $midias
            ];
        } catch (Exception $e) {
            error_log("Erro ao buscar mídias da playlist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao carregar mídias da playlist.',
                'midias' => []
            ];
        }
    }

    public function clone($playlistId, $novoNome = null, $novaDataInicio = null, $novaDataFim = null) {
        try {
            // Verifica se a playlist original existe
            $playlistOriginal = $this->playlistModel->getById($playlistId);
            if (!$playlistOriginal) {
                return [
                    'success' => false,
                    'message' => 'Playlist original não encontrada.'
                ];
            }
            
            // Define nome automático se não fornecido
            if (!$novoNome) {
                $novoNome = "Cópia de " . $playlistOriginal['nome'];
            }
            
            // Define datas automáticas se não fornecidas
            if (!$novaDataInicio || !$novaDataFim) {
                $agora = new DateTime();
                $novaDataInicio = $agora->format('Y-m-d H:i:s');
                $agora->add(new DateInterval('PT1H')); // Adiciona 1 hora
                $novaDataFim = $agora->format('Y-m-d H:i:s');
            }
            
            // Cria a nova playlist
            $novaPlaylistId = $this->playlistModel->create(
                $novoNome,
                $playlistOriginal['tela_id'],
                $novaDataInicio,
                $novaDataFim,
                2 // user_id
            );
            
            if (!$novaPlaylistId) {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar nova playlist.'
                ];
            }
            
            // Copia as mídias
            $midiasOriginais = $this->playlistModel->getMidias($playlistId);
            foreach ($midiasOriginais as $midia) {
                $this->playlistModel->addMidia(
                    $novaPlaylistId,
                    $midia['midia_id'],
                    $midia['ordem'],
                    $midia['tempo_exibicao']
                );
            }
            
            return [
                'success' => true,
                'message' => 'Playlist clonada com sucesso.',
                'playlist_id' => $novaPlaylistId
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao clonar playlist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ];
        }
    }
}

?>

