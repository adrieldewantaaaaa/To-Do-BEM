<script setup>
defineProps({
  id: String,
  label: String,
  modelValue: [String, Number],
  type: { type: String, default: 'text' },
  error: String,
  required: Boolean,
  placeholder: String,
  maxlength: [String, Number],
  autocomplete: String,
});
defineEmits(['update:modelValue']);
</script>
<template>
  <div>
    <label v-if="label" :for="id" class="tdb-label"
      >{{ label }} <span v-if="required" class="text-[var(--danger)]">*</span></label
    ><input
      :id="id"
      :type="type"
      :value="modelValue"
      :required="required"
      :placeholder="placeholder"
      :maxlength="maxlength"
      :autocomplete="autocomplete"
      class="tdb-input"
      :class="{ 'tdb-input-error': error }"
      :aria-invalid="!!error"
      :aria-describedby="error ? `${id}-error` : undefined"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" :id="`${id}-error`" class="mt-1 text-sm text-[var(--danger)]">{{ error }}</p>
  </div>
</template>
