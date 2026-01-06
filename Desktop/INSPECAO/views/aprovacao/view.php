<?php include 'views/templates/header.php'; ?>

<style>
    /* Pode copiar os estilos de 'create.php' para manter a consistência */
    .card-body { padding: 2.5rem; }
    .form-section { margin-bottom: 2rem; }
    .img-fluid { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
</style>

<div class="top-bar">
    <h1 class="h2 m-0">Revisar Apontamento</h1>
    <a href="index.php?route=aprovacao" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Detalhes do Apontamento Recebido</h6>
    </div>
    <div class="card-body">
        <?php displayFlashMessage(); ?>

        <div class="form-section bg-light">
            <h5>Informações Originais</h5>
            <div class="row">
                <div class="col-md-8">
                    <p><strong>Empresa:</strong> <?php echo htmlspecialchars($apontamento['empresa_nome']); ?></p>
                    <p><strong>Setor:</strong> <?php echo htmlspecialchars($apontamento['setor_nome']); ?></p>
                    <p><strong>Local:</strong> <?php echo htmlspecialchars($apontamento['local_nome']); ?></p>
                    <p><strong>Descrição Original:</strong></p>
                    <blockquote class="blockquote">
                        <p><?php echo nl2br(htmlspecialchars($apontamento['apontamento'])); ?></p>
                    </blockquote>
                    <?php if($apontamento['contato_nome']): ?>
                        <p><strong>Contato:</strong> <?php echo htmlspecialchars($apontamento['contato_nome'] . ' (' . $apontamento['contato_info'] . ')'); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <?php if ($apontamento['foto_apontamento']): ?>
                        <p><strong>Foto Anexada:</strong></p>
                        <a href="<?php echo UPLOADS_DIR . '/fotos_pendentes/' . $apontamento['foto_apontamento']; ?>" target="_blank">
                            <img src="<?php echo UPLOADS_DIR . '/fotos_pendentes/' . $apontamento['foto_apontamento']; ?>" class="img-fluid" alt="Foto do Apontamento">
                        </a>
                    <?php else: ?>
                        <p>Nenhuma foto foi anexada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form method="post" action="index.php?route=aprovacao&action=aprovar&id=<?php echo $apontamento['id']; ?>">
            <div class="form-section">
                <h5>Completar e Registrar como Inspeção</h5>
                <p>Preencha os campos abaixo para registrar este apontamento como uma inspeção oficial. Os dados originais já foram preenchidos.</p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="apontamento" class="form-label required">Apontamento (Revisado)</label>
                        <textarea class="form-control" id="apontamento" name="apontamento" rows="3" required><?php echo htmlspecialchars($apontamento['apontamento']); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo_id" class="form-label required">Tipo de Apontamento</label>
                        <select class="form-select" id="tipo_id" name="tipo_id" required>
                            <option value="">Selecione um tipo</option>
                             <?php foreach ($tipos as $tipo): ?>
                                <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="risco_consequencia" class="form-label">Risco/Consequência</label>
                        <textarea class="form-control" id="risco_consequencia" name="risco_consequencia" rows="2"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="resolucao_proposta" class="form-label">Resolução Proposta</label>
                        <textarea class="form-control" id="resolucao_proposta" name="resolucao_proposta" rows="2"></textarea>
                    </div>
                </div>
                
                 <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="responsavel" class="form-label">Responsável</label>
                        <input type="text" class="form-control" id="responsavel" name="responsavel">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="prazo" class="form-label">Prazo</label>
                        <input type="date" class="form-control" id="prazo" name="prazo">
                    </div>
                     <div class="col-md-4 mb-3">
                        <label for="data_apontamento" class="form-label">Data do Apontamento</label>
                        <input type="date" class="form-control" id="data_apontamento" name="data_apontamento" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="observacao" class="form-label">Observações Adicionais</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?route=aprovacao&action=rejeitar&id=<?php echo $apontamento['id']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja rejeitar este apontamento?');">
                    <i class="fas fa-times"></i> Rejeitar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Aprovar e Registrar Inspeção
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>