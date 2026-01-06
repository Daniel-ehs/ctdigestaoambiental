<?php
/**
 * Controlador para requisições de API
 * VERSÃO ATUALIZADA: Utiliza cURL para maior robustez na chamada da API.
 */

class ApiController {
    private $setorModel;
    private $localModel;
    private $inspecaoModel;
    private $empresaModel;
    
    public function __construct() {
        // Assegura que os caminhos para os modelos estão corretos
        require_once 'models/Setor.php';
        require_once 'models/Local.php';
        require_once 'models/Inspecao.php';
        require_once 'models/Empresa.php';
        
        $this->setorModel = new Setor();
        $this->localModel = new Local();
        $this->inspecaoModel = new Inspecao();
        $this->empresaModel = new Empresa();
    }

    /**
     * Obtém setores filtrados por empresa.
     */
    public function getSetoresPorEmpresa($empresaId) {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autenticado'], 401);
            return;
        }

        try {
            // true = apenas ativos, $empresaId = filtro
            $setores = $this->setorModel->listar(true, $empresaId); 
            $this->jsonResponse(['success' => true, 'setores' => $setores]);

        } catch (Throwable $e) {
            error_log("Erro em ApiController->getSetoresPorEmpresa: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao buscar setores.'], 500);
        }
    }
    
    /**
     * Obtém locais filtrados por setor (e opcionalmente empresa).
     */
    public function getLocaisPorSetor($setorId) {
        if (empty($setorId) || !is_numeric($setorId)) {
            $this->jsonResponse(['success' => true, 'locais' => []]);
            return;
        }
        
        try {
            $empresaId = isset($_GET['empresa_id']) && !empty($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;
            // true = apenas ativos
            $locais = $this->localModel->listarPorSetor($setorId, true, $empresaId);
            $this->jsonResponse(['success' => true, 'locais' => $locais]);
        } catch (Throwable $e) {
            error_log("Erro em ApiController->getLocaisPorSetor: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao buscar locais.'], 500);
        }
    }
    
    /**
     * Obtém estatísticas gerais das inspeções.
     */
    public function getEstatisticas() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Não autenticado'], 401);
            return;
        }
        
        try {
            $empresaId = isset($_GET['empresa_id']) ? intval($_GET['empresa_id']) : null;
            $usuarioEmpresaId = isset($_SESSION['user_empresa_id']) ? $_SESSION['user_empresa_id'] : null;
            $usuarioNivel = $_SESSION['user_nivel'] ?? '';
            
            // Se o usuário não for admin e pertencer a uma empresa, filtra automaticamente
            if ($usuarioNivel !== 'admin' && $usuarioEmpresaId) {
                $empresaId = $usuarioEmpresaId;
            }
            
            $estatisticas = $this->inspecaoModel->obterEstatisticas($empresaId);
            $this->jsonResponse(['success' => true, 'estatisticas' => $estatisticas]);
        } catch (Throwable $e) {
            error_log("Erro em ApiController->getEstatisticas: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao buscar estatísticas.'], 500);
        }
    }
    
    /**
     * Ponto de entrada da API para correção de texto via IA.
     */
    public function correctText() {
        if (!isset($_SESSION["user_id"])) {
            $this->jsonResponse(["success" => false, "message" => "Não autenticado"], 401);
            return;
        }

        $text = $_POST["text"] ?? '';
        $context = $_POST['context'] ?? 'inspecao';
        $field = $_POST['field'] ?? 'geral'; 
        $allContext = $_POST['all_context'] ?? []; // Contexto dos campos anteriores
        
        // Garante que $allContext seja sempre um array
        if (!is_array($allContext)) {
            $allContext = [];
        }

        if (!$text) {
            $this->jsonResponse(["success" => false, "message" => "Texto não fornecido"], 400);
            return;
        }

        // Gera o prompt com base no contexto e campo
        $prompt = $this->getPromptForContext($context, $text, $field, $allContext);
        $apiKey = getenv('GEMINI_API_KEY');

        if (empty($apiKey)) {
            error_log("A variável de ambiente GEMINI_API_KEY não foi encontrada ou está vazia.");
            $this->jsonResponse(["success" => false, "message" => "A chave da API não está configurada no servidor."], 500);
            return;
        }

        // URL da API Gemini (usando gemini-2.5-flash)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . urlencode($apiKey);
        
        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]]
        ];

        // Inicializa o cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Manter true em produção
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Timeout de 60 segundos

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $curl_error = curl_error($ch);
            curl_close($ch);
            error_log("Erro de cURL ao chamar a API Gemini: " . $curl_error);
            $this->jsonResponse(["success" => false, "message" => "Erro de comunicação com a API. Detalhe: " . $curl_error], 500);
            return;
        }
        
        curl_close($ch);

        if ($http_code >= 400) {
            error_log("Erro HTTP da API Gemini: " . $http_code . " - Resposta: " . $response);
            $this->jsonResponse(["success" => false, "message" => "A API retornou um erro (HTTP " . $http_code . "). Verifique os logs."], 500);
            return;
        }

        $result = json_decode($response, true);
        
        // Extrai o texto da resposta da API
        if (isset($result["candidates"][0]["content"]["parts"][0]["text"])) {
            $this->jsonResponse(["success" => true, "corrected" => trim($result["candidates"][0]["content"]["parts"][0]["text"])]);
        } else {
            error_log('Resposta inválida da API Gemini: ' . print_r($result, true));
            $this->jsonResponse(["success" => false, "message" => "Resposta inválida da API do Gemini. Verifique os logs."], 500);
        }
    }

    /**
     * Gera o prompt para a API Gemini com base no contexto e no campo.
     * ESTA É A SEÇÃO ATUALIZADA COM PROMPTS FLEXÍVEIS.
     */
    private function getPromptForContext($context, $text, $field, $allContext = []) {
        if ($context === 'inspecao') {
            switch ($field) {
                
                // PROMPT 'APONTAMENTO' ATUALIZADO
                case 'apontamento':
                    return <<<EOD
Você é um especialista em Segurança do Trabalho, responsável por revisar e melhorar os apontamentos de segurança.
Sua tarefa é reescrever o "apontamento" ou "situação encontrada" de forma técnica, clara e objetiva.

IMPORTANTE: O apontamento pode ser POSITIVO (um treinamento, uma boa prática, um elogio) ou NEGATIVO (uma não conformidade, um risco, algo quebrado).

- Corrija erros gramaticais, de ortografia e de concordância.
- Se for negativo (ex: "sem oculus"), use termos técnicos (ex: "sem utilização de Óculos de Segurança (EPI)").
- Se for positivo (ex: "dei treinamento"), reescreva de forma profissional (ex: "Realizado treinamento da CIPA sobre...").
- Foque em descrever a situação ou ação de forma coesa.
- Entregue **apenas** o texto revisado, sem comentários ou saudações.

Texto original: "{$text}"
EOD;

                // PROMPT 'RISCO_CONSEQUENCIA' ATUALIZADO
                case 'risco_consequencia':
                    $apontamentoCorrigido = $allContext['apontamento'] ?? 'Contexto não fornecido.';
                    return <<<EOD
Você é um especialista em análise de riscos de Segurança do Trabalho.
Sua tarefa é descrever o **impacto**, **risco** ou **resultado** do apontamento, com base no contexto.

- Se o apontamento for NEGATIVO (um risco), descreva o **perigo** (Ex: "Risco de lesão ocular...").
- Se o apontamento for POSITIVO (um treinamento, uma ação boa), descreva o **resultado positivo** ou o **objetivo alcançado** (Ex: "Equipe ciente dos procedimentos.", "Todas as dúvidas foram sanadas.").
- O texto DEVE ser coerente com o Apontamento.
- Seja sucinto e direto (máximo de 20 palavras).
- Entregue **apenas** o texto revisado, sem comentários ou saudações.

Apontamento (Contexto): "{$apontamentoCorrigido}"
Texto original do Risco/Impacto: "{$text}"
EOD;

                // PROMPT 'RESOLUCAO_PROPOSTA' ATUALIZADO
                case 'resolucao_proposta':
                    $apontamentoCorrigido = $allContext['apontamento'] ?? 'Contexto não fornecido.';
                    $riscoCorrigido = $allContext['risco_consequencia'] ?? 'Contexto não fornecido.';
                    return <<<EOD
Você é um especialista em planos de ação para Segurança do Trabalho.
Sua tarefa é reescrever a "resolução", "ação tomada" ou "próximo passo".

- Se o apontamento foi NEGATIVO, descreva a **ação corretiva** (Ex: "Foi instalado o guarda-corpo...").
- Se o apontamento foi POSITIVO ou um treinamento, descreva a **ação de conclusão** (Ex: "Treinamento concluído.", "Dúvidas sanadas.", "Nenhuma ação adicional necessária.").
- O texto DEVE ser coerente com o Apontamento e o Risco/Impacto.
- Entregue **apenas** o texto revisado, sem comentários ou saudações.

Apontamento (Contexto): "{$apontamentoCorrigido}"
Risco/Impacto (Contexto): "{$riscoCorrigido}"
Texto original da Resolução: "{$text}"
EOD;
                
                // PROMPT 'OBSERVACAO' ATUALIZADO (E CORRIGIDO PARA NÃO REPETIR)
                case 'observacao':
                    $apontamentoCorrigido = $allContext['apontamento'] ?? 'Contexto não fornecido.';
                    $riscoCorrigido = $allContext['risco_consequencia'] ?? 'Contexto não fornecido.';
                    $resolucaoCorrigida = $allContext['resolucao_proposta'] ?? 'Contexto não fornecido.';
                    return <<<EOD
Você é um especialista em Segurança do Trabalho revisando um relatório. Use o contexto abaixo para entender a situação (que pode ser positiva ou negativa), mas **NÃO O REPITA** na sua resposta.

**Contexto (Apenas para sua informação):**
- Apontamento: "{$apontamentoCorrigido}"
- Risco/Impacto: "{$riscoCorrigido}"
- Resolução/Conclusão: "{$resolucaoCorrigida}"

**Sua Tarefa:**
Corrija e melhore o seguinte texto de "Observação" para que ele seja claro, profissional e coerente com o contexto.
Retorne **APENAS** o texto da observação corrigido, sem introduções ou saudações.

**Texto original da Observação:**
"{$text}"
EOD;

                default:
                    // Prompt genérico para qualquer outro campo não mapeado
                    return <<<EOD
Você é um revisor de textos profissional. Sua tarefa é corrigir a gramática, ortografia e pontuação do texto a seguir, melhorando a clareza e a fluidez sem alterar o sentido original.
Entregue **apenas** o texto revisado, sem comentários, introduções ou saudações.

Texto original: "{$text}"
EOD;
            }
        }

        // Prompt genérico para outros contextos (como 'projeto')
        return <<<EOD
Você é um revisor de textos profissional. Sua tarefa é corrigir a gramática, ortografia e pontuação do texto a seguir, melhorando a clareza e a fluidez sem alterar o sentido original.
Entregue **apenas** o texto revisado, sem comentários, introduções ou saudações.

Texto original: "{$text}"
EOD;
    }

    /**
     * Resposta padrão em JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
