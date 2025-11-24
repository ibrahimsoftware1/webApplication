import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Card from '@/components/ui/Card.vue'

describe('Card Component', () => {
  it('should render with title', () => {
    const wrapper = mount(Card, {
      props: { title: 'Test Card' }
    })
    
    expect(wrapper.text()).toContain('Test Card')
  })

  it('should render with slots', () => {
    const wrapper = mount(Card, {
      slots: {
        default: 'Card content',
        header: 'Card header',
        footer: 'Card footer'
      }
    })
    
    expect(wrapper.text()).toContain('Card content')
    expect(wrapper.text()).toContain('Card header')
    expect(wrapper.text()).toContain('Card footer')
  })
})

