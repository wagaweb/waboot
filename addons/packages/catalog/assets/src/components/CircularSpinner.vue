<template>
  <svg
    class="circular-spinner"
    :width="pxSize"
    :height="pxSize"
    viewBox="-50 -50 100 100"
  >
    <circle
      :r="50 - props.stroke"
      fill="none"
      :stroke="props.color"
      :stroke-width="stroke"
      pathLength="150"
    ></circle>
  </svg>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps({
  size: {
    type: Number,
    default: null,
  },
  color: {
    type: String,
    default: "#000000",
  },
  stroke: {
    type: Number,
    default: 5,
  },
});

const pxSize = computed(() => {
  if (props.size === null) {
    return "auto";
  }

  return props.size + "px";
});
</script>

<style lang="scss">
.circular-spinner {
  background-color: transparent;
  circle {
    stroke-linecap: round;
    animation: dash 1.5s ease-in-out infinite, rotate 2s linear infinite;
  }
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

@keyframes dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -150;
  }
}
</style>
