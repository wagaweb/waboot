<script setup lang="ts">
    import {ref} from 'vue';
    import {WPUser} from "@/services/wp/wpuser";
    const emit = defineEmits<{
        (e: 'emailVerified', email: string, isRegistered: boolean): void
    }>();

    const email = ref('');

    function checkEmail(event: any){
        console.log('Checking email:' + email.value);
        new WPUser().checkIfEmailIsRegistered(email.value).then((isEmailRegistered: boolean) => {
            if(isEmailRegistered){
                emit('emailVerified', email.value, true);
            }else{
                emit('emailVerified', email.value, false);
            }
        });
    }
</script>

<template>
  <div>
    <h4>Benvenuto!</h4>

    <form action="" class="checkout woocommerce-checkout" @submit.prevent="checkEmail">
      <div class="woocommerce-billing-fields__field-wrapper">
        <div class="form-row form-row-wide">
          <label for="email">Inserisci la tua email prima di procedere</label>
          <input type="email" placeholder="Email" id="email" v-model="email">
        </div>
        <div class="form-row form-row-wide">
          <input type="checkbox" id="newsletter">
          <label for="newsletter">Ricevi offerte e comunicazioni</label>
        </div>
      </div>
      <input type="submit" value="Continua" class="btn btn--primary">
    </form>

    <h4>Oppure accedi con</h4>

    <p>Scegli un account social per accedere o registrati</p>

    [SOCIAL LOGIN BUTTONS HERE...]
  </div>
</template>