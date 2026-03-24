#!/bin/bash

# ==============================================================================
# 🐄 CattleRFID Onboarding & Setup Script
# ==============================================================================
# This script automates the installation and verification of necessary tools
# to run the CattleRFID project (Web, Desktop, and Arduino modules).
# ==============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================================${NC}"
echo -e "${YELLOW}🚀 Starting CattleRFID Onboarding Script...${NC}"
echo -e "${BLUE}====================================================${NC}\n"

# Check for --check or -c flag
CHECK_ONLY=false
if [[ "$1" == "--check" ]] || [[ "$1" == "-c" ]]; then
    CHECK_ONLY=true
    echo -e "${YELLOW}🔍 Running in Check-Only Mode...${NC}\n"
fi

# ------------------------------------------------------------------------------
# 1. Tool Existence & Version Checks
# ------------------------------------------------------------------------------

# Function to check if a command exists and its version
check_tool() {
    local tool=$1
    local min_version=$2
    local version_cmd=$3
    
    if ! command -v "$tool" &> /dev/null; then
        echo -e "${RED}❌ $tool is not installed.${NC}"
        return 1
    else
        local version
        version=$($version_cmd 2>&1 | head -n 1)
        echo -e "${GREEN}✅ $tool found: $version${NC}"
        return 0
    fi
}

echo -e "${BLUE}[1/3] Checking Prerequisites...${NC}"

check_tool "php" "8.2" "php -v" || { echo -e "${RED}Please install PHP 8.2+${NC}"; exit 1; }
check_tool "composer" "" "composer -V" || { echo -e "${RED}Please install Composer${NC}"; exit 1; }
check_tool "node" "" "node -v" || { echo -e "${RED}Please install Node.js${NC}"; exit 1; }
check_tool "npm" "" "npm -v" || { echo -e "${RED}Please install NPM${NC}"; exit 1; }
check_tool "java" "21" "java -version" || { echo -e "${RED}Please install Java 21+${NC}"; exit 1; }
check_tool "mvn" "" "mvn -v" || { echo -e "${RED}Please install Maven${NC}"; exit 1; }
check_tool "git" "" "git --version" || { echo -e "${RED}Please install Git${NC}"; exit 1; }

# Stop here if check-only mode is enabled
if [ "$CHECK_ONLY" = true ]; then
    echo -e "\n${GREEN}✅ Prerequisite check complete! Use without flags to run full setup.${NC}"
    exit 0
fi

# ------------------------------------------------------------------------------
# 2. Module Initialization: modulo_web
# ------------------------------------------------------------------------------

echo -e "\n${BLUE}[2/3] Initializing 'modulo_web'...${NC}"
if [ -d "modulo_web" ]; then
    cd modulo_web || exit 1

    if [ ! -f .env ]; then
        echo -e "  📄 Creating .env from .env.example..."
        cp .env.example .env
    fi

    echo -e "  📥 Installing PHP dependencies (Composer)..."
    composer install --no-interaction --quiet || { echo -e "${RED}Composer install failed.${NC}"; exit 1; }

    echo -e "  🔑 Generating application key..."
    php artisan key:generate --quiet

    echo -e "  🗄️ Setting up database..."
    # Check if DB_CONNECTION is sqlite and ensure database file exists
    if grep -q "DB_CONNECTION=sqlite" .env; then
        # Check standard Laravel sqlite paths
        mkdir -p database
        if [ ! -f database/database.sqlite ]; then
            touch database/database.sqlite
            echo -e "  ✅ Created database/database.sqlite"
        fi
    fi
    
    echo -e "  🌱 Running migrations and seeding..."
    php artisan migrate:fresh --seed --force || { echo -e "${RED}Database migration failed.${NC}"; exit 1; }

    echo -e "  📥 Installing NPM dependencies..."
    npm install --quiet || { echo -e "${RED}NPM install failed.${NC}"; exit 1; }

    echo -e "  🏗️ Building assets (Vite)..."
    npm run build --quiet || { echo -e "${RED}Vite build failed.${NC}"; exit 1; }

    cd ..
    echo -e "${GREEN}✅ modulo_web is ready!${NC}"
else
    echo -e "${RED}⚠️ modulo_web directory not found!${NC}"
fi

# ------------------------------------------------------------------------------
# 3. Module Initialization: modulo_desktop
# ------------------------------------------------------------------------------

echo -e "\n${BLUE}[3/3] Initializing 'modulo_desktop'...${NC}"
if [ -d "modulo_desktop" ]; then
    cd modulo_desktop || exit 1
    
    echo -e "  📥 Compiling Java project (Maven)..."
    mvn clean compile -q || { echo -e "${RED}Maven compilation failed.${NC}"; exit 1; }
    
    cd ..
    echo -e "${GREEN}✅ modulo_desktop is ready!${NC}"
else
    echo -e "${RED}⚠️ modulo_desktop directory not found!${NC}"
fi

# ------------------------------------------------------------------------------
# Final Success Message
# ------------------------------------------------------------------------------

echo -e "\n${BLUE}====================================================${NC}"
echo -e "${GREEN}✨ CattleRFID Project is Ready to Go! ✨${NC}"
echo -e "${BLUE}====================================================${NC}\n"

echo -e "To start the development environment:"
echo -e "${YELLOW}Dashboard (Web):${NC}"
echo -e "  cd modulo_web && php artisan serve"
echo -e "\n${YELLOW}Terminal (Desktop):${NC}"
echo -e "  cd modulo_desktop"
echo -e "  - Linux:   mvn exec:exec"
echo -e "  - Win/Mac: mvn exec:java"
echo -e "\n${YELLOW}Hardware (Arduino):${NC}"
echo -e "  1. Open modulo_arduino/src/rfid/rfid.ino in Arduino IDE"
echo -e "  2. Install 'MFRC522v2' library"
echo -e "  3. Upload to your board"
echo -e "\n${BLUE}Happy Coding! 🐄${NC}"
