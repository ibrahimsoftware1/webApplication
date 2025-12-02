import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Badge from '@/components/ui/Badge.vue'

describe('Badge Component', () => {
  it('should render with default variant', () => {
    const wrapper = mount(Badge, {
      slots: { default: 'Badge' }
    })
    
    expect(wrapper.text()).toBe('Badge')
    expect(wrapper.classes()).toContain('bg-gray-100')
  })

  it('should render with different variants', () => {
    const wrapper = mount(Badge, {
      props: { variant: 'success' },
      slots: { default: 'Success' }
    })
    
    expect(wrapper.classes()).toContain('bg-green-100')
  })
})

