import type { Component } from 'vue'
import type { NavigationGuard, RouteMeta } from 'vue-router'

export interface MetaRoute extends RouteMeta {
  requiresAuth?: boolean;
  requiresGuest?: boolean;
  menu?: boolean;
  allowedRoles?: string[];
}

export interface RouteConfig {
  path: string;
  name?: string;
  component: Component;
  meta?: MetaRoute;
  beforeEnter?: NavigationGuard | NavigationGuard[];
}
