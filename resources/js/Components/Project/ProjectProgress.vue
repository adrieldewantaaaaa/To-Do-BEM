<script setup>
import SketchProgress from '@/Components/Sketch/SketchProgress.vue';
defineProps({ progress: { type: Object, required: true } });
</script>
<template>
  <SketchProgress :value="progress.percentage" :label="`${progress.completed} / ${progress.total} tasks`" />
</template>
