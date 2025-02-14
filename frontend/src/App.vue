<template>
  <div class="min-h-screen bg-base-200 p-4">
    <div class="container mx-auto">
      <h1 class="text-4xl font-bold text-center mb-8">JSON Compare</h1>
      
      <!-- Input Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="form-control">
          <label class="label">
            <span class="label-text">First JSON</span>
          </label>
          <textarea
            class="textarea textarea-bordered h-64 font-mono"
            v-model="firstJson"
            placeholder="Paste your first JSON here"
          ></textarea>
        </div>
        
        <div class="form-control">
          <label class="label">
            <span class="label-text">Second JSON</span>
          </label>
          <textarea
            class="textarea textarea-bordered h-64 font-mono"
            v-model="secondJson"
            placeholder="Paste your second JSON here"
          ></textarea>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-center gap-4 mb-8">
        <button 
          class="btn btn-primary"
          @click="compareJsons"
          :disabled="!isValidJson"
          :class="{ 'loading': loading }"
        >
          Compare JSONs
        </button>
        <button 
          class="btn btn-ghost"
          @click="clearAll"
        >
          Clear All
        </button>
      </div>

      <!-- Results Section -->
      <div v-if="differences.length" class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title mb-4">Comparison Results</h2>
          
          <!-- Side by Side Comparison -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="relative">
              <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto"><code v-html="highlightJson(parsedFirstJson, differences, 'left')"></code></pre>
            </div>
            <div class="relative">
              <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto"><code v-html="highlightJson(parsedSecondJson, differences, 'right')"></code></pre>
            </div>
          </div>

          <!-- Differences List -->
          <div class="divider">Differences</div>
          <div class="space-y-2">
            <div v-for="(diff, index) in differences" :key="index" 
                 class="alert" 
                 :class="{
                   'alert-warning': diff.type === 'value_mismatch',
                   'alert-error': diff.type === 'missing_in_first',
                   'alert-info': diff.type === 'missing_in_second'
                 }">
              <div>
                <span class="font-bold">{{ diff.path }}:</span>
                <span v-if="diff.type === 'value_mismatch'">
                  Changed from <code class="mx-1">{{ diff.value1 }}</code> to <code class="mx-1">{{ diff.value2 }}</code>
                </span>
                <span v-else-if="diff.type === 'missing_in_first'">
                  Added <code class="mx-1">{{ diff.value }}</code>
                </span>
                <span v-else-if="diff.type === 'missing_in_second'">
                  Removed <code class="mx-1">{{ diff.value }}</code>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Alert -->
      <div v-if="error" class="alert alert-error mt-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ error }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const firstJson = ref('')
const secondJson = ref('')
const differences = ref([])
const loading = ref(false)
const error = ref('')

const parsedFirstJson = ref(null)
const parsedSecondJson = ref(null)

const isValidJson = computed(() => {
  try {
    if (firstJson.value && secondJson.value) {
      JSON.parse(firstJson.value)
      JSON.parse(secondJson.value)
      return true
    }
    return false
  } catch {
    return false
  }
})

const formatJson = (obj) => {
  return JSON.stringify(obj, null, 2)
}

const clearAll = () => {
  firstJson.value = ''
  secondJson.value = ''
  differences.value = []
  error.value = ''
  parsedFirstJson.value = null
  parsedSecondJson.value = null
}

const compareJsons = async () => {
  try {
    loading.value = true
    error.value = ''
    
    // Store first JSON
    const payload1 = {
      number: 1,
      payload_to_compare: firstJson.value
    }
    
    const payload2 = {
      number: 2,
      payload_to_compare: secondJson.value
    }
    
    console.log('Sending payload 1:', payload1)
    await axios.post('http://localhost:8000/api/store', payload1, {
      headers: {
        'Content-Type': 'application/json'
      }
    })
    
    console.log('Sending payload 2:', payload2)
    await axios.post('http://localhost:8000/api/store', payload2, {
      headers: {
        'Content-Type': 'application/json'
      }
    })
    
    // Get comparison results
    const response = await axios.get('http://localhost:8000/api/compare')
    differences.value = response.data.differences
    
    // Parse and store the JSONs for display
    parsedFirstJson.value = JSON.parse(firstJson.value)
    parsedSecondJson.value = JSON.parse(secondJson.value)
  } catch (err) {
    console.error('Error:', err)
    error.value = err.response?.data?.message || 'An error occurred while comparing the JSONs'
  } finally {
    loading.value = false
  }
}

const highlightJson = (json, differences, side) => {
  let jsonString = JSON.stringify(json, null, 2);
  
  // Escape HTML characters
  jsonString = jsonString
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
  
  // For each difference, wrap the different values in colored spans
  differences.forEach(diff => {
    const color = side === 'left' ? '#86efac' : '#93c5fd'; // Green for left, blue for right
    
    if (diff.type === 'value_mismatch') {
      // Handle array value differences
      if (diff.path.includes('.')) {
        const value = side === 'left' ? diff.value1 : diff.value2;
        const escapedValue = String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        
        // Only highlight the content between quotes
        jsonString = jsonString.replace(
          new RegExp(`"(${escapedValue})"`, 'g'),
          (_, content) => `"<span style="background-color: ${color}">${content}</span>"`
        );
      }
    } else if (diff.type === 'missing_in_second' && side === 'left') {
      // For left side, highlight only the property name content between quotes
      const escapedPropertyName = diff.path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      jsonString = jsonString.replace(
        new RegExp(`"(${escapedPropertyName})"(?=:)`, 'g'),
        (_, content) => `"<span style="background-color: ${color}">${content}</span>"`
      );
    } else if (diff.type === 'missing_in_first' && side === 'right') {
      // For right side, highlight only the property name content between quotes
      const escapedPropertyName = diff.path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      jsonString = jsonString.replace(
        new RegExp(`"(${escapedPropertyName})"(?=:)`, 'g'),
        (_, content) => `"<span style="background-color: ${color}">${content}</span>"`
      );
    }
  });
  
  return jsonString;
}
</script> 