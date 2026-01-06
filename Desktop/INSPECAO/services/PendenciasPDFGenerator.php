<?php
/**
 * PendenciasReportGenerator
 * Gera um relatório em formato de planilha (compatível com Excel) para as ações pendentes.
 * Esta versão não depende de bibliotecas externas para garantir a máxima compatibilidade.
 */

class PendenciasPDFGenerator { // O nome da classe foi mantido para não quebrar a chamada no seu Controller

    /**
     * Gera e força o download de um ficheiro .xls com os dados das inspeções.
     *
     * @param array $inspecoes Array com os dados das inspeções pendentes.
     * @param int $empresaId ID da empresa (não utilizado, mas mantido por consistência).
     */
    public function gerar(array $inspecoes, int $empresaId) {
        try {
            // 1. ORDENAÇÃO: Garante que os dados estejam em ordem crescente pelo número da inspeção.
            usort($inspecoes, function($a, $b) {
                return (int)($a['numero_inspecao'] ?? 0) <=> (int)($b['numero_inspecao'] ?? 0);
            });

            // 2. DEFINE OS CABEÇALHOS HTTP para forçar o download de um ficheiro Excel.
            $filename = "acoes_pendentes_" . date('Y-m-d') . ".xls";
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            
            // 3. INICIA A CRIAÇÃO DO HTML que será interpretado pelo Excel.
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta charset="utf-8">';
            
            // --- Bloco XML para configurar o Excel ---
            echo '<!--[if gte mso 9]><xml>';
            echo '<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
            echo '<x:Name>Acoes Pendentes</x:Name>';
            echo '<x:WorksheetOptions>';
            echo '<x:DisplayGridlines/>';
            echo '<x:FreezePanes/><x:SplitHorizontal>1</x:SplitHorizontal><x:TopRowBottomPane>1</x:TopRowBottomPane>'; // Congela a primeira linha (cabeçalho)
            echo '</x:WorksheetOptions>';
            echo '</x:ExcelWorksheet></x:ExcelWorksheets>';
            
            // AJUSTE: Define um "Named Range" que o Excel usa para o AutoFilter.
            $rowCount = count($inspecoes) + 1;
            $range = "'Acoes Pendentes'!\$A\$1:\$F\${$rowCount}";
            echo '<x:DefinedName Name="_FilterDatabase" LocalSheetId="0" RefersTo="=' . $range . '"></x:DefinedName>';

            echo '</x:ExcelWorkbook></xml><![endif]-->';

            // --- Estilos CSS para formatação da tabela ---
            echo '<style>';
            echo 'table { border-collapse: collapse; font-family: Arial, sans-serif; }';
            echo 'td { border: 1px solid #cccccc; padding: 8px; text-align: left; vertical-align: middle; }';
            echo 'th { background-color: #004E8F; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #004E8F; }';
            echo '.even { background-color: #f2f2f2; }'; // Estilo para linhas pares
            echo '.odd { background-color: #ffffff; }';  // Estilo para linhas ímpares
            echo '</style>';
            
            echo '</head>';
            echo '<body>';
            
            // 4. CRIA A TABELA com um nome que o Excel pode referenciar.
            echo '<table>';

            // 5. CABEÇALHO DA TABELA: Cria a primeira linha com os títulos.
            echo '<thead>';
            echo '<tr>';
            echo '<th>Nº</th>';
            echo '<th>Apontamento</th>';
            echo '<th>Setor</th>';
            echo '<th>Local</th>';
            echo '<th>Status</th>';
            echo '<th>Prazo</th>';
            echo '</tr>';
            echo '</thead>';
            
            // 6. CORPO DA TABELA: Itera sobre os dados e cria as linhas da planilha.
            echo '<tbody>';
            foreach ($inspecoes as $index => $inspecao) {
                // Define a classe CSS para o efeito "zebra"
                $rowClass = ($index % 2 == 0) ? 'even' : 'odd';
                echo "<tr class='{$rowClass}'>";
                
                // Converte os dados para evitar problemas de formatação no Excel.
                $num = htmlspecialchars($inspecao['numero_inspecao'] ?? 'N/A');
                $apontamento = htmlspecialchars($inspecao['apontamento'] ?? 'N/A');
                $setor = htmlspecialchars($inspecao['setor_nome'] ?? 'N/A');
                $local = htmlspecialchars($inspecao['local_nome'] ?? 'N/A');
                $status = htmlspecialchars($inspecao['status'] ?? 'N/A');
                $prazo = !empty($inspecao['prazo']) ? htmlspecialchars(date('d/m/Y', strtotime($inspecao['prazo']))) : 'N/A';

                echo "<td>{$num}</td>";
                echo "<td>{$apontamento}</td>";
                echo "<td>{$setor}</td>";
                echo "<td>{$local}</td>";
                echo "<td>{$status}</td>";
                // Adiciona o atributo mso-number-format para garantir que o Excel trate a data como texto.
                echo "<td style='mso-number-format:\"\\@\"'>{$prazo}</td>";
                echo '</tr>';
            }
            echo '</tbody>';

            // 7. FINALIZA A ESTRUTURA HTML.
            echo '</table>';
            
            echo '</body>';
            echo '</html>';
            
            // 8. Encerra o script para garantir que nada mais seja enviado.
            exit;

        } catch (Exception $e) {
            error_log("ERRO FATAL em PendenciasReportGenerator: " . $e->getMessage() . " no arquivo " . $e->getFile() . " na linha " . $e->getLine());
            die("Não foi possível gerar o relatório. O erro foi registrado e o administrador foi notificado.");
        }
    }
}
