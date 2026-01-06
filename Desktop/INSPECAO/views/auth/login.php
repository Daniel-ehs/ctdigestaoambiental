<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Inspeções de Segurança</title>
    
    <!-- Dependências de Estilo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Estilos para o novo layout -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0f2f1, #b2dfdb); /* Fundo em degradê verde claro */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            min-height: 580px;
            overflow: hidden;
            animation: fadeInAnimation 0.5s ease-in-out;
        }

        .login-form-section, .report-section {
            padding: 3rem;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* --- Seção de Login (Esquerda) --- */
        .login-form-section {
            color: #333;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo img {
            max-width: 70px;
        }
        .login-logo h2 {
            font-weight: 700;
            color: #0d6efd; /* Azul do seu design original */
            font-size: 1.7rem;
            margin-top: 1rem;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }
        
        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #333;
            border-radius: 0.5rem;
            padding: 0.8rem 1rem;
        }
        .form-control:focus {
            background-color: #ffffff;
            color: #333;
            border-color: #28a745;
            box-shadow: 0 0 0 0.1rem rgba(40, 167, 69, 0.25);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            color: #6c757d;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            padding: 0.8rem;
            border-radius: 0.5rem;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }

        /* --- Seção de Reporte (Direita) --- */
        .report-section {
            text-align: center;
            background-color: #e8f5e9; /* Fundo verde muito claro */
            border-left: 1px solid #dee2e6;
        }
        
        .report-section h3 {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #1e88e5; /* Um azul para o título do reporte */
        }
        
        .report-section p {
            color: #555;
            font-size: 1rem;
        }

        #qrcode {
            margin: 1.5rem auto;
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            width: fit-content;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .report-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        .report-link:hover {
            text-decoration: underline;
        }

        /* Animação */
        @keyframes fadeInAnimation {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Responsividade */
        @media (max-width: 991.98px) {
            body {
                height: auto;
                overflow: auto;
                padding: 1rem;
            }
            .login-container {
                flex-direction: column;
                height: auto;
                width: 100%;
                max-width: 500px;
                margin: 2rem 0;
            }
            .login-form-section, .report-section {
                width: 100%;
                padding: 2.5rem;
            }
            .report-section {
                border-left: none;
                border-top: 1px solid #dee2e6;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- SEÇÃO ESQUERDA: LOGIN -->
        <div class="login-form-section">
            <div class="login-logo">
                <img src="https://img.freepik.com/vetores-premium/um-design-de-vetor-plano-de-relatorio-de-calculo_9206-3723.jpg?w=826" alt="Logo">
                <h2 class="mt-2">Sistema de Inspeções</h2>
            </div>

            <?php if (function_exists('getFlashMessage') && $flashMessage = getFlashMessage()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="post" action="index.php?route=login">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senha" name="senha" required>
                        <span class="input-group-text" onclick="togglePassword()"><i id="eye-icon" class="fas fa-eye"></i></span>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>

        <!-- SEÇÃO DIREITA: REPORTE ANÔNIMO COM QR CODE -->
        <div class="report-section">
            <i class="fas fa-hard-hat fa-3x mb-3" style="color: #28a745;"></i>
            <h3>Reporte um Risco</h3>
            <p>Viu algo inseguro? Sua colaboração anônima é essencial para a segurança de todos. Aponte a câmera para o QR Code.</p>
            
            <div id="qrcode"></div>
            
            <p class="mt-3 small">Não consegue ler o QR Code? <br> <a href="index.php?route=reportar" class="report-link">Clique aqui para reportar.</a></p>
        </div>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        function togglePassword() {
            var senhaInput = document.getElementById("senha");
            var eyeIcon = document.getElementById("eye-icon");
            if (senhaInput.type === "password") {
                senhaInput.type = "text";
                eyeIcon.className = "fas fa-eye-slash";
            } else {
                senhaInput.type = "password";
                eyeIcon.className = "fas fa-eye";
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // URL para o QR Code
            const reportUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/index.php?route=reportar';

            // Geração do QR Code
            new QRCode(document.getElementById("qrcode"), {
                text: reportUrl,
                width: 160,
                height: 160,
                colorDark : "#218838",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
