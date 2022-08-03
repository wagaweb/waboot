<template>
    <div id="shop-rules" class="shop-rules">
        <component :is="currentView" />
    </div>
</template>
<script lang="ts">
import { defineComponent, PropType } from 'vue';
import NewRule from "@/components/NewRule.vue";
import EditRule from "@/components/EditRule.vue";
import RulesList from "@/components/RulesList.vue";
import '@vuepic/vue-datepicker/dist/main.css';

const routes = {
    'list': RulesList,
    'new': NewRule,
    'edit': EditRule
}

export default defineComponent({
    name: 'App',
    components: {RulesList, EditRule, NewRule},
    data() {
        return {}
    },
    computed: {
        currentView(){
            //@ts-ignore
            return routes[this.$store.state.currentView || 'list'];
        }
    },
    mounted() {}
});
</script>