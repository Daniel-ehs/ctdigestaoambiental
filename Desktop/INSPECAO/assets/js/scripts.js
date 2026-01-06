// Scripts principais do sistema
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Função para carregar locais com base no setor selecionado
    if (document.getElementById('setor_id')) {
        document.getElementById('setor_id').addEventListener('change', function() {
            var setorId = this.value;
            var localSelect = document.getElementById('local_id');
            
            if (localSelect) {
                // Limpar opções atuais
                localSelect.innerHTML = '<option value="">Selecione um local</option>';
                
                if (setorId) {
                    // Fazer requisição AJAX para obter locais do setor
                    fetch('index.php?route=api&action=getLocaisPorSetor&setor_id=' + setorId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                data.locais.forEach(function(local) {
                                    var option = document.createElement('option');
                                    option.value = local.id;
                                    option.textContent = local.nome;
                                    localSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Erro ao carregar locais:', error));
                }
            }
        });
    }

    // Preview de imagem antes do upload
    const inputFotoAntes = document.getElementById('foto_antes');
    const previewFotoAntes = document.getElementById('preview_foto_antes');
    
    if (inputFotoAntes && previewFotoAntes) {
        inputFotoAntes.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFotoAntes.src = e.target.result;
                    previewFotoAntes.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    const inputFotoDepois = document.getElementById('foto_depois');
    const previewFotoDepois = document.getElementById('preview_foto_depois');
    
    if (inputFotoDepois && previewFotoDepois) {
        inputFotoDepois.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewFotoDepois.src = e.target.result;
                    previewFotoDepois.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Inicializar DataTables se existir
    if ($.fn.DataTable && document.querySelector('.datatable')) {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
            },
            responsive: true
        });
    }

    // Inicializar datepickers
    if (document.querySelector('.datepicker')) {
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'pt-BR',
            autoclose: true
        });
    }

    // Inicializar color pickers
    if (document.querySelector('.colorpicker')) {
        $('.colorpicker').colorpicker();
    }

    // Confirmação de exclusão
    const deleteButtons = document.querySelectorAll('.btn-delete');
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja excluir este item?')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Inicializar gráficos se estiver na página de dashboard
    if (document.getElementById('graficoSetores')) {
        initDashboardCharts();
    }
});

// Função para inicializar os gráficos do dashboard
function initDashboardCharts() {
    // Gráfico de barras - Apontamentos por setor
    if (document.getElementById('graficoSetores')) {
        const ctx = document.getElementById('graficoSetores').getContext('2d');
        
        // Os dados virão do backend via variável PHP
        if (typeof dadosGraficoSetores !== 'undefined') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dadosGraficoSetores.labels,
                    datasets: [{
                        label: 'Apontamentos por Setor',
                        data: dadosGraficoSetores.data,
                        backgroundColor: '#28a745',
                        borderColor: '#218838',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Gráfico de pizza - Apontamentos por tipo
    if (document.getElementById('graficoTipos')) {
        const ctx = document.getElementById('graficoTipos').getContext('2d');
        
        // Os dados virão do backend via variável PHP
        if (typeof dadosGraficoTipos !== 'undefined') {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: dadosGraficoTipos.labels,
                    datasets: [{
                        data: dadosGraficoTipos.data,
                        backgroundColor: dadosGraficoTipos.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
}

// Função para filtrar tabelas
function filterTable() {
    const input = document.getElementById('tableSearch');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('dataTable');
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 0; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let display = false;
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    display = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = display ? '' : 'none';
    }
}


// Inicializar dropdown do usuário manualmente, se necessário
var userDropdownElement = document.getElementById('userDropdown');
if (userDropdownElement) {
    var userDropdown = new bootstrap.Dropdown(userDropdownElement);
}


