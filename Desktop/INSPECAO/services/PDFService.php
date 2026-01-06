<?php
/**
 * Serviço para geração de PDFs
 * VERSÃO v10 - correções de footer, fundo e paginação
 */
error_log("PDFService.php: Arquivo carregado em " . date("Y-m-d H:i:s") . " - VERSÃO v11");

if (!defined("BASE_PATH")) {
    define("BASE_PATH", realpath(__DIR__ . "/../"));
}

require_once BASE_PATH . "/vendor/autoload.php";
if (!class_exists("TCPDF")) {
    require_once BASE_PATH . "/vendor/tecnickcom/tcpdf/tcpdf.php";
}

class CustomTCPDF extends TCPDF
{
    private $semana;
    private $ano;
    private $nextPageType = 'content'; // 'content' | 'cover' | 'title'
    private $pagesWithoutFooter = [];  // mapa: pageNumber => true

    public function __construct($semana, $ano)
    {
        parent::__construct("L", "mm", "A4", true, "UTF-8", false);
        $this->semana = (int) $semana;
        $this->ano = (int) $ano;
        $this->setPageFormat("A4", "L");
        $this->SetMargins(0, 0, 0, true);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);
        $this->SetAutoPageBreak(true, 15); // margem inferior reservada para footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(true);
        $this->SetCellPadding(0);
    }

    /**
     * Define o tipo da PRÓXIMA página a ser adicionada.
     * Deve ser chamado antes de AddPage().
     * Valores: 'content', 'cover', 'title'
     */
    public function setNextPageType(string $type)
    {
        $type = strtolower($type);
        if (!in_array($type, ['content', 'cover', 'title']))
            $type = 'content';
        $this->nextPageType = $type;
    }

    // compatibilidade (não obrigatórias)
    public function setCoverPage(bool $isCover)
    {
        if ($isCover)
            $this->setNextPageType('cover');
    }
    public function setTituloPage(bool $isTitulo)
    {
        if ($isTitulo)
            $this->setNextPageType('title');
    }

    // startPage é chamado sempre que uma nova página começa (inclui quebras automáticas)
    public function startPage($orientation = "", $format = "", $tocpage = false)
    {
        parent::startPage($orientation, $format, false);

        // reset margens locais (mantemos 0)
        $this->SetMargins(0, 0, 0, false);
        $this->SetHeaderMargin(0);
        $this->SetFooterMargin(0);
        $this->SetCellPadding(0);

        // Determina o tipo real desta página com base no nextPageType (após usada, zera para 'content')
        $pageType = $this->nextPageType ?? 'content';
        $this->nextPageType = 'content'; // volta ao padrão para páginas criadas automaticamente

        // Escolhe imagem de fundo (cover usa fundo1.jpg, demais usam fundo.jpg)
        $backgroundImage = ($pageType === 'cover') ? BASE_PATH . "/assets/images/fundo1.jpg" : BASE_PATH . "/assets/images/fundo.jpg";
        if (file_exists($backgroundImage)) {
            try {
                // fundo full-bleed (cobre 100% da página, sem margens)
                $pageW = $this->getPageWidth();
                $pageH = $this->getPageHeight();

                $this->SetAutoPageBreak(false, 0);
                $this->setPrintHeader(false);
                $this->setPrintFooter(false);
                $this->SetMargins(0, 0, 0);
                $this->SetXY(0, 0);

                $this->Image(
                    $backgroundImage,
                    0,
                    0,                   // canto superior esquerdo
                    $pageW,
                    $pageH,         // largura e altura totais
                    '',
                    '',
                    '',
                    false,
                    300, // sem link, 300 DPI
                    '',
                    false,
                    false,
                    0,
                    false,
                    false
                );

                $this->setPageMark(); // garante que o conteúdo venha por cima
                $this->SetAutoPageBreak(true, 15);
                $this->setPrintFooter(true);
            } catch (Exception $e) {
                error_log("CustomTCPDF->startPage: erro imagem fundo: " . $e->getMessage());
            }
        }

        // Se for página de capa/título, garante que não haja footer naquela página específica
        if ($pageType === 'cover' || $pageType === 'title') {
            $this->pagesWithoutFooter[$this->getPage()] = true;
        }
    }

    public function Header()
    { /* intencionalmente vazio */
    }

    public function Footer()
    {
        // se essa página estiver marcada para não mostrar footer, sai
        if (!empty($this->pagesWithoutFooter[$this->getPage()])) {
            return;
        }

        $this->SetY(-15);
        $this->SetFont("helvetica", "I", 8);
        $this->SetTextColor(100, 100, 100);

        try {
            $startDate = new DateTime();
            $startDate->setISODate($this->ano, $this->semana, 1); // segunda
            $endDate = new DateTime();
            $endDate->setISODate($this->ano, $this->semana, 7);   // domingo
            $reportText = "Relatório de Inspeções de EHS - Semana {$this->semana} ({$startDate->format('d/m/Y')} a {$endDate->format('d/m/Y')})";
        } catch (Exception $e) {
            $reportText = "Relatório de Inspeções de EHS - Semana {$this->semana}";
        }

        // Left text with 15mm margin
        $this->SetX(15);
        // Reserve a largura do lado direito para a paginação
        $rightWidth = 50; // largura do campo de paginação (ajuste se quiser mais/menos)
        $leftWidth = $this->getPageWidth() - 15 - $rightWidth - 5;
        if ($leftWidth < 20)
            $leftWidth = 20;
        $this->Cell($leftWidth, 10, $reportText, 0, 0, 'L');

        // Paginação mais para dentro (SetX negativo posiciona relativo à borda direita)
        $this->SetX(-($rightWidth + 10)); // espaço adicional da borda direita
        $this->Cell($rightWidth, 10, "Página " . $this->getAliasNumPage() . " de " . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

use Aws\S3\S3Client;

class PDFService
{
    private $pdf;
    private $s3Client;

    public function __construct()
    {
        // As credenciais são lidas das variáveis de ambiente que configurou no CapRover
        try {
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'region' => getenv('S3_REGION'),
                'endpoint' => getenv('S3_ENDPOINT'),
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => getenv('S3_KEY'),
                    'secret' => getenv('S3_SECRET'),
                ],
            ]);
        } catch (Exception $e) {
            // Se falhar, regista o erro mas não impede a app de carregar
            error_log("Erro ao inicializar o cliente S3 no PDFService: " . $e->getMessage());
            $this->s3Client = null;
        }
    }

    private function resolveImage($pathOrUrl)
    {
        if (empty($pathOrUrl))
            return false;

        // 1. Verificar arquivo local (caminho absoluto ou relativo)
        if (file_exists($pathOrUrl)) {
            return ['path' => $pathOrUrl, 'is_temp' => false];
        }
        $localPath = BASE_PATH . '/' . ltrim($pathOrUrl, '/');
        if (file_exists($localPath)) {
            return ['path' => $localPath, 'is_temp' => false];
        }

        // 2. Se for URL e S3 estiver configurado, tentar baixar
        if ($this->s3Client && strpos($pathOrUrl, 'http') === 0) {
            try {
                // Tenta usar S3_BUCKET (padrão) ou S3_BUCKET_RELATORIOS como fallback
                $bucket = getenv("S3_BUCKET") ?: getenv("S3_BUCKET_RELATORIOS");
                if (!$bucket)
                    return false;

                $key = ltrim(parse_url($pathOrUrl, PHP_URL_PATH), "/");
                // Remove prefixo do bucket se existir na chave
                if (strpos($key, $bucket . "/") === 0) {
                    $key = substr($key, strlen($bucket) + 1);
                }

                $result = $this->s3Client->getObject(["Bucket" => $bucket, "Key" => $key]);
                $tempFilePath = tempnam(sys_get_temp_dir(), "s3_img_");
                file_put_contents($tempFilePath, $result["Body"]);
                return ['path' => $tempFilePath, 'is_temp' => true];
            } catch (Exception $e) {
                error_log("PDFService->resolveImage: Erro ao baixar imagem S3 ($pathOrUrl): " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    private function ensurePdfDirIsReady()
    {
        if (!defined("PDFS_DIR"))
            define("PDFS_DIR", BASE_PATH . '/uploads/pdfs');
        if (!is_dir(PDFS_DIR)) {
            if (!mkdir(PDFS_DIR, 0775, true)) {
                error_log("PDFService: Falha ao criar diretório " . PDFS_DIR);
                return false;
            }
        }
        return is_writable(PDFS_DIR);
    }

    public function gerarRelatorioSemanal($inspecoes, $semana, $ano, $incluirFotos = false, $returnContent = false)
    {
        if (empty($inspecoes))
            throw new Exception("Lista de inspeções vazia.");
        if (!$this->ensurePdfDirIsReady())
            throw new Exception("Falha ao criar ou acessar diretório de PDFs.");

        try {
            $this->pdf = new CustomTCPDF($semana, $ano);
            $this->pdf->SetCreator("Sistema de Inspeções de Segurança");
            $this->pdf->SetAuthor("CTDI");
            $this->pdf->SetTitle("Relatório Semanal - Semana " . $semana . "/" . $ano);

            // Agrupa por setor|local
            $inspecoesAgrupadas = [];
            foreach ($inspecoes as $inspecao) {
                $chave = ($inspecao["setor_nome"] ?? "N/A") . "|" . ($inspecao["local_nome"] ?? "N/A");
                $inspecoesAgrupadas[$chave][] = $inspecao;
            }

            foreach ($inspecoesAgrupadas as $chave => $inspecoesGrupo) {
                list($setorNome, $localNome) = explode("|", $chave);

                // --- CAPA do grupo (sem rodapé) ---
                $this->pdf->setNextPageType('cover'); // marca a próxima página como cover (sem footer)
                $this->pdf->AddPage();
                $this->pdf->SetY(95);
                $this->pdf->SetFont("helvetica", "B", 35);
                $this->pdf->SetTextColor(0, 86, 150);
                $this->pdf->Cell(0, 15, htmlspecialchars($setorNome), 0, 1, "C");
                $this->pdf->SetFont("helvetica", "", 24);
                $this->pdf->SetTextColor(100, 100, 100);
                $this->pdf->Cell(0, 15, htmlspecialchars($localNome), 0, 1, "C");

                // --- PÁGINAS DE APONTAMENTO (conteúdo com rodapé) ---
                foreach ($inspecoesGrupo as $inspecao) {
                    $this->pdf->setNextPageType('content'); // garante que a página criada seja de conteúdo
                    $this->pdf->AddPage();

                    $imgData = false;
                    if ($incluirFotos && !empty($inspecao["foto_antes"])) {
                        $imgData = $this->resolveImage($inspecao["foto_antes"]);
                    }

                    $yStart = 55;
                    $xImage = 28.8;
                    $imageWidth = 92;
                    $imageHeight = 116.5;

                    if ($imgData && file_exists($imgData['path'])) {
                        $this->pdf->Image($imgData['path'], $xImage, $yStart, $imageWidth, $imageHeight);
                        if ($imgData['is_temp']) {
                            @unlink($imgData['path']);
                        }
                    } else {
                        $semFotoPath = BASE_PATH . '/assets/images/sem-foto.png';
                        if (file_exists($semFotoPath)) {
                            $this->pdf->Image($semFotoPath, $xImage, $yStart, $imageWidth, $imageHeight, 'PNG');
                        }
                    }

                    $xDados = 170;
                    $wDados = 112;
                    $this->pdf->SetY($yStart - 5);

                    // APONTAMENTO
                    $this->pdf->SetFont('helvetica', 'B', 12);
                    $this->pdf->SetTextColor(0, 86, 150);
                    $this->pdf->SetX($xDados);
                    $this->pdf->Cell($wDados, 8, 'Apontamento #' . ($inspecao['numero_inspecao'] ?? 'N/A') . ':', 0, 1, 'L');
                    $this->pdf->SetFont('helvetica', '', 10);
                    $this->pdf->SetTextColor(80, 80, 80);
                    $this->pdf->SetX($xDados);
                    $this->pdf->MultiCell($wDados, 5, htmlspecialchars($inspecao['apontamento'] ?? 'Não informado'), 0, 'L');
                    $this->pdf->Ln(18);

                    // RISCO/CONSEQUÊNCIA
                    $this->pdf->SetFont('helvetica', 'B', 12);
                    $this->pdf->SetTextColor(0, 86, 150);
                    $this->pdf->SetX($xDados);
                    $this->pdf->Cell($wDados, 8, 'Risco/Consequência:', 0, 1, 'L');

                    $this->pdf->SetFont('helvetica', '', 10);
                    $tipoNome = htmlspecialchars($inspecao['tipo_nome'] ?? 'N/A');
                    $colorRGB = '80,80,80';
                    switch ($tipoNome) {
                        case 'Oportunidade de Melhoria':
                            $colorRGB = '34,197,94';
                            break;
                        case 'Apontamento':
                            $colorRGB = '249,115,22';
                            break;
                        case 'Falta de Uso de EPI':
                            $colorRGB = '168,85,247';
                            break;
                        case 'Risco Potencial':
                            $colorRGB = '220,38,38';
                            break;
                        case 'Uso correto dos EPIs':
                            $colorRGB = '59,130,246';
                            break;
                    }
                    $riscoTexto = '<span style="color:rgb(' . $colorRGB . '); font-weight: bold;">' . $tipoNome . ': </span><span style="color:rgb(80,80,80);">' . htmlspecialchars($inspecao['risco_consequencia'] ?? 'Não informado') . '</span>';
                    $this->pdf->SetX($xDados);
                    $this->pdf->writeHTMLCell($wDados, 5, $xDados, $this->pdf->GetY(), $riscoTexto, 0, 1, false, true, 'L');
                    $this->pdf->Ln(18);

                    // RESOLUÇÃO/AÇÃO TOMADA
                    $this->pdf->SetFont('helvetica', 'B', 12);
                    $this->pdf->SetTextColor(0, 86, 150);
                    $this->pdf->SetX($xDados);
                    $this->pdf->Cell($wDados, 8, 'Resolução/Ação Tomada:', 0, 1, 'L');
                    $this->pdf->SetFont('helvetica', '', 10);
                    $this->pdf->SetTextColor(80, 80, 80);
                    $this->pdf->SetX($xDados);
                    $this->pdf->MultiCell($wDados, 5, htmlspecialchars($inspecao['resolucao_proposta'] ?? 'Não informado'), 0, 'L');
                    $this->pdf->Ln(18);

                    // RESPONSÁVEL/PRAZO
                    $this->pdf->SetFont('helvetica', 'B', 12);
                    $this->pdf->SetTextColor(0, 86, 150);
                    $this->pdf->SetX($xDados);
                    $this->pdf->Cell($wDados, 8, 'Responsável/Prazo:', 0, 1, 'L');
                    $this->pdf->SetFont('helvetica', '', 10);
                    $this->pdf->SetTextColor(80, 80, 80);
                    $this->pdf->SetX($xDados);
                    $responsavelPrazo = (htmlspecialchars($inspecao['responsavel'] ?? 'N/A')) . ' - ' . $this->formatDateSafe($inspecao['prazo'] ?? 'N/A');
                    $this->pdf->MultiCell($wDados, 5, $responsavelPrazo, 0, 'L');

                    // STATUS (colocar mais para cima para não "cair" na próxima página)
                    $status = trim($inspecao["status"] ?? "");
                    if ($status === "Concluído") {
                        $statusImagePath = BASE_PATH . "/assets/images/Resolvido.png";
                        $imgWidth = 65;
                        $imgHeight = 15; // proporção 4.23:1
                    } else {
                        $statusImagePath = BASE_PATH . "/assets/images/Resolver.png";
                        $imgWidth = 65;
                        $imgHeight = 17; // proporção 3.78:1
                    }

                    if (file_exists($statusImagePath)) {
                        $this->pdf->Image($statusImagePath, $xDados, 177, $imgWidth, $imgHeight, 'PNG');
                    }
                } // foreach inspecoesGrupo
            } // foreach grupos

            $nomeArquivo = 'relatorio_semanal_' . $semana . '_' . $ano . '_' . date('YmdHis') . '.pdf';
            $outputFile = PDFS_DIR . '/' . $nomeArquivo;

            if ($returnContent) {
                return $this->pdf->Output("" . rand() . ".pdf", "S"); // Retorna o PDF como string
            } else {
                $this->pdf->Output($outputFile, "F");
                return $outputFile;
            }
        } catch (Exception $e) {
            error_log("PDFService->gerarRelatorioSemanal: Erro na geração do PDF: " . $e->getMessage());
            throw $e;
        }
    }
    private function formatDateSafe($dateString)
    {
        if (empty($dateString))
            return 'N/A';
        try {
            return (new DateTime($dateString))->format('d/m/Y');
        } catch (Exception $e) {
            return $dateString;
        }
    }
}
?>