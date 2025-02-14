import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import JsonCompare from '../src/components/JsonCompare.vue';

describe('JsonCompare.vue', () => {
  it('component renders correctly', () => {
    const wrapper = mount(JsonCompare);
    expect(wrapper.exists()).toBe(true);
  });

  it('has two text areas for JSON input', () => {
    const wrapper = mount(JsonCompare);
    const textareas = wrapper.findAll('textarea');
    expect(textareas.length).toBe(2);
  });

  it('shows error when invalid JSON is entered', async () => {
    const wrapper = mount(JsonCompare);
    const firstJsonInput = wrapper.findAll('textarea')[0];
    await firstJsonInput.setValue('{invalid json}');
    await wrapper.find('button.compare-btn').trigger('click');
    
    expect(wrapper.text()).toContain('Invalid JSON');
  });

  it('successfully compares two valid JSON objects', async () => {
    const wrapper = mount(JsonCompare);
    const json1 = '{"name": "John", "age": 30}';
    const json2 = '{"name": "John", "age": 31}';
    
    const textareas = wrapper.findAll('textarea');
    await textareas[0].setValue(json1);
    await textareas[1].setValue(json2);
    
    await wrapper.find('button.compare-btn').trigger('click');
    
    expect(wrapper.find('.differences').exists()).toBe(true);
    expect(wrapper.text()).toContain('age');
  });

  it('shows "No differences found" for identical JSON', async () => {
    const wrapper = mount(JsonCompare);
    const json = '{"name": "John", "age": 30}';
    
    const textareas = wrapper.findAll('textarea');
    await textareas[0].setValue(json);
    await textareas[1].setValue(json);
    
    await wrapper.find('button.compare-btn').trigger('click');
    
    expect(wrapper.text()).toContain('No differences found');
  });
});