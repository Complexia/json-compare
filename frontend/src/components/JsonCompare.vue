<template>
  <div class="json-compare">
    <div class="flex flex-col gap-4">
      <div class="flex gap-4">
        <textarea class="textarea textarea-bordered w-full" v-model="json1"></textarea>
        <textarea class="textarea textarea-bordered w-full" v-model="json2"></textarea>
      </div>
      <button class="btn compare-btn" @click="compareJson">Compare JSON</button>
      <div v-if="error" class="text-red-500">{{ error }}</div>
      <div v-if="differences !== null" class="differences">
        <div v-if="differences.length === 0">No differences found</div>
        <div v-else>
          <div v-for="(diff, index) in differences" :key="index">
            {{ diff }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const json1 = ref('');
const json2 = ref('');
const differences = ref(null);
const error = ref(null);

const compareJson = () => {
  try {
    const obj1 = JSON.parse(json1.value);
    const obj2 = JSON.parse(json2.value);
    
    if (JSON.stringify(obj1) === JSON.stringify(obj2)) {
      differences.value = [];
      error.value = null;
    } else {
      differences.value = findDifferences(obj1, obj2);
      error.value = null;
    }
  } catch (e) {
    error.value = 'Invalid JSON';
    differences.value = null;
  }
};

const findDifferences = (obj1, obj2, path = '') => {
  const differences = [];
  
  for (const key in obj1) {
    const currentPath = path ? `${path}.${key}` : key;
    if (!(key in obj2)) {
      differences.push(`${currentPath} is missing in second object`);
    } else if (typeof obj1[key] !== typeof obj2[key]) {
      differences.push(`${currentPath} has different types`);
    } else if (typeof obj1[key] === 'object') {
      differences.push(...findDifferences(obj1[key], obj2[key], currentPath));
    } else if (obj1[key] !== obj2[key]) {
      differences.push(`${currentPath} values are different`);
    }
  }
  
  return differences;
};
</script> 