// plugins/vuetify.ts
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { mdi } from 'vuetify/iconsets/mdi'
import colors from 'vuetify/util/colors'

// Thème personnalisé pour MuscuScope
const lightTheme = {
  dark: false,
  colors: {
    background: '#FFFFFF',
    surface: '#FFFFFF',
    'surface-variant': '#F5F5F5',
    'on-surface': '#1D1B20',
    'on-surface-variant': '#49454F',
    primary: '#6750A4',
    'primary-darken-1': '#5842A1',
    secondary: '#625B71',
    'secondary-darken-1': '#4C456B',
    error: '#BA1A1A',
    info: '#2196F3',
    success: '#4CAF50',
    warning: '#FF9800',
    accent: '#03DAC6',
    // Couleurs pour la navigation
    'nav-surface': '#FAFAFA',
    'nav-primary': '#6750A4',
    'nav-active': '#E7E0EC',
  },
  variables: {
    'border-color': '#E0E0E0',
    'border-opacity': 0.12,
    'high-emphasis-opacity': 0.87,
    'medium-emphasis-opacity': 0.60,
    'disabled-opacity': 0.38,
    'activated-opacity': 0.12,
    'hover-opacity': 0.04,
    'focus-opacity': 0.12,
    'selected-opacity': 0.08,
    'dragged-opacity': 0.08,
    'theme-kbd': '#212529',
    'theme-on-kbd': '#FFFFFF',
    'theme-code': '#F5F5F5',
    'theme-on-code': '#000000',
  }
}

const darkTheme = {
  dark: true,
  colors: {
    background: '#121212',
    surface: '#1E1E1E',
    'surface-variant': '#2D2D2D',
    'on-surface': '#E6E1E6',
    'on-surface-variant': '#CAC5CD',
    primary: '#D0BCFF',
    'primary-darken-1': '#B8A9FF',
    secondary: '#CCC2DC',
    'secondary-darken-1': '#B4A8C7',
    error: '#FFB4AB',
    info: '#64B5F6',
    success: '#81C784',
    warning: '#FFB74D',
    accent: '#80CBC4',
    // Couleurs pour la navigation
    'nav-surface': '#1A1A1A',
    'nav-primary': '#D0BCFF',
    'nav-active': '#2D2D2D',
  },
  variables: {
    'border-color': '#272727',
    'border-opacity': 0.12,
    'high-emphasis-opacity': 1,
    'medium-emphasis-opacity': 0.70,
    'disabled-opacity': 0.38,
    'activated-opacity': 0.12,
    'hover-opacity': 0.08,
    'focus-opacity': 0.12,
    'selected-opacity': 0.08,
    'dragged-opacity': 0.08,
    'theme-kbd': '#212529',
    'theme-on-kbd': '#FFFFFF',
    'theme-code': '#343434',
    'theme-on-code': '#FFFFFF',
  }
}

export default createVuetify({
  components,
  directives,
  
  // Configuration des icônes
  icons: {
    defaultSet: 'mdi',
    sets: {
      mdi,
    },
  },

  // Thèmes
  theme: {
    defaultTheme: 'light',
    themes: {
      light: lightTheme,
      dark: darkTheme,
    },
    variations: {
      colors: ['primary', 'secondary', 'accent'],
      lighten: 4,
      darken: 4,
    },
  },

  // Configuration par défaut
  defaults: {
    global: {
      ripple: true,
      hideDetails: 'auto',
    },
    VBtn: {
      variant: 'flat',
      height: 40,
      style: 'text-transform: none; font-weight: 500;',
    },
    VCard: {
      elevation: 2,
      rounded: 'lg',
    },
    VChip: {
      size: 'small',
      rounded: 'lg',
    },
    VList: {
      density: 'comfortable',
      nav: true,
    },
    VListItem: {
      rounded: 'lg',
      ripple: true,
    },
    VNavigationDrawer: {
      elevation: 2,
      rail: false,
      expandOnHover: true,
      color: 'nav-surface',
    },
    VAvatar: {
      size: 40,
    },
    VBadge: {
      dot: false,
      inline: false,
      rounded: 'lg',
    },
    VMenu: {
      rounded: 'lg',
      offset: 8,
      closeOnContentClick: false,
      transition: 'slide-y-transition',
    },
    VTextField: {
      variant: 'outlined',
      density: 'comfortable',
      hideDetails: 'auto',
    },
    VTextarea: {
      variant: 'outlined',
      density: 'comfortable',
      hideDetails: 'auto',
    },
    VSelect: {
      variant: 'outlined',
      density: 'comfortable',
      hideDetails: 'auto',
    },
    VAutocomplete: {
      variant: 'outlined',
      density: 'comfortable',
      hideDetails: 'auto',
    },
    VDialog: {
      maxWidth: 600,
      rounded: 'xl',
    },
    VSnackbar: {
      timeout: 4000,
      rounded: 'lg',
      location: 'bottom right',
    },
    VFab: {
      size: 'default',
      elevation: 8,
      rounded: 'xl',
    },
  },

  // Configuration du display (responsive)
  display: {
    mobileBreakpoint: 'sm',
    thresholds: {
      xs: 0,
      sm: 600,
      md: 960,
      lg: 1280,
      xl: 1920,
      xxl: 2560,
    },
  },

  // Paramètres d'accessibilité
  ssr: false,
  locale: {
    locale: 'fr',
    fallback: 'en',
  },

  // Configuration avancée
  aliases: {
    VBtnPrimary: 'VBtn',
    VBtnSecondary: 'VBtn',
  },
})

// Types personnalisés pour TypeScript
declare module 'vuetify' {
  interface ThemeDefinition {
    colors: {
      'nav-surface'?: string
      'nav-primary'?: string
      'nav-active'?: string
    }
  }
}

// Utilitaires pour la navigation
export const navigationBreakpoints = {
  mobile: 960,
  tablet: 1280,
  desktop: 1920,
}

export const navigationConfig = {
  railWidth: 80,
  expandedWidth: 320,
  mobileHeight: '70vh',
  animationDuration: 300,
  hoverDelay: 200,
}

// Helper functions
export const useNavigationTheme = () => {
  const theme = useTheme()
  
  const isDark = computed(() => theme.global.name.value === 'dark')
  
  const navColors = computed(() => ({
    surface: `rgb(var(--v-theme-nav-surface))`,
    primary: `rgb(var(--v-theme-nav-primary))`,
    active: `rgb(var(--v-theme-nav-active))`,
    border: isDark.value 
      ? 'rgba(255, 255, 255, 0.12)' 
      : 'rgba(0, 0, 0, 0.12)',
    hover: isDark.value
      ? 'rgba(255, 255, 255, 0.08)'
      : 'rgba(0, 0, 0, 0.04)',
  }))
  
  const toggleTheme = () => {
    theme.global.name.value = isDark.value ? 'light' : 'dark'
  }
  
  return {
    isDark,
    navColors,
    toggleTheme,
    theme,
  }
}

// Composable pour la responsivité
export const useNavigationDisplay = () => {
  const { mobile, tablet, desktop, width, height } = useDisplay()
  
  const isMobile = computed(() => width.value < navigationBreakpoints.mobile)
  const isTablet = computed(() => 
    width.value >= navigationBreakpoints.mobile && 
    width.value < navigationBreakpoints.tablet
  )
  const isDesktop = computed(() => width.value >= navigationBreakpoints.tablet)
  
  const navigationMode = computed(() => {
    if (isMobile.value) return 'mobile'
    if (isTablet.value) return 'tablet'
    return 'desktop'
  })
  
  return {
    isMobile,
    isTablet,
    isDesktop,
    navigationMode,
    width,
    height,
  }
}
