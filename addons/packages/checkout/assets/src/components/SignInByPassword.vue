<script setup lang="ts">
import {ref} from "vue";
import {WPUser} from "@/services/wp/wpuser";
import type {fetchedUserData} from "../../env";

const props = defineProps({
    email: String
});

const emit = defineEmits<{
    (e: 'userSignedIn', userData: fetchedUserData): void
}>();

const password = ref('');
const loading = ref(false);

function onSubmit(){
    loading.value = true;
    new WPUser().signInUser(props.email as string,password.value).then((userData) => {
        loading.value = false;
        emit('userSignedIn',userData);
    }).catch((error) => {
        loading.value = false;
        console.log('SignInByPassword error');
        console.error(error);
    });
}

</script>
<template>
  <div>
    <h4>Bentornato!</h4>

    <div class="woocommerce-checkout-steps__data">
      <h5>Indirizzo email</h5>
      <ul class="">
        <li>{{ email }}</li>
      </ul>
    </div>

    <div class="checkout woocommerce-checkout" :class="{'loading': loading}">
      <div class="woocommerce-billing-fields__field-wrapper">
        <div class="form-row form-row-wide">
          <label for="password">Inserisci la tua password per accedere</label>
          <input type="password" placeholder="Password" id="password" v-model="password">
        </div>
        <div class="form-row form-row-wide">
          <a href="#">Password dimenticata?</a>
        </div>
      </div>
      <input type="submit" value="Accedi" class="btn btn--primary" @click.prevent="onSubmit">
    </div>
  </div>
</template>