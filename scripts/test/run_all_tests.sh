#!/bin/bash

# Mudar para a raiz do repositório
cd "$(dirname "${BASH_SOURCE[0]}")/../.."

# Default values
MODE="local"
KEEP_DOCKER=false
RUN_INTEGRATION=true
RUN_E2E=false

# Simple argument parsing
for arg in "$@"; do
    case $arg in
        --docker)
            MODE="docker"
            ;;
        --local)
            MODE="local"
            ;;
        --keep-docker)
            KEEP_DOCKER=true
            ;;
        --no-cache)
            NO_CACHE=true
            ;;
        --integration)
            RUN_INTEGRATION=true
            RUN_E2E=false
            ;;
        --e2e)
            RUN_E2E=true
            RUN_INTEGRATION=false
            ;;
        --all)
            RUN_INTEGRATION=true
            RUN_E2E=true
            ;;
        *)
            echo "Uso: ./run_all_tests.sh [--local | --docker] [--keep-docker] [--no-cache] [--integration | --e2e | --all]"
            echo "  --local       : Roda usando 'php artisan serve' (HTTP, porta 8555)"
            echo "  --docker      : Roda usando 'docker compose' (HTTPS, porta 443 / porta mapeada)"
            echo "  --keep-docker : Mantém o docker rodando após os testes (apenas modo --docker)"
            echo "  --no-cache    : Limpa imagens e volumes do docker antes de iniciar (apenas modo --docker)"
            echo "  --integration : Roda apenas integração (PHP Feature + Java) - Padrão"
            echo "  --e2e         : Roda apenas testes End-to-End (Playwright)"
            echo "  --all         : Roda tudo"
            exit 1
            ;;
    esac
done

echo "🚀 Starting test suite in $MODE mode..."
echo "=================================================="

# --- 1. PHP FEATURE TESTS (Always on Host) ---
if [ "$RUN_INTEGRATION" = true ]; then
    echo "1. Running PHP web module tests (Host)..."
    echo "=================================================="
    cd modulo_web || exit 1
    php artisan test
    PHP_TEST_RESULT=$?
    cd ..

    if [ $PHP_TEST_RESULT -ne 0 ]; then
      echo "❌ PHP tests failed! Aborting."
      exit 1
    fi
fi

# --- 2. SHARED ENVIRONMENT PREPARATION ---
if [ "$RUN_INTEGRATION" = true ] || [ "$RUN_E2E" = true ]; then
    echo ""
    echo "=================================================="
    echo "2. Preparing database and services..."
    echo "=================================================="
    cd modulo_web || exit 1
    
    # Ensure database directory and file exist
    mkdir -p database
    touch database/database.sqlite

    if [ "$MODE" == "docker" ]; then
        if [ "$NO_CACHE" = true ]; then
        echo "Limpando cache do Docker (imagens, volumes, orphans)..."
            docker compose down -v --rmi all --remove-orphans
        fi
        docker compose up -d
        echo "Aguardando serviços do Docker subirem..."
        
    # Check if we can communicate with the container/service
        MAX_RETRIES=10
        COUNT=0
    echo -n "Checking Docker services health"
        while [ $COUNT -lt $MAX_RETRIES ]; do
            if docker compose exec -T laravel php artisan --version > /dev/null 2>&1; then
                echo " ✅ Docker is UP"
                break
            fi
            echo -n "."
            sleep 2
            COUNT=$((COUNT + 1))
        done

        if [ $COUNT -eq $MAX_RETRIES ]; then
        echo " ❌"
        echo "Erro: Não foi possível comunicar com os serviços do Docker após $MAX_RETRIES tentativas. Abortando."
            docker compose down -v
            exit 1
        fi

        docker compose exec -T laravel php artisan migrate:fresh --seed --force
        docker compose exec -T laravel php artisan db:seed --class=IntegrationTestDataSeeder --force
        ../scripts/setup/generate_dev_ssl.sh
    else
        # Local Setup
        php artisan migrate:fresh --seed --force
        php artisan db:seed --class=IntegrationTestDataSeeder --force
        
        pkill -f "artisan serve" || true
        php artisan serve --port=8555 > /dev/null 2>&1 &
        SERVER_PID=$!
        echo "Aguardando artisan serve subir (porta 8555)..."
        sleep 3
    fi
    echo "✅ Database seeded."
fi

# --- 3. JAVA INTEGRATION TESTS ---
if [ "$RUN_INTEGRATION" = true ]; then
    echo ""
    echo "=================================================="
    echo "3. Running Java desktop module tests..."
    echo "=================================================="
    
    DESKTOP_ENV_FILE="../modulo_desktop/.env.test"
    if [ "$MODE" == "docker" ]; then
        echo "API_BASE_URL=https://localhost/api/desktop" > $DESKTOP_ENV_FILE
      
     echo "API_WORKSTATION_HASH=WS-XTYBQRG6" >> $DESKTOP_ENV_FILE
    CERT_PATH=$(readlink -f "../modulo_web/nginx/certs/dev.crt")
        echo "SSL_DEV_CERT_PATH=$CERT_PATH" >> $DESKTOP_ENV_FILE
        echo "SSL_TRUST_ALL=true" >> $DESKTOP_ENV_FILE
    else
        echo "API_BASE_URL=http://127.0.0.1:8555/api/desktop" > $DESKTOP_ENV_FILE
        echo "SSL_TRUST_ALL=false" >> $DESKTOP_ENV_FILE
        echo "API_WORKSTATION_HASH=WS-XTYBQRG6" >> $DESKTOP_ENV_FILE

    fi
  
    cd ../modulo_desktop || { [ "$MODE" != "docker" ] && kill $SERVER_PID; exit 1; }
    mvn test -DexcludedGroups=""
    JAVA_TEST_RESULT=$?
    
    rm -f "$DESKTOP_ENV_FILE"
    cd ../modulo_web
    
    if [ $JAVA_TEST_RESULT -ne 0 ]; then
      echo "❌ Java tests failed!"
      [ "$MODE" != "docker" ] && kill $SERVER_PID
      exit 1
    fi
fi

# --- 4. E2E TESTS (Playwright) ---
if [ "$RUN_E2E" = true ]; then
    echo ""
    echo "=================================================="
    echo "4. Running E2E tests (Playwright)..."
    echo "=================================================="
    
    # Ensure server is running for local mode if integration didn't run
    if [ "$MODE" == "local" ] && [ -z "$SERVER_PID" ]; then
        pkill -f "artisan serve" || true
        php artisan serve --port=8555 > /dev/null 2>&1 &
        SERVER_PID=$!
        sleep 3
    fi

    cd e2e || exit 1
    
    if [ "$MODE" == "docker" ]; then
        PLAYWRIGHT_BASE_URL="https://localhost"
    else
        PLAYWRIGHT_BASE_URL="http://127.0.0.1:8555"
    fi

    echo "Running tests against $PLAYWRIGHT_BASE_URL"
    PLAYWRIGHT_BASE_URL=$PLAYWRIGHT_BASE_URL npx playwright test
    E2E_RESULT=$?
    cd ..
    
    if [ $E2E_RESULT -ne 0 ]; then
      echo "❌ E2E tests failed!"
      [ "$MODE" != "docker" ] && kill $SERVER_PID
      exit 1
    fi
fi

# --- CLEANUP ---
echo ""
echo "=================================================="
echo "5. Cleanup..."
echo "=================================================="

if [ "$MODE" == "docker" ]; then
    if [ "$KEEP_DOCKER" = true ]; then
        echo "✅ Docker mantido para debug."
    else
        docker compose down -v --remove-orphans
        echo "✅ Docker removido."
    fi
else
    if [ -n "$SERVER_PID" ]; then
        kill $SERVER_PID 2>/dev/null
        echo "✅ 'artisan serve' encerrado."
    fi
fi

echo "✅ All selected tests completed successfully!"