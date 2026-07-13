<template>
  <div :class="`filter filter--${taxonomy}`">
    <h4 v-if="title !== null" class="filter__title">{{ title }}</h4>
    <ul class="filter__list filter-list">
      <li
        v-for="t in terms"
        :key="`term-${t.id}`"
        class="filter-list__item"
        :class="{ 'permalink-list__item--active': selectedTerms.has(t.id) }"
      >
        <label>
          <input
            :checked="selectedTerms.has(t.id)"
            @change="handleChange($event, t)"
            type="checkbox"
          />
          {{ t.name }}
        </label>
        <FilterList
          v-if="
            t.children.length > 0 && (maxDepth === null || maxDepth > depth)
          "
          v-show="fullOpen || selectedTerms.has(t.id)"
          :key="`term-${t.id}`"
          :taxonomy="taxonomy"
          :terms="t.children"
          :selectedTerms="selectedTerms"
          :toggleCb="toggleCb"
          :depth="depth + 1"
          :maxDepth="maxDepth"
        ></FilterList>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { Term } from '@/services/api';
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'FilterList',
  props: {
    taxonomy: {
      type: String,
      required: true,
    },
    title: {
      type: String as PropType<string | null>,
      default: null,
    },
    terms: {
      type: Array as PropType<Term[]>,
      required: true,
    },
    selectedTerms: {
      type: Set as PropType<Set<Term['id']>>,
      required: true,
    },
    toggleCb: {
      type: Function as PropType<
        (taxonomy: string, term: Term, checked: boolean) => Promise<void> | void
      >,
      required: false,
    },
    fullOpen: {
      type: Boolean,
      default: false,
    },
    depth: {
      type: Number,
      default: 1,
    },
    maxDepth: {
      type: Number as PropType<number | null>,
      default: null,
    },
  },
  methods: {
    handleChange(e: Event, term: Term): void {
      const checked = (e.target as HTMLInputElement).checked;

      if (this.toggleCb === undefined) return;
      this.toggleCb(this.taxonomy, term, checked);
    },
  },
});
</script>
