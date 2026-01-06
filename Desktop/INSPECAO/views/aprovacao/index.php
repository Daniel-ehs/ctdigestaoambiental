<?php
// views/aprovacao/index.php (Atualizado)

// Inclui o cabeçalho do seu template
include 'views/templates/header.php'; 
?>

<div class="container-fluid">

    <!-- Cabeçalho da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Aprovações Pendentes</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Apontamentos Recebidos para Validação</h6>
        </div>
        <div class="card-body">
            <?php // Se houver uma função para exibir mensagens flash, ela pode ser chamada aqui. ?>
            
            <?php
            // LÓGICA PRINCIPAL ATUALIZADA:
            // Verificamos se a variável $pendentes (que vem do seu controller) está vazia.
            if (empty($pendentes)):
            ?>
                <!-- Se estiver vazio, mostramos a nova mensagem estilizada -->
                <div class="text-center p-5">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; color: #4e73df;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                    </div>
                    <h4 class="font-weight-bold text-gray-800">Tudo certo por aqui!</h4>
                    <p class="text-gray-600 mt-2">
                        Não há nenhum apontamento pendente de aprovação no momento.
                    </p>
                </div>

            <?php
            else:
            ?>
                <!-- Se não estiver vazio, mostramos a tabela com os dados -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Empresa</th>
                                <th>Setor/Local</th>
                                <th>Apontamento</th>
                                <th>Contato</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendentes as $item): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($item['data_criacao'])); // Supondo que você tenha uma função formatDate, senão usamos a padrão ?></td>
                                    <td><?php echo htmlspecialchars($item['empresa_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($item['setor_nome'] . ' / ' . $item['local_nome']); ?></td>
                                    <td title="<?php echo htmlspecialchars($item['apontamento']); ?>">
                                        <?php echo htmlspecialchars(substr($item['apontamento'], 0, 50)) . (strlen($item['apontamento']) > 50 ? '...' : ''); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['contato_nome'])): ?>
                                            <span title="<?php echo htmlspecialchars($item['contato_info']); ?>">
                                                <?php echo htmlspecialchars($item['contato_nome']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span>Anônimo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-column text-center">
                                        <a href="index.php?route=aprovacao&action=view&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-success" title="Revisar e Aprovar">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="index.php?route=aprovacao&action=rejeitar&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger btn-delete-pendente" title="Rejeitar" onclick="return confirm('Tem certeza que deseja rejeitar este apontamento?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php
            endif;
            ?>
        </div>
    </div>
</div>

<?php 
// Inclui o rodapé do seu template
include 'views/templates/footer.php'; 
?>
