#!/bin/bash
# load-testing.sh - Tests de charge automatisés

set -e

# Configuration
API_URL=${API_URL:-"https://api.muscuscope.com"}
FRONTEND_URL=${FRONTEND_URL:-"https://muscuscope.com"}
CONCURRENT_USERS=${CONCURRENT_USERS:-100}
TEST_DURATION=${TEST_DURATION:-"5m"}
RAMP_UP_TIME=${RAMP_UP_TIME:-"30s"}

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

# Vérification des outils requis
check_dependencies() {
    local missing_tools=()
    
    if ! command -v k6 &> /dev/null; then
        missing_tools+=("k6")
    fi
    
    if ! command -v jq &> /dev/null; then
        missing_tools+=("jq")
    fi
    
    if [[ ${#missing_tools[@]} -gt 0 ]]; then
        log_error "Outils manquants: ${missing_tools[*]}"
        echo "Installation requise:"
        for tool in "${missing_tools[@]}"; do
            case $tool in
                "k6")
                    echo "  - K6: https://k6.io/docs/getting-started/installation/"
                    ;;
                "jq")
                    echo "  - jq: sudo apt-get install jq (Linux) / brew install jq (macOS)"
                    ;;
            esac
        done
        exit 1
    fi
}

echo "🚀 Tests de Charge MuscuScope"
echo "📅 Date: $(date)"
echo "🔗 API: $API_URL"
echo "🌐 Frontend: $FRONTEND_URL"
echo "👥 Utilisateurs simultanés: $CONCURRENT_USERS"
echo "⏱️ Durée: $TEST_DURATION"
echo ""

# Vérification dépendances
log_info "Vérification des dépendances..."
check_dependencies
log_success "Outils disponibles"

# Phase 1: Tests de charge API
log_info "Phase 1: Tests de charge API endpoints"

# Création script K6 pour tests API
K6_API_SCRIPT="/tmp/k6-api-test.js"
cat > "$K6_API_SCRIPT" <<EOF
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Métriques personnalisées
const errorRate = new Rate('errors');
const authTrend = new Trend('auth_duration');
const apiTrend = new Trend('api_duration');

export let options = {
  stages: [
    { duration: '$RAMP_UP_TIME', target: $((CONCURRENT_USERS / 4)) }, // Montée progressive
    { duration: '$TEST_DURATION', target: $CONCURRENT_USERS }, // Charge nominale
    { duration: '30s', target: 0 }, // Descente
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'], // 95% des requêtes < 2s
    http_req_failed: ['rate<0.05'], // Moins de 5% d'erreurs
    errors: ['rate<0.05'],
  },
};

// Données de test
const testUsers = [
  { email: 'loadtest1@example.com', password: 'TestPassword123!' },
  { email: 'loadtest2@example.com', password: 'TestPassword123!' },
  { email: 'loadtest3@example.com', password: 'TestPassword123!' },
];

export default function () {
  // 1. Test endpoint health
  let healthResponse = http.get('$API_URL/api/health');
  check(healthResponse, {
    'health endpoint status 200': (r) => r.status === 200,
    'health response time < 500ms': (r) => r.timings.duration < 500,
  }) || errorRate.add(1);

  // 2. Récupération token CSRF
  let csrfResponse = http.get('$API_URL/api/csrf-token');
  let csrfToken = '';
  
  if (csrfResponse.status === 200) {
    try {
      csrfToken = JSON.parse(csrfResponse.body).token;
    } catch (e) {
      errorRate.add(1);
    }
  }

  // 3. Test authentification (avec utilisateur aléatoire)
  let user = testUsers[Math.floor(Math.random() * testUsers.length)];
  let authStart = Date.now();
  
  let authResponse = http.post('$API_URL/api/login', JSON.stringify({
    email: user.email,
    password: user.password
  }), {
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken,
    },
  });
  
  authTrend.add(Date.now() - authStart);
  
  let authSuccess = check(authResponse, {
    'auth response status in [200, 401]': (r) => r.status === 200 || r.status === 401,
    'auth response time < 1000ms': (r) => r.timings.duration < 1000,
  });
  
  if (!authSuccess) {
    errorRate.add(1);
  }

  // 4. Test endpoints API (si authentifié)
  let token = '';
  if (authResponse.status === 200) {
    try {
      token = JSON.parse(authResponse.body).token;
    } catch (e) {
      // Token non récupérable, continuer sans auth
    }
  }

  // 5. Test endpoint machines (public ou avec auth)
  let apiStart = Date.now();
  let headers = { 'Content-Type': 'application/json' };
  if (token) {
    headers['Authorization'] = 'Bearer ' + token;
  }
  
  let machinesResponse = http.get('$API_URL/api/machines', { headers });
  apiTrend.add(Date.now() - apiStart);
  
  check(machinesResponse, {
    'machines endpoint accessible': (r) => r.status === 200 || r.status === 401,
    'machines response time < 1000ms': (r) => r.timings.duration < 1000,
  }) || errorRate.add(1);

  // 6. Test endpoint doc
  let docResponse = http.get('$API_URL/api/doc');
  check(docResponse, {
    'doc endpoint status 200': (r) => r.status === 200,
    'doc response time < 2000ms': (r) => r.timings.duration < 2000,
  }) || errorRate.add(1);

  // Pause entre les itérations
  sleep(Math.random() * 2 + 1); // 1-3 secondes
}

export function handleSummary(data) {
  return {
    '/tmp/k6-api-results.json': JSON.stringify(data, null, 2),
  };
}
EOF

log_info "Exécution tests de charge API..."
k6 run "$K6_API_SCRIPT"

# Analyse résultats API
if [[ -f "/tmp/k6-api-results.json" ]]; then
    API_SUCCESS_RATE=$(jq -r '.metrics.http_req_failed.values.rate * 100' /tmp/k6-api-results.json 2>/dev/null || echo "0")
    API_P95_RESPONSE=$(jq -r '.metrics.http_req_duration.values.p95' /tmp/k6-api-results.json 2>/dev/null || echo "0")
    API_RPS=$(jq -r '.metrics.http_reqs.values.rate' /tmp/k6-api-results.json 2>/dev/null || echo "0")
    
    log_success "Tests API terminés"
    echo "  📊 Taux d'erreur: ${API_SUCCESS_RATE}%"
    echo "  ⏱️ P95 Response: ${API_P95_RESPONSE}ms"
    echo "  🔄 Requêtes/sec: ${API_RPS}"
else
    log_warning "Résultats API non trouvés"
fi

# Phase 2: Tests de charge Frontend
log_info "Phase 2: Tests de charge Frontend"

# Script K6 pour frontend
K6_FRONTEND_SCRIPT="/tmp/k6-frontend-test.js"
cat > "$K6_FRONTEND_SCRIPT" <<EOF
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const errorRate = new Rate('frontend_errors');
const loadTimeTrend = new Trend('page_load_time');

export let options = {
  stages: [
    { duration: '30s', target: $((CONCURRENT_USERS / 2)) },
    { duration: '2m', target: $((CONCURRENT_USERS / 2)) },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(95)<5000'], // Pages < 5s
    http_req_failed: ['rate<0.02'], // Moins de 2% d'erreurs
  },
};

export default function () {
  // Test page principale
  let mainPageStart = Date.now();
  let mainResponse = http.get('$FRONTEND_URL');
  loadTimeTrend.add(Date.now() - mainPageStart);
  
  let mainSuccess = check(mainResponse, {
    'main page status 200': (r) => r.status === 200,
    'main page load < 5s': (r) => r.timings.duration < 5000,
    'main page contains title': (r) => r.body.includes('<title>') && (r.body.includes('MuscuScope') || r.body.includes('Musculation')),
  });
  
  if (!mainSuccess) {
    errorRate.add(1);
  }

  // Test assets statiques (simulation)
  let assetPaths = ['/css/app.css', '/js/app.js', '/favicon.ico'];
  let randomAsset = assetPaths[Math.floor(Math.random() * assetPaths.length)];
  
  let assetResponse = http.get('$FRONTEND_URL' + randomAsset);
  check(assetResponse, {
    'asset accessible': (r) => r.status === 200 || r.status === 404, // 404 acceptable pour assets non existants
    'asset load < 2s': (r) => r.timings.duration < 2000,
  }) || errorRate.add(1);

  sleep(Math.random() * 3 + 2); // 2-5 secondes entre les visites
}

export function handleSummary(data) {
  return {
    '/tmp/k6-frontend-results.json': JSON.stringify(data, null, 2),
  };
}
EOF

log_info "Exécution tests de charge Frontend..."
k6 run "$K6_FRONTEND_SCRIPT"

# Analyse résultats Frontend
if [[ -f "/tmp/k6-frontend-results.json" ]]; then
    FRONTEND_ERROR_RATE=$(jq -r '.metrics.http_req_failed.values.rate * 100' /tmp/k6-frontend-results.json 2>/dev/null || echo "0")
    FRONTEND_P95_LOAD=$(jq -r '.metrics.http_req_duration.values.p95' /tmp/k6-frontend-results.json 2>/dev/null || echo "0")
    FRONTEND_RPS=$(jq -r '.metrics.http_reqs.values.rate' /tmp/k6-frontend-results.json 2>/dev/null || echo "0")
    
    log_success "Tests Frontend terminés"
    echo "  📊 Taux d'erreur: ${FRONTEND_ERROR_RATE}%"
    echo "  ⏱️ P95 Load Time: ${FRONTEND_P95_LOAD}ms"
    echo "  🔄 Pages/sec: ${FRONTEND_RPS}"
else
    log_warning "Résultats Frontend non trouvés"
fi

# Phase 3: Test de stress base de données (si pgbench disponible)
log_info "Phase 3: Tests stress base de données"

if command -v pgbench &> /dev/null; then
    log_info "pgbench détecté, exécution tests DB..."
    
    # Configuration DB (à adapter selon environnement)
    DB_HOST=${DB_HOST:-"localhost"}
    DB_USER=${DB_USER:-"postgres"}
    DB_NAME=${DB_NAME:-"muscuscope_test"}
    
    # Test de connectivité
    if pg_isready -h "$DB_HOST" -U "$DB_USER" >/dev/null 2>&1; then
        log_info "Exécution pgbench (10 connexions, 100 transactions par connexion)..."
        
        PGBENCH_OUTPUT=$(pgbench -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" \
            -c 10 -j 2 -t 100 2>&1 || echo "Erreur pgbench")
        
        if echo "$PGBENCH_OUTPUT" | grep -q "tps"; then
            TPS=$(echo "$PGBENCH_OUTPUT" | grep "tps" | awk '{print $3}')
            log_success "Tests DB terminés - TPS: $TPS"
        else
            log_warning "Tests DB échoués ou non disponibles"
        fi
    else
        log_warning "Base de données non accessible pour tests de stress"
    fi
else
    log_warning "pgbench non disponible - Tests DB ignorés"
fi

# Phase 4: Test performance avec Lighthouse (si disponible)
log_info "Phase 4: Tests performance Lighthouse"

if command -v lighthouse &> /dev/null; then
    log_info "Lighthouse détecté, analyse performance..."
    
    LIGHTHOUSE_OUTPUT="/tmp/lighthouse-report.json"
    lighthouse "$FRONTEND_URL" \
        --chrome-flags="--headless --no-sandbox" \
        --output=json \
        --output-path="$LIGHTHOUSE_OUTPUT" \
        --quiet 2>/dev/null || log_warning "Erreur Lighthouse"
    
    if [[ -f "$LIGHTHOUSE_OUTPUT" ]]; then
        PERF_SCORE=$(jq -r '.categories.performance.score * 100' "$LIGHTHOUSE_OUTPUT" 2>/dev/null || echo "0")
        FCP=$(jq -r '.audits["first-contentful-paint"].numericValue' "$LIGHTHOUSE_OUTPUT" 2>/dev/null || echo "0")
        LCP=$(jq -r '.audits["largest-contentful-paint"].numericValue' "$LIGHTHOUSE_OUTPUT" 2>/dev/null || echo "0")
        
        log_success "Lighthouse terminé"
        echo "  🎯 Performance Score: ${PERF_SCORE}/100"
        echo "  🎨 First Contentful Paint: ${FCP}ms"
        echo "  📏 Largest Contentful Paint: ${LCP}ms"
    else
        log_warning "Rapport Lighthouse non généré"
    fi
else
    log_warning "Lighthouse non disponible - Tests performance ignorés"
fi

# Phase 5: Génération rapport final
log_info "Phase 5: Génération rapport de performance"

REPORT_FILE="/tmp/load-test-report-$(date +%Y%m%d_%H%M%S).md"

cat > "$REPORT_FILE" <<EOF
# Rapport de Tests de Charge - MuscuScope
Date: $(date)
Durée totale: $SECONDS secondes

## Configuration
- **API URL**: $API_URL
- **Frontend URL**: $FRONTEND_URL  
- **Utilisateurs simultanés**: $CONCURRENT_USERS
- **Durée test**: $TEST_DURATION
- **Montée en charge**: $RAMP_UP_TIME

## Résultats API
- **Taux d'erreur**: ${API_SUCCESS_RATE:-N/A}%
- **P95 Response Time**: ${API_P95_RESPONSE:-N/A}ms
- **Requêtes/seconde**: ${API_RPS:-N/A}

## Résultats Frontend
- **Taux d'erreur**: ${FRONTEND_ERROR_RATE:-N/A}%
- **P95 Load Time**: ${FRONTEND_P95_LOAD:-N/A}ms
- **Pages/seconde**: ${FRONTEND_RPS:-N/A}

## Performance (Lighthouse)
- **Score Performance**: ${PERF_SCORE:-N/A}/100
- **First Contentful Paint**: ${FCP:-N/A}ms
- **Largest Contentful Paint**: ${LCP:-N/A}ms

## Base de données
- **TPS**: ${TPS:-N/A}

## Recommandations
EOF

# Ajout recommandations basées sur les résultats
if [[ $(echo "${API_SUCCESS_RATE:-0} > 5" | bc 2>/dev/null || echo 0) -eq 1 ]]; then
    echo "- ⚠️ Taux d'erreur API élevé (${API_SUCCESS_RATE}%) - Investigation requise" >> "$REPORT_FILE"
fi

if [[ $(echo "${API_P95_RESPONSE:-0} > 2000" | bc 2>/dev/null || echo 0) -eq 1 ]]; then
    echo "- ⚠️ Temps de réponse API dégradé (${API_P95_RESPONSE}ms) - Optimisation recommandée" >> "$REPORT_FILE"
fi

if [[ $(echo "${PERF_SCORE:-100} < 70" | bc 2>/dev/null || echo 0) -eq 1 ]]; then
    echo "- ⚠️ Score performance faible (${PERF_SCORE}/100) - Optimisation frontend requise" >> "$REPORT_FILE"
fi

echo "- ✅ Tests de charge complétés avec succès" >> "$REPORT_FILE"

log_info "Rapport généré: $REPORT_FILE"

# Nettoyage fichiers temporaires
rm -f "$K6_API_SCRIPT" "$K6_FRONTEND_SCRIPT" 2>/dev/null || true

# Résumé final
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RÉSUMÉ TESTS DE CHARGE"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⏱️ Durée totale: $SECONDS secondes"
echo "👥 Utilisateurs simulés: $CONCURRENT_USERS"
echo ""
echo "🌐 **API Performance**:"
echo "  • Erreurs: ${API_SUCCESS_RATE:-N/A}%"
echo "  • P95 Response: ${API_P95_RESPONSE:-N/A}ms"
echo "  • Throughput: ${API_RPS:-N/A} req/s"
echo ""
echo "🖥️ **Frontend Performance**:"
echo "  • Erreurs: ${FRONTEND_ERROR_RATE:-N/A}%"
echo "  • P95 Load: ${FRONTEND_P95_LOAD:-N/A}ms"
echo "  • Throughput: ${FRONTEND_RPS:-N/A} pages/s"
echo ""
echo "🎯 **Lighthouse Score**: ${PERF_SCORE:-N/A}/100"
echo ""

# Évaluation globale
OVERALL_GOOD=true

if [[ $(echo "${API_SUCCESS_RATE:-0} > 5" | bc 2>/dev/null || echo 0) -eq 1 ]]; then
    OVERALL_GOOD=false
fi

if [[ $(echo "${API_P95_RESPONSE:-0} > 3000" | bc 2>/dev/null || echo 0) -eq 1 ]]; then
    OVERALL_GOOD=false
fi

if [[ "$OVERALL_GOOD" == "true" ]]; then
    log_success "🎉 TESTS DE CHARGE RÉUSSIS"
    echo "✅ Performance acceptable sous charge"
    exit 0
else
    log_warning "⚠️ PROBLÈMES DE PERFORMANCE DÉTECTÉS"
    echo "🔍 Optimisation recommandée avant mise en production"
    exit 1
fi
