<template>
  <div class="dropdown">
    <h4
      v-if="title !== null"
      @click="e => $emit('toggle', e)"
      class="dropdown__title"
    >
      {{ title }}
    </h4>
    <div class="dropdown__content" style="display: none">
      <slot></slot>
      <a class="btn dropdown__apply" @click="e => $emit('apply', e)">Applica</a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'DropdownFilter',
  emits: ['toggle', 'apply'],
  props: {
    title: {
      type: String as PropType<string | null>,
      default: null,
    },
  },
});
</script>
