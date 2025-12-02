import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Button from '@/components/ui/Button.vue'

describe('Button Component', () => {
  it('should render with default props', () => {
    const wrapper = mount(Button, {
      slots: { default: 'Click me' }
    })
    
    expect(wrapper.text()).toBe('Click me')
    expect(wrapper.classes()).toContain('bg-primary-600')
  })

  it('should render with different variants', () => {
    const wrapper = mount(Button, {
      props: { variant: 'danger' },
      slots: { default: 'Delete' }
    })
    
    expect(wrapper.classes()).toContain('bg-red-600')
  })

  it('should be disabled when loading', () => {
    const wrapper = mount(Button, {
      props: { loading: true },
      slots: { default: 'Submit' }
    })
    
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('should emit click event', async () => {
    const wrapper = mount(Button, {
      slots: { default: 'Click me' }
    })
    
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
  })
})

