<template>
  <div id="rules" class="shop-rules">
    <div class="shop-rules__heading">
      <h1 class="shop-rules__title">Shop Rules</h1>
      <button class="button button-primary" v-on:click="newRule()">Add New Rule</button>
    </div>
    <table class="wp-list-table widefat fixed posts striped table-view-list">
      <thead>
        <tr>
          <th class="manage-column column-title column-primary desc">Title</th>
          <th class="manage-column column-author">Rule Type</th>
          <th class="manage-column column-author">Start Date</th>
          <th class="manage-column column-author">End Date</th>
          <th class="manage-column column-author">Status</th>
          <th class="manage-column column-author">Actions</th>
        </tr>
      </thead>
      <tbody id="the-list">
        <tr
          v-for="rule in currentRules"
          :key="rule.id"
          :data-rule="rule.id"
          v-if="!loading"
          class="iedit author-self level-0 type-post status-draft hentry shop-rules__row"
        >
          <td class="title column-title column-primary page-title shop-rules__title">
            {{ rule.name }}
          </td>
          <td class="author column-author">{{ rule.type }}</td>
          <td class="author column-author">{{ formatDate(rule.from) }}</td>
          <td class="author column-author">{{ formatDate(rule.to) }}</td>
          <td class="author column-author">
            {{ rule.enabled ? 'enabled' : 'disabled' }}
          </td>
          <td class="author column-author wp-buttons shop-rules__cta">
            <a class="button button-primary" v-on:click="editRule(rule.id)" href="#"
              >Edit</a
            >
            <a class="button button-danger" v-on:click="deleteRule(rule.id)" href="#"
              >Delete</a
            >
          </td>
        </tr>
        <tr v-else>
          <td colspan="6">Loading...</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script lang="ts">
import { defineComponent, ref } from 'vue';
import { fetchRules } from '@/services/api';
import { useStore } from '@/store';
import { format } from 'date-fns';

const axios = require('axios').default;
export default defineComponent({
  name: 'RulesList',
  components: {},
  data(): {
    currentRules: ShopRule[];
    loading: boolean;
  } {
    return {
      currentRules: [],
      loading: false,
    };
  },
  computed: {
    hasRules(): boolean {
      return this.currentRules.length > 0;
    },
  },
  mounted() {
    this.populateRules();
  },
  methods: {
    async populateRules(): Promise<void> {
      this.loading = true;
      this.currentRules = await fetchRules();
      this.loading = false;
    },
    newRule() {
      this.$store.commit('goToNewView');
    },
    editRule(ruleId: number) {
      this.$store.commit('goToEditView', ruleId);
    },
    deleteRule(ruleId: number) {},
    formatDate(date: string): string {
      return format(new Date(date), 'dd-MM-yyyy HH:mm')
    }
  },
});
</script>
