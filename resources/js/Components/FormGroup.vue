<template>
  <div>
    <label :for="id" class="form-label">{{ label }}</label>
    <input
      :id="id"
      :type="type || 'text'"
      :placeholder="placeholder"
      class="form-input"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="form-error">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  id: String,
  type: String,
  modelValue: String,
  error: String,
  placeholder: String,
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.form-label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: #374151;
}
.dark .form-label {
  color: #e5e7eb;
}
.form-input {
  width: 100%;
  padding: 0.65rem 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db;
  transition: all 0.2s ease-in-out;
  background-color: #ffffff;
  color: #111827;
}
.dark .form-input {
  border-color: #4b5563;
  background-color: #1f2937;
  color: #f9fafb;
}
.form-input:hover,
.form-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
  outline: none;
}
.dark .form-input:hover,
.dark .form-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}
.form-error {
  font-size: 0.875rem;
  color: #dc2626;
  margin-top: 0.25rem;
}
.dark .form-error {
  color: #f87171;
}
</style>


