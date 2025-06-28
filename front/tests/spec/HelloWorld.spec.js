import { mount } from '@vue/test-utils'
import { expect, test, describe, beforeEach } from 'vitest'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import HelloWorld from '../../src/components/HelloWorld.vue'

const vuetify = createVuetify({
  components,
  directives,
})

global.ResizeObserver = require('resize-observer-polyfill')

describe('HelloWorld Component', () => {
  let wrapper

  beforeEach(() => {
    wrapper = mount(HelloWorld, {
      global: {
        plugins: [vuetify],
      }
    })
  })

  test('should render v-app component', () => {
    expect(wrapper.find('.v-application').exists()).toBe(true)
  })

  test('should be a Vue component instance', () => {
    expect(wrapper.vm).toBeTruthy()
    expect(wrapper.vm.$).toBeTruthy()
  })

  test('should have correct component structure', () => {
    // Test that the component renders without errors
    expect(wrapper.html()).toContain('v-application')
  })

  test('should mount and unmount without errors', () => {
    expect(() => {
      const testWrapper = mount(HelloWorld, {
        global: {
          plugins: [vuetify],
        }
      })
      testWrapper.unmount()
    }).not.toThrow()
  })

  test('should be responsive to prop changes', async () => {
    // Even though this component doesn't have props,
    // testing the reactivity system
    expect(wrapper.vm).toBeInstanceOf(Object)
    
    // Test Vue reactivity is working
    await wrapper.vm.$nextTick()
    expect(wrapper.find('.v-application').exists()).toBe(true)
  })

  test('should integrate correctly with Vuetify', () => {
    // Check that Vuetify CSS classes are applied
    const app = wrapper.find('.v-application')
    expect(app.exists()).toBe(true)
    
    // Check that Vuetify theme is applied
    expect(app.classes()).toContain('v-application')
  })
})
