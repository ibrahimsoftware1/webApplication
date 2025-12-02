import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Input from '@/components/ui/Input.vue'

describe('Input Component', () => {
  it('should render with label', () => {
    const wrapper = mount(Input, {
      props: {
        modelValue: '',
        label: 'Email'
      }
    })
    
    expect(wrapper.text()).toContain('Email')
  })

  it('should show error message', () => {
    const wrapper = mount(Input, {
      props: {
        modelValue: '',
        error: 'This field is required'
      }
    })
    
    expect(wrapper.text()).toContain('This field is required')
    expect(wrapper.find('input').classes()).toContain('border-red-500')
  })

  it('should emit update:modelValue on input', async () => {
    const wrapper = mount(Input, {
      props: {
        modelValue: ''
      }
    })
    
    const input = wrapper.find('input')
    await input.setValue('test@example.com')
    
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['test@example.com'])
  })
})

