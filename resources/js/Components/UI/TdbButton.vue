<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
  href: String,
  variant: { type: String, default: 'primary' },
  type: { type: String, default: 'button' },
  disabled: Boolean,
});
</script>
<template>
  <component
    :is="href ? Link : 'button'"
    :href="href"
    :type="href ? undefined : type"
    :disabled="disabled"
    class="tdb-btn"
    :class="`tdb-btn-${variant}`"
    ><slot
  /></component>
</template>
