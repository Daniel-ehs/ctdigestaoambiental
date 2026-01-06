#!/bin/sh
# Para o script se qualquer comando falhar
set -e

# Define o caminho do diretório de uploads
UPLOADS_DIR="/var/www/html/uploads"

# --- INÍCIO DA NOVA LÓGICA DE DEBUG E CORREÇÃO ---

# 1. Mostra as permissões ATUAIS do volume, como o CapRover o montou.
echo "--> Verificando permissões iniciais do volume..."
ls -ld $UPLOADS_DIR

# 2. Garante que os subdiretórios essenciais existam.
echo "--> Garantindo a existência dos subdiretórios..."
mkdir -p "$UPLOADS_DIR/fotos_antes"
mkdir -p "$UPLOADS_DIR/fotos_depois"
mkdir -p "$UPLOADS_DIR/pdfs"

# 3. MUDANÇA PRINCIPAL: Em vez de apenas chown, usamos chmod 777.
# Isto dá permissão de leitura, escrita e execução para TODOS (dono, grupo e outros).
# É a forma mais direta de garantir que o www-data possa escrever.
echo "--> Aplicando permissões de escrita para todos (chmod 777)..."
chmod -R 777 "$UPLOADS_DIR"

# 4. Tenta novamente o chown, por segurança. Não causa mal se falhar.
echo "--> Tentando definir www-data como proprietário (chown)..."
chown -R www-data:www-data "$UPLOADS_DIR" || echo "Aviso: chown falhou, mas chmod 777 deve ser suficiente."

# 5. Mostra as permissões FINAIS para confirmarmos a alteração.
echo "--> Verificando permissões finais do volume..."
ls -ld $UPLOADS_DIR
ls -l $UPLOADS_DIR

echo "--> Script de inicialização concluído. A iniciar o Apache..."
# --- FIM DA NOVA LÓGICA ---

# Inicia o servidor Apache em primeiro plano.
exec apache2-foreground

