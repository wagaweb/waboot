<template>
  <div :class="`filter filter--${taxonomy}`">
    <h4 @click="open = !open" class="filter__title">{{ title }}</h4>
    <div class="filter__dropdown" v-show="open">
      <ul class="">
        <li v-for="t in terms" :key="`term-${t.id}`" class="filter-list__item">
          <label>
            <input
              :checked="checkedTerms.has(t.id)"
              @change="e => handleChange(e, t.id)"
              type="checkbox"
            />
            {{ t.name }}
          </label>
        </li>
      </ul>
      <a class="btn" @click="applyFilters">Applica</a>
    </div>
  </div>
</template>

<script lang="ts">
import { Term } from '@/services/api';
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'DropdownFilter',
  props: {
    taxonomy: {
      type: String,
      required: true,
    },
    title: {
      type: String as PropType<string | null>,
      required: true,
    },
    terms: {
      type: Array as PropType<Term[]>,
      required: true,
    },
    selectedTerms: {
      type: Set as PropType<Set<Term['id']>>,
      required: true,
    },
    applyCb: {
      type: Function as PropType<
        (
          taxonomy: string,
          checkedTerms: Set<Term['id']>,
        ) => Promise<void> | undefined
      >,
      required: false,
    },
  },
  data() {
    return {
      open: false,
      checkedTerms: new Set(this.selectedTerms.values()) as Set<Term['id']>,
    };
  },
  methods: {
    handleChange(e: Event, term: Term['id']): void {
      const checked = (e.target as HTMLInputElement).checked;

      if (checked) this.checkedTerms.add(term);
      else this.checkedTerms.delete(term);
    },
    applyFilters(): void {
      this.open = false;

      if (this.applyCb === undefined) return;
      this.applyCb(this.taxonomy, this.checkedTerms);
    },
  },
});
</script>
