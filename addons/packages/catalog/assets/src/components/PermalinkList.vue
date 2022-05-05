<template>
  <div :class="`filter filter--${taxonomy}`">
    <h3 v-if="title !== null" class="filter__title">{{ title }}</h3>
    <ul class="filter__list permalink-list__list">
      <li
        v-for="t in terms"
        :key="`term-${t.id}`"
        class="permalink-list__item"
        :class="{ 'permalink-list_item--selected': t.id === selected }"
      >
        <a :href="`${baseUrl}/${t.slug}`">{{ t.name }}</a>
        <template
          v-if="
            t.children.length > 0 && (maxDepth === null || depth < maxDepth)
          "
        >
          <small
            v-if="!fullOpen"
            @click="openMap.set(t.id, !openMap.get(t.id))"
          >
            {{ openMap.get(t.id) ? '-' : '+' }}
          </small>
          <PermalinkList
            :key="`term-${t.id}`"
            v-show="fullOpen || openMap.get(t.id)"
            :taxonomy="taxonomy"
            :terms="t.children"
            :baseUrl="baseUrl"
            :fullOpen="fullOpen"
            :selected="selected"
            :depth="depth + 1"
            :maxDepth="maxDepth"
          ></PermalinkList>
        </template>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { Term } from '@/services/api';
import { defineComponent, PropType } from 'vue';

export default defineComponent({
  name: 'PermalinkList',
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
    selected: {
      type: String as PropType<string | null>,
      default: null,
    },
    baseUrl: {
      type: String,
      required: true,
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
  data() {
    const openMap = new Map();
    for (const t of this.terms) {
      openMap.set(t.id, this.fullOpen || t.id === this.selected);
    }

    return {
      openMap,
    };
  },
});
</script>
