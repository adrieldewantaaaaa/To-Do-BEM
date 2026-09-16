<script setup>
import { computed, ref, onMounted } from 'vue';
const props = defineProps({
  points: { type: Array, default: () => [] },
  color: { type: String, default: 'var(--primary)' },
  width: { type: Number, default: 240 },
  height: { type: Number, default: 46 },
});
const max = computed(() => Math.max(1, ...props.points));
const coords = computed(() => {
  const n = props.points.length;
  if (n < 2) return [];
  return props.points.map((v, i) => [
    (i / (n - 1)) * props.width,
    props.height - 4 - (v / max.value) * (props.height - 10),
  ]);
});
const line = computed(() =>
  coords.value.map((c, i) => (i ? 'L' : 'M') + c[0].toFixed(1) + ' ' + c[1].toFixed(1)).join(' '),
);
const area = computed(() =>
  coords.value.length ? line.value + ` L ${props.width} ${props.height} L 0 ${props.height} Z` : '',
);
const last = computed(() => coords.value[coords.value.length - 1]);
const path = ref(null);
onMounted(() => {
  const el = path.value;
  if (!el) return;
  try {
    const L = el.getTotalLength();
    el.style.strokeDasharray = L;
    el.style.strokeDashoffset = L;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      el.style.strokeDashoffset = 0;
      return;
    }
    requestAnimationFrame(() => {
      el.style.transition = 'stroke-dashoffset .9s ease';
      el.style.strokeDashoffset = 0;
    });
  } catch (e) {
    /* noop */
  }
});
</script>
<template>
  <svg
    :viewBox="`0 0 ${width} ${height}`"
    preserveAspectRatio="none"
    class="block w-full"
    :style="{ height: height + 'px' }"
  >
    <path v-if="area" :d="area" :fill="color" opacity=".12" />
    <path
      ref="path"
      :d="line"
      :stroke="color"
      stroke-width="2"
      fill="none"
      stroke-linecap="round"
      stroke-linejoin="round"
      vector-effect="non-scaling-stroke"
    />
    <circle v-if="last" :cx="last[0]" :cy="last[1]" r="3" :fill="color" />
  </svg>
</template>
