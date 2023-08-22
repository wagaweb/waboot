<script setup lang="ts">
import Breadcrumb from './components/Breadcrumb.vue'
import SignInLanding from "@/components/SignInLanding.vue";
import SignInByPassword from "@/components/SignInByPassword.vue";
import {onMounted, ref} from 'vue';

const loading = ref(false);
const userRegistered = ref(false);
const userSignedIn = ref(false);

onMounted(() => {
  loading.value = true;
  setTimeout(() => {
    userSignedIn.value = true;
    loading.value = false;
  },3000);
});
</script>

<template>
  <Breadcrumb />
  <SignInLanding v-if="!loading && !userSignedIn" />
  <SignInByPassword v-if="!loading && userSignedIn" />
</template>
