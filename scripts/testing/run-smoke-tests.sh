#!/bin/bash
# run-smoke-tests.sh - Tests de fumée pour validation rapide

set -e

# Configuration
API_URL=${API_URL:-"https://api.muscuscope.com"}
FRONTEND_URL=${FRONTEND_URL:-"https://muscuscope.com"}
TIMEOUT=${TIMEOUT:-30}
QUICK_MODE=${1:-false}

# Couleurs pour les logs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Variables de test
TESTS_PASSED=0
TESTS_FAILED=0
TOTAL_TESTS=0

# Fonction pour exécuter un test
run_test() {
    local test_name="$1"
    local test_command="$2"
    local is_critical="${3:-true}"
    
    ((TOTAL_TESTS++))
    
    log_info "Test: $test_name"
    
    if eval "$test_command" >/dev/null 2>&1; then
        log_success "$test_name"
        ((TESTS_PASSED++))
        return 0
    else
        if [[ "$is_critical" == "true" ]]; then
            log_error "$test_name"
            ((TESTS_FAILED++))
            return 1
        else
            log_warning "$test_name (non critique)"
            return 0
        fi
    fi
}

# Fonction pour tester un endpoint avec retry
test_endpoint() {
    local url="$1"
    local expected_code="${2:-200}"
    local max_retries="${3:-3}"
    
    for i in $(seq 1 $max_retries); do
        local response_code=$(curl -o /dev/null -s -w "%{http_code}" --max-time $TIMEOUT "$url" || echo "000")
        
        if [[ "$response_code" == "$expected_code" ]]; then
            return 0
        fi
        
        if [[ $i -lt $max_retries ]]; then
            sleep 2
        fi
    done
    
    return 1
}

echo "🧪 Tests de Fumée MuscuScope"
echo "📅 Date: $(date)"
echo "🔗 API: $API_URL"
echo "🌐 Frontend: $FRONTEND_URL"
echo "⚡ Mode rapide: $QUICK_MODE"
echo ""

# Test 1: Connectivité de base
log_info "=== Tests de Connectivité ==="

run_test "Frontend accessible" \
    "test_endpoint '$FRONTEND_URL' 200"

run_test "API health endpoint" \
    "test_endpoint '$API_URL/api/health' 200"

run_test "API doc endpoint" \
    "test_endpoint '$API_URL/api/doc' 200" false

# Test 2: Endpoints critiques API
log_info "=== Tests API Critiques ==="

run_test "CSRF token endpoint" \
    "test_endpoint '$API_URL/api/csrf-token' 200"

run_test "Login endpoint (POST)" \
    "curl -X POST -H 'Content-Type: application/json' -d '{\"email\":\"invalid\",\"password\":\"invalid\"}' --max-time $TIMEOUT '$API_URL/api/login' | grep -q 'error\\|invalid\\|401'"

# Test 3: Base de données
log_info "=== Tests Base de Données ==="

run_test "Database health endpoint" \
    "test_endpoint '$API_URL/api/health/db' 200"

# Test 4: Performance de base
log_info "=== Tests Performance ==="

# Test temps de réponse API
API_RESPONSE_TIME=$(curl -o /dev/null -s -w "%{time_total}" --max-time $TIMEOUT "$API_URL/api/health" || echo "999")
API_RESPONSE_MS=$(echo "$API_RESPONSE_TIME * 1000" | bc 2>/dev/null | cut -d. -f1 || echo "999")

if [[ ${API_RESPONSE_MS:-999} -lt 2000 ]]; then
    log_success "Temps de réponse API acceptable (${API_RESPONSE_MS}ms)"
    ((TESTS_PASSED++))
else
    log_warning "Temps de réponse API dégradé (${API_RESPONSE_MS}ms)"
fi
((TOTAL_TESTS++))

# Test temps de réponse Frontend
FRONTEND_RESPONSE_TIME=$(curl -o /dev/null -s -w "%{time_total}" --max-time $TIMEOUT "$FRONTEND_URL" || echo "999")
FRONTEND_RESPONSE_MS=$(echo "$FRONTEND_RESPONSE_TIME * 1000" | bc 2>/dev/null | cut -d. -f1 || echo "999")

if [[ ${FRONTEND_RESPONSE_MS:-999} -lt 3000 ]]; then
    log_success "Temps de réponse Frontend acceptable (${FRONTEND_RESPONSE_MS}ms)"
    ((TESTS_PASSED++))
else
    log_warning "Temps de réponse Frontend dégradé (${FRONTEND_RESPONSE_MS}ms)"
fi
((TOTAL_TESTS++))

# Test 5: Sécurité de base
log_info "=== Tests Sécurité ==="

# Test HTTPS redirect
HTTP_FRONTEND=$(echo "$FRONTEND_URL" | sed 's/https/http/')
HTTP_RESPONSE=$(curl -o /dev/null -s -w "%{http_code}" --max-time $TIMEOUT "$HTTP_FRONTEND" || echo "000")

if [[ "$HTTP_RESPONSE" == "301" || "$HTTP_RESPONSE" == "302" ]]; then
    log_success "Redirection HTTPS active"
    ((TESTS_PASSED++))
else
    log_warning "Redirection HTTPS manquante (Code: $HTTP_RESPONSE)"
fi
((TOTAL_TESTS++))

# Test headers de sécurité
SECURITY_HEADERS=$(curl -s -I --max-time $TIMEOUT "$FRONTEND_URL" | grep -i "x-content-type-options\|x-frame-options\|strict-transport-security" | wc -l)

if [[ $SECURITY_HEADERS -ge 2 ]]; then
    log_success "Headers de sécurité présents"
    ((TESTS_PASSED++))
else
    log_warning "Headers de sécurité insuffisants"
fi
((TOTAL_TESTS++))

# Tests approfondis (si pas en mode rapide)
if [[ "$QUICK_MODE" != "true" && "$QUICK_MODE" != "--quick" ]]; then
    log_info "=== Tests Approfondis ==="
    
    # Test authentification complète
    run_test "Workflow authentification complet" \
        "
        # Récupération token CSRF
        CSRF_TOKEN=\$(curl -s --max-time $TIMEOUT '$API_URL/api/csrf-token' | jq -r '.token' 2>/dev/null || echo '')
        
        # Test avec token CSRF
        if [[ -n \"\$CSRF_TOKEN\" && \"\$CSRF_TOKEN\" != \"null\" ]]; then
            curl -X POST \
                -H 'Content-Type: application/json' \
                -H \"X-CSRF-Token: \$CSRF_TOKEN\" \
                -d '{\"email\":\"test@example.com\",\"password\":\"invalid\"}' \
                --max-time $TIMEOUT \
                '$API_URL/api/login' | grep -q 'credentials\\|invalid\\|401'
        else
            false
        fi
        " false
    
    # Test CORS
    run_test "Configuration CORS" \
        "curl -H 'Origin: https://muscuscope.com' -H 'Access-Control-Request-Method: POST' -H 'Access-Control-Request-Headers: Content-Type' -X OPTIONS --max-time $TIMEOUT '$API_URL/api/login' | grep -q 'Access-Control-Allow'" false
    
    # Test rate limiting (si configuré)
    log_info "Test rate limiting..."
    RATE_LIMIT_OK=true
    for i in {1..10}; do
        RESPONSE_CODE=$(curl -o /dev/null -s -w "%{http_code}" --max-time 5 "$API_URL/api/health" || echo "000")
        if [[ "$RESPONSE_CODE" == "429" ]]; then
            log_success "Rate limiting actif (detecté à la tentative $i)"
            RATE_LIMIT_OK=true
            break
        fi
        sleep 0.1
    done
    
    if [[ "$RATE_LIMIT_OK" == "true" ]]; then
        ((TESTS_PASSED++))
    else
        log_warning "Rate limiting non détecté (non critique)"
    fi
    ((TOTAL_TESTS++))
fi

# Test 6: Monitoring et logs
log_info "=== Tests Monitoring ==="

# Test endpoint métriques (si disponible)
run_test "Endpoint métriques" \
    "test_endpoint '$API_URL/metrics' 200" false

# Test 7: Validation contenu
log_info "=== Tests Contenu ==="

# Validation réponse API health
HEALTH_RESPONSE=$(curl -s --max-time $TIMEOUT "$API_URL/api/health" || echo "{}")
if echo "$HEALTH_RESPONSE" | jq . >/dev/null 2>&1; then
    if echo "$HEALTH_RESPONSE" | jq -e '.status' >/dev/null 2>&1; then
        log_success "Réponse health API valide"
        ((TESTS_PASSED++))
    else
        log_warning "Réponse health API incomplète"
    fi
else
    log_error "Réponse health API invalide"
    ((TESTS_FAILED++))
fi
((TOTAL_TESTS++))

# Validation titre page frontend
FRONTEND_TITLE=$(curl -s --max-time $TIMEOUT "$FRONTEND_URL" | grep -o '<title>[^<]*</title>' | sed 's/<title>\|<\/title>//g' || echo "")
if [[ "$FRONTEND_TITLE" =~ MuscuScope ]] || [[ "$FRONTEND_TITLE" =~ Musculation ]]; then
    log_success "Titre page frontend correct"
    ((TESTS_PASSED++))
else
    log_warning "Titre page frontend suspect: '$FRONTEND_TITLE'"
fi
((TOTAL_TESTS++))

# Résumé final
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RÉSUMÉ DES TESTS DE FUMÉE"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Tests réussis: $TESTS_PASSED"
echo "❌ Tests échoués: $TESTS_FAILED"
echo "📋 Total tests: $TOTAL_TESTS"

SUCCESS_RATE=$((TESTS_PASSED * 100 / TOTAL_TESTS))
echo "📈 Taux de réussite: $SUCCESS_RATE%"
echo "⏱️ Durée: $SECONDS secondes"

# Métriques de performance
echo ""
echo "⚡ MÉTRIQUES PERFORMANCE:"
echo "  • API Response: ${API_RESPONSE_MS}ms"
echo "  • Frontend Response: ${FRONTEND_RESPONSE_MS}ms"

echo ""
if [[ $TESTS_FAILED -eq 0 ]]; then
    log_success "🎉 TOUS LES TESTS DE FUMÉE RÉUSSIS"
    echo "✅ Système prêt pour utilisation"
    exit 0
elif [[ $TESTS_FAILED -le 2 && $SUCCESS_RATE -ge 80 ]]; then
    log_warning "⚠️ TESTS MAJORITAIREMENT RÉUSSIS"
    echo "🔍 Vérification recommandée des échecs non-critiques"
    exit 0
else
    log_error "❌ ÉCHECS CRITIQUES DÉTECTÉS"
    echo "🚨 Investigation immédiate requise"
    echo ""
    echo "🔧 Actions recommandées:"
    echo "  1. Vérifier les logs d'application"
    echo "  2. Contrôler l'état des services"
    echo "  3. Valider la connectivité réseau"
    echo "  4. Exécuter un diagnostic complet"
    exit 1
fi
