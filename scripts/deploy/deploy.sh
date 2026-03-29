#!/bin/bash
set -e

echo "====================================="
echo "Iniciando o Deploy do CattleRFID"
echo "====================================="

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
# Navega para a pasta do script (modulo_web) para garantir que roda na raiz correta
cd "$SCRIPT_DIR/../../modulo_web"

# 1. Derrubando containers antigos e subindo novos (recria se houve mudança de imagem/Dockerfile)
echo "➜ Derrubando instâncias antigas do docker-compose..."
docker-compose down

echo "➜ Fazendo build e subindo os containers (docker-compose up -d)..."
docker-compose up -d --build

# 2. Limpeza e Aguardando inicialização
echo "➜ Limpando imagens antigas e não utilizadas (docker image prune -f)..."
docker image prune -f

echo "➜ Aguardando o Laravel inicializar (5 segundos)..."
sleep 5

# 3. Rodar os comandos essenciais do Laravel (banco de dados, cache)
echo "➜ Executando migrações do banco de dados (ignorando prompts)..."
docker exec cattlerfid_app php artisan migrate --force

echo "➜ Limpando e recriando os caches de produção (config, rotas, views)..."
docker exec cattlerfid_app php artisan optimize:clear
docker exec cattlerfid_app php artisan config:cache
docker exec cattlerfid_app php artisan event:cache
docker exec cattlerfid_app php artisan route:cache
docker exec cattlerfid_app php artisan view:cache
docker exec cattlerfid_app php artisan migrate 

# 4. Corrigindo algumas permissões do SQLite e pastas que sempre dão problema
echo "➜ Ajustando permissões essenciais..."
docker exec cattlerfid_app chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database || true

# 5. Validação (Health Check Simples)
echo "➜ Verificando a saúde da aplicação local (Acessando http://localhost)..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)

# Um status 302 indica redirecionamento para o login, 200 significa tela acessada com sucesso (ou SSL/443 para nginx se configurado HTTP -> HTTPS)
if [[ "$HTTP_STATUS" =~ ^(200|301|302)$ ]]; then
    echo "====================================="
    echo "✅ DEPLOY CONCLUÍDO COM SUCESSO!"
    echo "A aplicação está respondendo com HTTP Code: $HTTP_STATUS"
    echo "====================================="
else
    echo "====================================="
    echo "⚠️ ALERTA: DEPLOY CONCLUÍDO, MAS APLICAÇÃO PODE ESTAR FORA!"
    echo "A aplicação respondeu com HTTP Code: $HTTP_STATUS"
    echo "Exibindo os últimos logs do container Nginx:"
    docker-compose logs --tail=20 nginx
    echo "====================================="
fi
