<?php
/**
 * Serviço para geração de PDFs de Planos de Ação
 * VERSÃO ATUALIZADA PARA USAR TABELA GENÉRICA 'relatorios'
 * CORRIGIDO: Query SQL ajustada para o schema do banco de dados fornecido (inspecaodb.sql).
 */
error_log("PlanoPDFService.php: Arquivo carregado em " . date('Y-m-d H:i:s') . " - VERSÃO COM AJUSTES DE LAYOUT");

if (!class_exists('TCPDF')) {
    error_log("PlanoPDFService: Incluindo TCPDF de " . BASE_PATH . '/vendor/tecnickcom/tcpdf/tcpdf.php');
    require_once BASE_PATH . '/vendor/tecnickcom/tcpdf/tcpdf.php';
}

/**
 * Classe personalizada que estende TCPDF para fundo estilizado
 */
class CustomTCPDF extends TCPDF {
    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding);
        $this->SetMargins(0, 0, 0, true);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);
        $this->SetAutoPageBreak(false, 0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetCellPadding(0);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        error_log("CustomTCPDF->construct: Inicializado para formato A4 horizontal");
    }

    public function startPage($orientation = '', $format = '', $tocpage = false) {
        parent::startPage($orientation, $format, false);
        $this->SetMargins(0, 0, 0, false);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);
        $this->SetCellPadding(0);
        
        $backgroundImage = BASE_PATH . '/assets/images/Plano.png';
        error_log("CustomTCPDF->startPage: Selecionada imagem de fundo: {$backgroundImage}");

        if (file_exists($backgroundImage) && is_readable($backgroundImage)) {
            try {
                list($imgWidth, $imgHeight) = @getimagesize($backgroundImage) ?: [0, 0];
                error_log("CustomTCPDF->startPage: Imagem {$backgroundImage} encontrada, dimensões de {$imgWidth}x{$imgHeight}px");
                $this->Image($backgroundImage, 0, 0, 297, 210, '', '', 'C', true, 300, 'C', false, false, false);
                error_log("CustomTCPDF->startPage: Imagem de fundo {$backgroundImage} esticada para 297mm x 210mm");
            } catch (Exception $e) {
                error_log("CustomTCPDF->startPage: Erro ao carregar imagem de fundo {$backgroundImage}: " . $e->getMessage());
            }
        } else {
            error_log("CustomTCPDF->startPage: Imagem {$backgroundImage} não encontrada ou não legível");
        }
    }
}

class PlanoPDFService {
    private $pdf;
    private $s3Client; // Cliente S3 para baixar imagens do MinIO
    private $db; // Conexão com banco de dados

    public function __construct() {
        // Inicializar cliente S3 para baixar imagens do MinIO
        require_once BASE_PATH . '/vendor/autoload.php';
        try {
            $this->s3Client = new Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => getenv('S3_REGION'),
                'endpoint' => getenv('S3_ENDPOINT'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => getenv('S3_KEY'),
                    'secret' => getenv('S3_SECRET'),
                ],
            ]);
            error_log("PlanoPDFService: Cliente S3 inicializado com sucesso");
        } catch (Exception $e) {
            error_log("PlanoPDFService: Erro ao inicializar cliente S3: " . $e->getMessage());
            $this->s3Client = null;
        }

        // Inicializar conexão com banco de dados
        try {
            $this->db = Database::getInstance()->getConnection();
            error_log("PlanoPDFService: Conexão com banco de dados estabelecida");
        } catch (Exception $e) {
            error_log("PlanoPDFService: Erro ao conectar ao banco de dados: " . $e->getMessage());
            $this->db = null;
        }
    }

    private function downloadImageFromS3($s3Url) {
        if (!$this->s3Client) {
            error_log("PlanoPDFService->downloadImageFromS3: Cliente S3 não disponível");
            return null;
        }
        try {
            $parsedUrl = parse_url($s3Url);
            $path = ltrim($parsedUrl['path'] ?? '', '/');
            $pathParts = explode('/', $path, 2);
            if (count($pathParts) < 2) {
                error_log("PlanoPDFService->downloadImageFromS3: URL inválida: $s3Url");
                return null;
            }
            $bucket = $pathParts[0];
            $key = $pathParts[1];
            $result = $this->s3Client->getObject(['Bucket' => $bucket, 'Key' => $key]);
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_image_');
            file_put_contents($tempFile, $result['Body']);
            return $tempFile;
        } catch (Exception $e) {
            error_log("PlanoPDFService->downloadImageFromS3: Erro ao baixar imagem: " . $e->getMessage());
            return null;
        }
    }

    private function uploadPDFToS3($localPath, $fileName) {
        if (!$this->s3Client) {
            error_log("PlanoPDFService->uploadPDFToS3: Cliente S3 não disponível");
            return null;
        }
        try {
            $bucket = getenv('S3_BUCKET_RELATORIOS') ?: getenv('S3_BUCKET');
            $key = 'relatorios-pdf/' . $fileName;
            $result = $this->s3Client->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'SourceFile' => $localPath,
                'ACL'    => 'public-read',
                'ContentType' => 'application/pdf'
            ]);
            return $result['ObjectURL'];
        } catch (Exception $e) {
            error_log("PlanoPDFService->uploadPDFToS3: Erro ao fazer upload: " . $e->getMessage());
            return null;
        }
    }

    private function salvarRegistroRelatorio($tipoRelatorio, $referenciaId, $nomeArquivo, $urlMinio, $tamanhoBytes, $usuarioId, $observacao = null) {
        if (!$this->db) {
            error_log("PlanoPDFService->salvarRegistroRelatorio: Conexão com banco não disponível");
            return null;
        }
        try {
            $sql = "INSERT INTO relatorios (tipo_relatorio, referencia_id, nome_arquivo, url_minio, tamanho_bytes, usuario_id, observacao) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tipoRelatorio, $referenciaId, $nomeArquivo, $urlMinio, $tamanhoBytes, $usuarioId, $observacao]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("PlanoPDFService->salvarRegistroRelatorio: Erro ao salvar: " . $e->getMessage());
            return null;
        }
    }

    private function isS3Url($url) {
        if (empty($url)) return false;
        $s3Endpoint = getenv('S3_ENDPOINT');
        if (empty($s3Endpoint)) return false;
        $s3Host = parse_url($s3Endpoint, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        return $s3Host === $urlHost;
    }

    private function getLocalImagePath($imageUrl, $localDir = null) {
        if (empty($imageUrl)) return null;
        if ($this->isS3Url($imageUrl)) {
            return $this->downloadImageFromS3($imageUrl);
        }
        if ($localDir) {
            $localPath = $localDir . '/' . basename($imageUrl);
            if (file_exists($localPath) && is_readable($localPath)) {
                return $localPath;
            }
        }
        return null;
    }

    private function ensurePdfDirIsReady() {
        if (!defined('PDFS_DIR')) return false;
        if (!is_dir(PDFS_DIR)) {
            if (!mkdir(PDFS_DIR, 0775, true)) return false;
        }
        if (!is_writable(PDFS_DIR)) {
            if (!chmod(PDFS_DIR, 0775)) return false;
        }
        return true;
    }

    public function gerarPlanoPDF($plano) {
        error_log("PlanoPDFService->gerarPlanoPDF: Iniciando para plano ID " . ($plano['id'] ?? 'null'));
        if (!$plano || !isset($plano['id'])) {
            error_log("PlanoPDFService->gerarPlanoPDF: Dados do plano inválidos ou ID não fornecido.");
            return null;
        }

        if ($this->db) {
            try {
                $sql = "SELECT 
                            p.foto_depois,
                            i.apontamento,
                            i.numero_inspecao,
                            i.semana_ano,
                            i.foto_antes AS inspecao_foto_antes,
                            e.nome AS empresa_nome,
                            s.nome AS setor,
                            l.nome AS local
                        FROM planos_acao p
                        LEFT JOIN inspecoes i ON p.inspecao_id = i.id
                        LEFT JOIN empresas e ON i.empresa_id = e.id
                        LEFT JOIN setores s ON i.setor_id = s.id
                        LEFT JOIN locais l ON i.local_id = l.id
                        WHERE p.id = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$plano['id']]);
                $dadosAdicionais = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($dadosAdicionais) {
                    $plano = array_merge($plano, $dadosAdicionais);
                    error_log("PlanoPDFService->gerarPlanoPDF: Dados do plano enriquecidos com sucesso via DB.");
                } else {
                    error_log("PlanoPDFService->gerarPlanoPDF: Nenhum dado adicional encontrado no DB para o plano ID " . $plano['id']);
                }

            } catch (Exception $e) {
                error_log("PlanoPDFService->gerarPlanoPDF: ERRO na query SQL para buscar dados: " . $e->getMessage());
            }
        } else {
            error_log("PlanoPDFService->gerarPlanoPDF: Conexão com o banco não disponível para enriquecer dados.");
        }
        
        error_log("PlanoPDFService->gerarPlanoPDF: Dados FINAIS para o PDF: " . print_r($plano, true));
        
        if (!$this->ensurePdfDirIsReady()) return null;
        
        $this->pdf = new CustomTCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        $this->pdf->SetCreator('Sistema de Inspeções de Segurança');
        $this->pdf->SetAuthor('Sistema de Ação');
        $this->pdf->SetTitle('Plano de Ação - #' . $plano['id']);
        $this->pdf->SetSubject('Plano de Ação');
        $this->pdf->SetKeywords('Plano de Ação, Inspeção, Segurança');
        
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(false, 0);
        $this->pdf->SetCellPadding(0);
        
        $this->pdf->AddPage();
        
        // Dados no topo
        $this->pdf->SetXY(23.5, 35); // EDITADO: Aumentei de 30 para 35 para descer o texto.
        $this->pdf->SetFont('Helvetica', '', 10);
        $this->pdf->writeHTMLCell(50, 7, '', '', '<b style="color:#00008B">FILIAL:</b> ' . htmlspecialchars($plano['empresa_nome'] ?? 'N/A'), 0, 0, false, true, 'L');
        $this->pdf->writeHTMLCell(45, 7, '', '', '<b style="color:#00008B">ITEM:</b> ' . htmlspecialchars($plano['numero_inspecao'] ?? 'N/A'), 0, 0, false, true, 'L');
        $this->pdf->writeHTMLCell(50, 7, '', '', '<b style="color:#00008B">SEMANA:</b> ' . htmlspecialchars($plano['semana_ano'] ?? 'N/A'), 0, 0, false, true, 'L');
        $this->pdf->writeHTMLCell(53, 7, '', '', '<b style="color:#00008B">SETOR:</b> ' . htmlspecialchars($plano['setor'] ?? 'N/A'), 0, 0, false, true, 'L');
        $this->pdf->writeHTMLCell(52, 7, '', '', '<b style="color:#00008B">LOCAL:</b> ' . htmlspecialchars($plano['local'] ?? 'N/A'), 0, 1, false, true, 'L');
        
        $this->pdf->writeHTMLCell(250, 0, 23.5, '', '<b style="color:#00008B">APONTAMENTO:</b> ' . htmlspecialchars($plano['apontamento'] ?? 'Não informado'), 0, 1, false, true, 'L');
        
        $this->pdf->Ln(10);
        
        // Obter caminhos locais das imagens (baixando do S3 se necessário)
        $fotoAntesPath = null;
        $fotoDepoisPath = null;
        $tempFiles = [];

        if (!empty($plano['inspecao_foto_antes'])) {
            $fotoAntesPath = $this->getLocalImagePath($plano['inspecao_foto_antes'], defined('FOTOS_ANTES_DIR') ? FOTOS_ANTES_DIR : null);
            if ($fotoAntesPath && $this->isS3Url($plano['inspecao_foto_antes'])) {
                $tempFiles[] = $fotoAntesPath;
            }
        }

        if (!empty($plano['foto_depois'])) {
            $fotoDepoisPath = $this->getLocalImagePath($plano['foto_depois'], defined('FOTOS_DEPOIS_DIR') ? FOTOS_DEPOIS_DIR : null);
            if ($fotoDepoisPath && $this->isS3Url($plano['foto_depois'])) {
                $tempFiles[] = $fotoDepoisPath;
            }
        }
        
        $temFotoAntes = $fotoAntesPath && file_exists($fotoAntesPath) && is_readable($fotoAntesPath);
        $temFotoDepois = $fotoDepoisPath && file_exists($fotoDepoisPath) && is_readable($fotoDepoisPath);
        
        if ($temFotoAntes || $temFotoDepois) {
            $imageWidthAntes = 76.5; // EDITADO: Aumentei a largura da caixa da imagem de 70 para 85.
            $imageHeightAntes = 105.9; // EDITADO: Aumentei a altura da caixa da imagem de 97 para 115.
            $imageWidthDepois = 76.5; // EDITADO: Aumentei a largura da caixa da imagem de 70 para 85.
            $imageHeightDepois = 105.9; // EDITADO: Aumentei a altura da caixa da imagem de 97 para 115.
            $x1 = 34; // EDITADO: Diminuí o valor de 38.1 para 28.1 para mover a imagem da esquerda
            $x2 = 184; // EDITADO: Diminuí o valor de 188 para 178 para mover a imagem da direita.
            $y = $this->pdf->GetY();
            $yAntes = $y + 24; // EDITADO: Aumentei de 29 para 35 para descer a imagem.
            $yDepois = $y + 24; // EDITADO: Aumentei de 29 para 35 para descer a imagem.
            $maxImageHeight = 0;

            if ($temFotoAntes) {
                try {
                    // EDITADO: Mudei o penúltimo parâmetro de 'false' para 'true' para manter a proporção da imagem.
                    $this->pdf->Image($fotoAntesPath, $x1, $yAntes, $imageWidthAntes, $imageHeightAntes, '', '', 'T', true, 300);
                    $maxImageHeight = max($maxImageHeight, $imageHeightAntes + 20);
                } catch (Exception $e) {
                    error_log("PlanoPDFService->gerarPlanoPDF: Erro ao carregar imagem Antes: " . $e->getMessage());
                }
            }
            
            if ($temFotoDepois) {
                try {
                    // EDITADO: Mudei o penúltimo parâmetro de 'false' para 'true' para manter a proporção da imagem.
                    $this->pdf->Image($fotoDepoisPath, $x2, $yDepois, $imageWidthDepois, $imageHeightDepois, '', '', 'T', true, 300);
                    $maxImageHeight = max($maxImageHeight, $imageHeightDepois + 20);
                } catch (Exception $e) {
                    error_log("PlanoPDFService->gerarPlanoPDF: Erro ao carregar imagem Depois: " . $e->getMessage());
                }
            }
            $this->pdf->SetY($y + $maxImageHeight + 10);
        } else {
            $this->pdf->SetY($this->pdf->GetY() + 40);
            $this->pdf->SetFont('Helvetica', 'I', 10);
            $this->pdf->SetTextColor(128);
            $this->pdf->Cell(0, 10, 'Nenhuma imagem para exibir.', 0, 1, 'C');
        }
        
        // Rodapé
        $this->pdf->SetY(-13);
        $this->pdf->SetFont('Helvetica', 'I', 8);
        $this->pdf->SetTextColor(107, 114, 128);
        $this->pdf->SetX(15);
        $this->pdf->Cell(0, 10, 'Relatório Semanal de Inspeção de EHS - Plano de Ação #' . $plano['id'], 0, 0, 'L');
        
        $nomeArquivo = 'plano_acao_' . $plano['id'] . '_' . date('YmdHis') . '.pdf';
        $caminhoArquivo = PDFS_DIR . '/' . $nomeArquivo;
        
        try {
            $this->pdf->Output($caminhoArquivo, 'F');
            $tamanhoBytes = filesize($caminhoArquivo);
            $urlMinio = $this->uploadPDFToS3($caminhoArquivo, $nomeArquivo);
            
            if ($urlMinio) {
                $usuarioId = $_SESSION['user_id'] ?? 1;
                $this->salvarRegistroRelatorio('plano_acao', $plano['id'], $nomeArquivo, $urlMinio, $tamanhoBytes, $usuarioId, 'Relatório de plano de ação gerado automaticamente');
            }
            
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) unlink($tempFile);
            }
            return $caminhoArquivo;
        } catch (Exception $e) {
            error_log("PlanoPDFService->gerarPlanoPDF: Erro ao salvar PDF: " . $e->getMessage());
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) unlink($tempFile);
            }
            return null;
        }
    }
}
?>
